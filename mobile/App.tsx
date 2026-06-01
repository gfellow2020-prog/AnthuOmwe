import { StatusBar } from 'expo-status-bar';
import { useMemo, useState, type ReactNode } from 'react';
import {
  ActivityIndicator,
  Alert,
  FlatList,
  Pressable,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';

type User = {
  id: number;
  name: string;
  email: string;
};

type Encounter = {
  id: number;
  encounter_number: string;
  current_stage: string;
  current_status: string;
  priority_level?: string;
  patient?: {
    id: number;
    patient_id: string;
    full_name: string;
    gender?: string;
    date_of_birth?: string;
  };
};

type Patient = {
  id: number;
  patient_id: string;
  full_name: string;
  gender?: string;
  date_of_birth?: string;
  phone_number?: string;
};

type DashboardSummary = {
  patients: number;
  households: number;
  today_shift_patients: number;
  encounters: {
    total: number;
    active: number;
    completed: number;
    by_stage: Record<string, number>;
    recent: Encounter[];
  };
};

const API_BASE_URL = 'http://localhost:8000/api/v1';
const QUEUES = ['registration', 'triage', 'screening', 'lab', 'screening-review', 'pharmacy'];

async function readJson<T>(response: Response): Promise<T> {
  const json = await response.json().catch(() => ({}));

  if (!response.ok) {
    const message = typeof json?.message === 'string' ? json.message : 'Request failed.';
    throw new Error(message);
  }

  return json as T;
}

export default function App() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [token, setToken] = useState<string | null>(null);
  const [user, setUser] = useState<User | null>(null);
  const [summary, setSummary] = useState<DashboardSummary | null>(null);
  const [activeQueue, setActiveQueue] = useState('triage');
  const [queued, setQueued] = useState<Encounter[]>([]);
  const [inProgress, setInProgress] = useState<Encounter[]>([]);
  const [patientQuery, setPatientQuery] = useState('');
  const [patients, setPatients] = useState<Patient[]>([]);
  const [encounters, setEncounters] = useState<Encounter[]>([]);
  const [loading, setLoading] = useState(false);

  const authHeaders = useMemo(
    () => ({
      Accept: 'application/json',
      'Content-Type': 'application/json',
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
    }),
    [token],
  );

  async function login() {
    setLoading(true);
    try {
      const response = await fetch(`${API_BASE_URL}/auth/login`, {
        method: 'POST',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ email, password, device_name: 'react-native-app' }),
      });
      const json = await readJson<{ token: string; user: User }>(response);
      setToken(json.token);
      setUser(json.user);
      await Promise.all([loadDashboard(json.token), loadEncounters(json.token)]);
    } catch (error) {
      Alert.alert('Login failed', error instanceof Error ? error.message : 'Please try again.');
    } finally {
      setLoading(false);
    }
  }

  async function logout() {
    if (token) {
      await fetch(`${API_BASE_URL}/auth/logout`, { method: 'POST', headers: authHeaders }).catch(() => undefined);
    }
    setToken(null);
    setUser(null);
    setSummary(null);
    setQueued([]);
    setInProgress([]);
    setPatients([]);
    setEncounters([]);
  }

  async function loadDashboard(forToken = token) {
    if (!forToken) return;
    const response = await fetch(`${API_BASE_URL}/dashboard/summary`, {
      headers: { ...authHeaders, Authorization: `Bearer ${forToken}` },
    });
    const json = await readJson<{ data: DashboardSummary }>(response);
    setSummary(json.data);
  }

  async function loadQueue(stage = activeQueue) {
    if (!token) return;
    setLoading(true);
    try {
      const response = await fetch(`${API_BASE_URL}/queues/${stage}`, { headers: authHeaders });
      const json = await readJson<{ data: { queued: Encounter[]; in_progress: Encounter[] } }>(response);
      setActiveQueue(stage);
      setQueued(json.data.queued ?? []);
      setInProgress(json.data.in_progress ?? []);
    } catch (error) {
      Alert.alert('Queue error', error instanceof Error ? error.message : 'Could not load queue.');
    } finally {
      setLoading(false);
    }
  }

  async function searchPatients() {
    if (!token || patientQuery.trim().length < 2) return;
    setLoading(true);
    try {
      const response = await fetch(`${API_BASE_URL}/patients/search?q=${encodeURIComponent(patientQuery.trim())}`, {
        headers: authHeaders,
      });
      const json = await readJson<{ data: Patient[] }>(response);
      setPatients(json.data ?? []);
    } catch (error) {
      Alert.alert('Search error', error instanceof Error ? error.message : 'Could not search patients.');
    } finally {
      setLoading(false);
    }
  }

  async function loadEncounters(forToken = token) {
    if (!forToken) return;
    const response = await fetch(`${API_BASE_URL}/encounters?per_page=10`, {
      headers: { ...authHeaders, Authorization: `Bearer ${forToken}` },
    });
    const json = await readJson<{ data: Encounter[] }>(response);
    setEncounters(json.data ?? []);
  }

  if (!token) {
    return (
      <SafeAreaView style={styles.safeArea}>
        <StatusBar style="dark" />
        <View style={styles.loginCard}>
          <Text style={styles.title}>Anthu Omwe Mobile</Text>
          <Text style={styles.subtitle}>Sign in with your staff portal account.</Text>
          <TextInput
            autoCapitalize="none"
            keyboardType="email-address"
            onChangeText={setEmail}
            placeholder="Email"
            style={styles.input}
            value={email}
          />
          <TextInput
            onChangeText={setPassword}
            placeholder="Password"
            secureTextEntry
            style={styles.input}
            value={password}
          />
          <Pressable disabled={loading} onPress={login} style={styles.primaryButton}>
            <Text style={styles.primaryButtonText}>{loading ? 'Signing in...' : 'Sign in'}</Text>
          </Pressable>
          <Text style={styles.helpText}>API: {API_BASE_URL}</Text>
        </View>
      </SafeAreaView>
    );
  }

  return (
    <SafeAreaView style={styles.safeArea}>
      <StatusBar style="dark" />
      <ScrollView contentContainerStyle={styles.container}>
        <View style={styles.header}>
          <View>
            <Text style={styles.title}>Admin Portal</Text>
            <Text style={styles.subtitle}>Signed in as {user?.name}</Text>
          </View>
          <Pressable onPress={logout} style={styles.secondaryButton}>
            <Text style={styles.secondaryButtonText}>Logout</Text>
          </Pressable>
        </View>

        <Section title="Dashboard">
          <View style={styles.metricsGrid}>
            <Metric label="Patients" value={summary?.patients ?? 0} />
            <Metric label="Households" value={summary?.households ?? 0} />
            <Metric label="Active" value={summary?.encounters.active ?? 0} />
            <Metric label="Completed" value={summary?.encounters.completed ?? 0} />
          </View>
          <Pressable onPress={() => loadDashboard()} style={styles.secondaryButton}>
            <Text style={styles.secondaryButtonText}>Refresh dashboard</Text>
          </Pressable>
        </Section>

        <Section title="Queues">
          <ScrollView horizontal showsHorizontalScrollIndicator={false} style={styles.queueTabs}>
            {QUEUES.map((stage) => (
              <Pressable
                key={stage}
                onPress={() => loadQueue(stage)}
                style={[styles.tab, activeQueue === stage && styles.activeTab]}
              >
                <Text style={[styles.tabText, activeQueue === stage && styles.activeTabText]}>{stage}</Text>
              </Pressable>
            ))}
          </ScrollView>
          {loading ? <ActivityIndicator /> : null}
          <Text style={styles.listTitle}>Queued</Text>
          <EncounterList encounters={queued} />
          <Text style={styles.listTitle}>In progress</Text>
          <EncounterList encounters={inProgress} />
        </Section>

        <Section title="Patient search">
          <View style={styles.searchRow}>
            <TextInput
              onChangeText={setPatientQuery}
              placeholder="Name, NRC, phone, barcode"
              style={[styles.input, styles.searchInput]}
              value={patientQuery}
            />
            <Pressable onPress={searchPatients} style={styles.primaryButtonSmall}>
              <Text style={styles.primaryButtonText}>Search</Text>
            </Pressable>
          </View>
          <FlatList
            data={patients}
            keyExtractor={(item) => String(item.id)}
            renderItem={({ item }) => (
              <View style={styles.listItem}>
                <Text style={styles.itemTitle}>{item.full_name}</Text>
                <Text style={styles.itemMeta}>{item.patient_id} - {item.gender ?? 'unknown'} - {item.date_of_birth ?? 'DOB unknown'}</Text>
                <Text style={styles.itemMeta}>{item.phone_number ?? 'No phone'}</Text>
              </View>
            )}
            scrollEnabled={false}
          />
        </Section>

        <Section title="Recent encounters">
          <Pressable onPress={() => loadEncounters()} style={styles.secondaryButton}>
            <Text style={styles.secondaryButtonText}>Refresh encounters</Text>
          </Pressable>
          <EncounterList encounters={encounters} />
        </Section>
      </ScrollView>
    </SafeAreaView>
  );
}

function Section({ title, children }: { title: string; children: ReactNode }) {
  return (
    <View style={styles.section}>
      <Text style={styles.sectionTitle}>{title}</Text>
      {children}
    </View>
  );
}

function Metric({ label, value }: { label: string; value: number }) {
  return (
    <View style={styles.metric}>
      <Text style={styles.metricValue}>{value}</Text>
      <Text style={styles.metricLabel}>{label}</Text>
    </View>
  );
}

function EncounterList({ encounters }: { encounters: Encounter[] }) {
  if (encounters.length === 0) {
    return <Text style={styles.emptyText}>No encounters found.</Text>;
  }

  return (
    <FlatList
      data={encounters}
      keyExtractor={(item) => String(item.id)}
      renderItem={({ item }) => (
        <View style={styles.listItem}>
          <Text style={styles.itemTitle}>{item.encounter_number}</Text>
          <Text style={styles.itemMeta}>{item.patient?.full_name ?? 'Unknown patient'}</Text>
          <Text style={styles.itemMeta}>{item.current_stage} - {item.current_status} - {item.priority_level ?? 'normal'}</Text>
        </View>
      )}
      scrollEnabled={false}
    />
  );
}

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    backgroundColor: '#eef4ff',
  },
  container: {
    padding: 16,
    gap: 16,
  },
  loginCard: {
    margin: 20,
    marginTop: 96,
    padding: 20,
    borderRadius: 24,
    backgroundColor: '#fff',
    shadowColor: '#000',
    shadowOpacity: 0.08,
    shadowRadius: 16,
    elevation: 3,
    gap: 12,
  },
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  title: {
    fontSize: 28,
    fontWeight: '800',
    color: '#0f172a',
  },
  subtitle: {
    color: '#475569',
    marginTop: 4,
  },
  section: {
    backgroundColor: '#fff',
    borderRadius: 20,
    padding: 16,
    gap: 12,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: '#0f172a',
  },
  metricsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 10,
  },
  metric: {
    width: '47%',
    backgroundColor: '#eff6ff',
    borderRadius: 16,
    padding: 14,
  },
  metricValue: {
    fontSize: 24,
    fontWeight: '800',
    color: '#1d4ed8',
  },
  metricLabel: {
    color: '#475569',
    marginTop: 4,
  },
  input: {
    borderWidth: 1,
    borderColor: '#cbd5e1',
    borderRadius: 12,
    paddingHorizontal: 14,
    paddingVertical: 12,
    backgroundColor: '#fff',
  },
  searchRow: {
    flexDirection: 'row',
    gap: 8,
    alignItems: 'center',
  },
  searchInput: {
    flex: 1,
  },
  primaryButton: {
    backgroundColor: '#2563eb',
    borderRadius: 12,
    paddingVertical: 14,
    alignItems: 'center',
  },
  primaryButtonSmall: {
    backgroundColor: '#2563eb',
    borderRadius: 12,
    paddingVertical: 12,
    paddingHorizontal: 16,
    alignItems: 'center',
  },
  primaryButtonText: {
    color: '#fff',
    fontWeight: '700',
  },
  secondaryButton: {
    borderColor: '#2563eb',
    borderWidth: 1,
    borderRadius: 12,
    paddingVertical: 10,
    paddingHorizontal: 12,
    alignItems: 'center',
    alignSelf: 'flex-start',
  },
  secondaryButtonText: {
    color: '#2563eb',
    fontWeight: '700',
  },
  queueTabs: {
    flexGrow: 0,
  },
  tab: {
    borderRadius: 999,
    borderWidth: 1,
    borderColor: '#cbd5e1',
    paddingHorizontal: 12,
    paddingVertical: 8,
    marginRight: 8,
  },
  activeTab: {
    backgroundColor: '#2563eb',
    borderColor: '#2563eb',
  },
  tabText: {
    color: '#475569',
    textTransform: 'capitalize',
  },
  activeTabText: {
    color: '#fff',
    fontWeight: '700',
  },
  listTitle: {
    fontWeight: '700',
    color: '#334155',
  },
  listItem: {
    borderWidth: 1,
    borderColor: '#e2e8f0',
    borderRadius: 14,
    padding: 12,
    marginBottom: 8,
  },
  itemTitle: {
    color: '#0f172a',
    fontWeight: '700',
  },
  itemMeta: {
    color: '#64748b',
    marginTop: 3,
  },
  emptyText: {
    color: '#94a3b8',
  },
  helpText: {
    color: '#64748b',
    fontSize: 12,
    textAlign: 'center',
  },
});
