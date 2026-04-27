<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds the medications table with a comprehensive hospital formulary.
 *
 * Categories follow the Zambian Essential Medicines List (ZEML) and WHO ATC
 * therapeutic groupings commonly used in primary/secondary-level health centers.
 *
 * Idempotent: uses upsert on (name, strength, form) to avoid duplicates.
 */
class MedicationsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $medications = collect($this->medications())->map(fn (array $m) => array_merge($m, [
            'is_controlled' => $m['is_controlled'] ?? false,
            'is_active'     => true,
            'notes'         => $m['notes'] ?? null,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]))->all();

        // Insert in chunks (idempotent — skip duplicates)
        foreach (array_chunk($medications, 50) as $chunk) {
            DB::table('medications')->upsert(
                $chunk,
                ['name', 'strength', 'form'],
                ['generic_name', 'category', 'default_route', 'default_frequency', 'is_controlled', 'is_active', 'notes', 'updated_at']
            );
        }

        $this->command->info('Seeded ' . count($medications) . ' medications.');
    }

    /** @return array<int, array<string, mixed>> */
    private function medications(): array
    {
        return [
            // ═══════════════════════════════════════════════════════════════
            //  ANALGESICS & ANTIPYRETICS
            // ═══════════════════════════════════════════════════════════════
            ['name' => 'Paracetamol',             'generic_name' => 'Acetaminophen',       'category' => 'Analgesic',          'form' => 'Tablet',     'strength' => '500mg',       'default_route' => 'Oral',       'default_frequency' => 'QDS'],
            ['name' => 'Paracetamol',             'generic_name' => 'Acetaminophen',       'category' => 'Analgesic',          'form' => 'Syrup',      'strength' => '120mg/5ml',   'default_route' => 'Oral',       'default_frequency' => 'QDS'],
            ['name' => 'Paracetamol',             'generic_name' => 'Acetaminophen',       'category' => 'Analgesic',          'form' => 'Suppository','strength' => '125mg',       'default_route' => 'Rectal',     'default_frequency' => 'QDS'],
            ['name' => 'Paracetamol',             'generic_name' => 'Acetaminophen',       'category' => 'Analgesic',          'form' => 'Injection',  'strength' => '10mg/ml',     'default_route' => 'IV',         'default_frequency' => 'QDS'],
            ['name' => 'Ibuprofen',               'generic_name' => 'Ibuprofen',           'category' => 'Analgesic',          'form' => 'Tablet',     'strength' => '200mg',       'default_route' => 'Oral',       'default_frequency' => 'TDS'],
            ['name' => 'Ibuprofen',               'generic_name' => 'Ibuprofen',           'category' => 'Analgesic',          'form' => 'Tablet',     'strength' => '400mg',       'default_route' => 'Oral',       'default_frequency' => 'TDS'],
            ['name' => 'Ibuprofen',               'generic_name' => 'Ibuprofen',           'category' => 'Analgesic',          'form' => 'Syrup',      'strength' => '100mg/5ml',   'default_route' => 'Oral',       'default_frequency' => 'TDS'],
            ['name' => 'Diclofenac',              'generic_name' => 'Diclofenac Sodium',   'category' => 'Analgesic',          'form' => 'Tablet',     'strength' => '50mg',        'default_route' => 'Oral',       'default_frequency' => 'TDS'],
            ['name' => 'Diclofenac',              'generic_name' => 'Diclofenac Sodium',   'category' => 'Analgesic',          'form' => 'Injection',  'strength' => '75mg/3ml',    'default_route' => 'IM',         'default_frequency' => 'BD'],
            ['name' => 'Aspirin',                 'generic_name' => 'Acetylsalicylic Acid','category' => 'Analgesic',          'form' => 'Tablet',     'strength' => '300mg',       'default_route' => 'Oral',       'default_frequency' => 'TDS'],
            ['name' => 'Aspirin',                 'generic_name' => 'Acetylsalicylic Acid','category' => 'Analgesic',          'form' => 'Tablet',     'strength' => '75mg',        'default_route' => 'Oral',       'default_frequency' => 'OD'],
            ['name' => 'Tramadol',                'generic_name' => 'Tramadol HCl',        'category' => 'Analgesic',          'form' => 'Capsule',    'strength' => '50mg',        'default_route' => 'Oral',       'default_frequency' => 'TDS', 'is_controlled' => true],
            ['name' => 'Tramadol',                'generic_name' => 'Tramadol HCl',        'category' => 'Analgesic',          'form' => 'Injection',  'strength' => '100mg/2ml',   'default_route' => 'IM',         'default_frequency' => 'TDS', 'is_controlled' => true],
            ['name' => 'Morphine',                'generic_name' => 'Morphine Sulfate',    'category' => 'Analgesic',          'form' => 'Injection',  'strength' => '10mg/ml',     'default_route' => 'IV',         'default_frequency' => 'PRN', 'is_controlled' => true],
            ['name' => 'Pethidine',               'generic_name' => 'Meperidine',          'category' => 'Analgesic',          'form' => 'Injection',  'strength' => '50mg/ml',     'default_route' => 'IM',         'default_frequency' => 'PRN', 'is_controlled' => true],
            ['name' => 'Codeine Phosphate',       'generic_name' => 'Codeine',             'category' => 'Analgesic',          'form' => 'Tablet',     'strength' => '30mg',        'default_route' => 'Oral',       'default_frequency' => 'QDS', 'is_controlled' => true],

            // ═══════════════════════════════════════════════════════════════
            //  ANTIBIOTICS
            // ═══════════════════════════════════════════════════════════════
            ['name' => 'Amoxicillin',             'generic_name' => 'Amoxicillin',         'category' => 'Antibiotic',         'form' => 'Capsule',    'strength' => '250mg',       'default_route' => 'Oral',       'default_frequency' => 'TDS'],
            ['name' => 'Amoxicillin',             'generic_name' => 'Amoxicillin',         'category' => 'Antibiotic',         'form' => 'Capsule',    'strength' => '500mg',       'default_route' => 'Oral',       'default_frequency' => 'TDS'],
            ['name' => 'Amoxicillin',             'generic_name' => 'Amoxicillin',         'category' => 'Antibiotic',         'form' => 'Syrup',      'strength' => '125mg/5ml',   'default_route' => 'Oral',       'default_frequency' => 'TDS'],
            ['name' => 'Amoxicillin/Clavulanate', 'generic_name' => 'Co-Amoxiclav',       'category' => 'Antibiotic',         'form' => 'Tablet',     'strength' => '625mg',       'default_route' => 'Oral',       'default_frequency' => 'TDS'],
            ['name' => 'Amoxicillin/Clavulanate', 'generic_name' => 'Co-Amoxiclav',       'category' => 'Antibiotic',         'form' => 'Syrup',      'strength' => '228mg/5ml',   'default_route' => 'Oral',       'default_frequency' => 'TDS'],
            ['name' => 'Azithromycin',            'generic_name' => 'Azithromycin',        'category' => 'Antibiotic',         'form' => 'Tablet',     'strength' => '250mg',       'default_route' => 'Oral',       'default_frequency' => 'OD'],
            ['name' => 'Azithromycin',            'generic_name' => 'Azithromycin',        'category' => 'Antibiotic',         'form' => 'Tablet',     'strength' => '500mg',       'default_route' => 'Oral',       'default_frequency' => 'OD'],
            ['name' => 'Ciprofloxacin',           'generic_name' => 'Ciprofloxacin',       'category' => 'Antibiotic',         'form' => 'Tablet',     'strength' => '500mg',       'default_route' => 'Oral',       'default_frequency' => 'BD'],
            ['name' => 'Ciprofloxacin',           'generic_name' => 'Ciprofloxacin',       'category' => 'Antibiotic',         'form' => 'Injection',  'strength' => '200mg/100ml', 'default_route' => 'IV',         'default_frequency' => 'BD'],
            ['name' => 'Doxycycline',             'generic_name' => 'Doxycycline',         'category' => 'Antibiotic',         'form' => 'Capsule',    'strength' => '100mg',       'default_route' => 'Oral',       'default_frequency' => 'BD'],
            ['name' => 'Erythromycin',            'generic_name' => 'Erythromycin',        'category' => 'Antibiotic',         'form' => 'Tablet',     'strength' => '250mg',       'default_route' => 'Oral',       'default_frequency' => 'QDS'],
            ['name' => 'Erythromycin',            'generic_name' => 'Erythromycin',        'category' => 'Antibiotic',         'form' => 'Syrup',      'strength' => '125mg/5ml',   'default_route' => 'Oral',       'default_frequency' => 'QDS'],
            ['name' => 'Metronidazole',           'generic_name' => 'Metronidazole',       'category' => 'Antibiotic',         'form' => 'Tablet',     'strength' => '200mg',       'default_route' => 'Oral',       'default_frequency' => 'TDS'],
            ['name' => 'Metronidazole',           'generic_name' => 'Metronidazole',       'category' => 'Antibiotic',         'form' => 'Tablet',     'strength' => '400mg',       'default_route' => 'Oral',       'default_frequency' => 'TDS'],
            ['name' => 'Metronidazole',           'generic_name' => 'Metronidazole',       'category' => 'Antibiotic',         'form' => 'Injection',  'strength' => '500mg/100ml', 'default_route' => 'IV',         'default_frequency' => 'TDS'],
            ['name' => 'Metronidazole',           'generic_name' => 'Metronidazole',       'category' => 'Antibiotic',         'form' => 'Syrup',      'strength' => '200mg/5ml',   'default_route' => 'Oral',       'default_frequency' => 'TDS'],
            ['name' => 'Ceftriaxone',             'generic_name' => 'Ceftriaxone',         'category' => 'Antibiotic',         'form' => 'Injection',  'strength' => '1g',          'default_route' => 'IV',         'default_frequency' => 'OD'],
            ['name' => 'Ceftriaxone',             'generic_name' => 'Ceftriaxone',         'category' => 'Antibiotic',         'form' => 'Injection',  'strength' => '250mg',       'default_route' => 'IM',         'default_frequency' => 'Stat'],
            ['name' => 'Gentamicin',              'generic_name' => 'Gentamicin',          'category' => 'Antibiotic',         'form' => 'Injection',  'strength' => '80mg/2ml',    'default_route' => 'IM',         'default_frequency' => 'OD'],
            ['name' => 'Benzylpenicillin',        'generic_name' => 'Penicillin G',        'category' => 'Antibiotic',         'form' => 'Injection',  'strength' => '5MU',         'default_route' => 'IV',         'default_frequency' => 'QDS'],
            ['name' => 'Cloxacillin',             'generic_name' => 'Cloxacillin',         'category' => 'Antibiotic',         'form' => 'Capsule',    'strength' => '500mg',       'default_route' => 'Oral',       'default_frequency' => 'QDS'],
            ['name' => 'Cotrimoxazole',           'generic_name' => 'Sulfamethoxazole/TMP','category' => 'Antibiotic',         'form' => 'Tablet',     'strength' => '480mg',       'default_route' => 'Oral',       'default_frequency' => 'BD'],
            ['name' => 'Cotrimoxazole',           'generic_name' => 'Sulfamethoxazole/TMP','category' => 'Antibiotic',         'form' => 'Tablet',     'strength' => '960mg',       'default_route' => 'Oral',       'default_frequency' => 'BD'],
            ['name' => 'Cotrimoxazole',           'generic_name' => 'Sulfamethoxazole/TMP','category' => 'Antibiotic',         'form' => 'Syrup',      'strength' => '240mg/5ml',   'default_route' => 'Oral',       'default_frequency' => 'BD'],
            ['name' => 'Nitrofurantoin',          'generic_name' => 'Nitrofurantoin',      'category' => 'Antibiotic',         'form' => 'Capsule',    'strength' => '100mg',       'default_route' => 'Oral',       'default_frequency' => 'QDS'],
            ['name' => 'Flucloxacillin',          'generic_name' => 'Flucloxacillin',      'category' => 'Antibiotic',         'form' => 'Capsule',    'strength' => '250mg',       'default_route' => 'Oral',       'default_frequency' => 'QDS'],
            ['name' => 'Cefixime',                'generic_name' => 'Cefixime',            'category' => 'Antibiotic',         'form' => 'Tablet',     'strength' => '200mg',       'default_route' => 'Oral',       'default_frequency' => 'BD'],
            ['name' => 'Chloramphenicol',         'generic_name' => 'Chloramphenicol',     'category' => 'Antibiotic',         'form' => 'Capsule',    'strength' => '250mg',       'default_route' => 'Oral',       'default_frequency' => 'QDS'],

            // ═══════════════════════════════════════════════════════════════
            //  ANTIMALARIALS
            // ═══════════════════════════════════════════════════════════════
            ['name' => 'Artemether/Lumefantrine',  'generic_name' => 'AL (Coartem)',       'category' => 'Antimalarial',       'form' => 'Tablet',     'strength' => '20/120mg',    'default_route' => 'Oral',       'default_frequency' => 'BD'],
            ['name' => 'Artesunate',               'generic_name' => 'Artesunate',         'category' => 'Antimalarial',       'form' => 'Injection',  'strength' => '60mg',        'default_route' => 'IV',         'default_frequency' => 'Stat'],
            ['name' => 'Quinine',                  'generic_name' => 'Quinine Dihydrochloride','category' => 'Antimalarial',   'form' => 'Injection',  'strength' => '600mg/2ml',   'default_route' => 'IV',         'default_frequency' => 'TDS'],
            ['name' => 'Quinine',                  'generic_name' => 'Quinine Sulfate',    'category' => 'Antimalarial',       'form' => 'Tablet',     'strength' => '300mg',       'default_route' => 'Oral',       'default_frequency' => 'TDS'],
            ['name' => 'SP (Fansidar)',            'generic_name' => 'Sulfadoxine/Pyrimethamine','category' => 'Antimalarial', 'form' => 'Tablet',     'strength' => '500/25mg',    'default_route' => 'Oral',       'default_frequency' => 'Stat', 'notes' => 'IPTp in pregnancy'],

            // ═══════════════════════════════════════════════════════════════
            //  ANTIHISTAMINES & ALLERGY
            // ═══════════════════════════════════════════════════════════════
            ['name' => 'Chlorpheniramine',         'generic_name' => 'Chlorphenamine',     'category' => 'Antihistamine',      'form' => 'Tablet',     'strength' => '4mg',         'default_route' => 'Oral',       'default_frequency' => 'TDS'],
            ['name' => 'Chlorpheniramine',         'generic_name' => 'Chlorphenamine',     'category' => 'Antihistamine',      'form' => 'Injection',  'strength' => '10mg/ml',     'default_route' => 'IM',         'default_frequency' => 'Stat'],
            ['name' => 'Cetirizine',               'generic_name' => 'Cetirizine',         'category' => 'Antihistamine',      'form' => 'Tablet',     'strength' => '10mg',        'default_route' => 'Oral',       'default_frequency' => 'OD'],
            ['name' => 'Loratadine',               'generic_name' => 'Loratadine',         'category' => 'Antihistamine',      'form' => 'Tablet',     'strength' => '10mg',        'default_route' => 'Oral',       'default_frequency' => 'OD'],
            ['name' => 'Promethazine',             'generic_name' => 'Promethazine',       'category' => 'Antihistamine',      'form' => 'Tablet',     'strength' => '25mg',        'default_route' => 'Oral',       'default_frequency' => 'TDS'],
            ['name' => 'Promethazine',             'generic_name' => 'Promethazine',       'category' => 'Antihistamine',      'form' => 'Injection',  'strength' => '25mg/ml',     'default_route' => 'IM',         'default_frequency' => 'Stat'],
            ['name' => 'Hydrocortisone',           'generic_name' => 'Hydrocortisone',     'category' => 'Corticosteroid',     'form' => 'Injection',  'strength' => '100mg',       'default_route' => 'IV',         'default_frequency' => 'Stat'],
            ['name' => 'Epinephrine (Adrenaline)', 'generic_name' => 'Epinephrine',        'category' => 'Emergency',          'form' => 'Injection',  'strength' => '1mg/ml',      'default_route' => 'IM',         'default_frequency' => 'Stat', 'notes' => 'Anaphylaxis first-line'],

            // ═══════════════════════════════════════════════════════════════
            //  ANTIHYPERTENSIVES & CARDIOVASCULAR
            // ═══════════════════════════════════════════════════════════════
            ['name' => 'Amlodipine',               'generic_name' => 'Amlodipine',         'category' => 'Antihypertensive',   'form' => 'Tablet',     'strength' => '5mg',         'default_route' => 'Oral',       'default_frequency' => 'OD'],
            ['name' => 'Amlodipine',               'generic_name' => 'Amlodipine',         'category' => 'Antihypertensive',   'form' => 'Tablet',     'strength' => '10mg',        'default_route' => 'Oral',       'default_frequency' => 'OD'],
            ['name' => 'Enalapril',                'generic_name' => 'Enalapril',          'category' => 'Antihypertensive',   'form' => 'Tablet',     'strength' => '5mg',         'default_route' => 'Oral',       'default_frequency' => 'OD'],
            ['name' => 'Enalapril',                'generic_name' => 'Enalapril',          'category' => 'Antihypertensive',   'form' => 'Tablet',     'strength' => '10mg',        'default_route' => 'Oral',       'default_frequency' => 'OD'],
            ['name' => 'Losartan',                 'generic_name' => 'Losartan',           'category' => 'Antihypertensive',   'form' => 'Tablet',     'strength' => '50mg',        'default_route' => 'Oral',       'default_frequency' => 'OD'],
            ['name' => 'Hydrochlorothiazide',      'generic_name' => 'HCTZ',               'category' => 'Antihypertensive',   'form' => 'Tablet',     'strength' => '25mg',        'default_route' => 'Oral',       'default_frequency' => 'OD'],
            ['name' => 'Nifedipine',               'generic_name' => 'Nifedipine',         'category' => 'Antihypertensive',   'form' => 'Tablet',     'strength' => '20mg',        'default_route' => 'Oral',       'default_frequency' => 'BD'],
            ['name' => 'Methyldopa',               'generic_name' => 'Methyldopa',         'category' => 'Antihypertensive',   'form' => 'Tablet',     'strength' => '250mg',       'default_route' => 'Oral',       'default_frequency' => 'TDS', 'notes' => 'Safe in pregnancy'],
            ['name' => 'Atenolol',                 'generic_name' => 'Atenolol',           'category' => 'Antihypertensive',   'form' => 'Tablet',     'strength' => '50mg',        'default_route' => 'Oral',       'default_frequency' => 'OD'],
            ['name' => 'Propranolol',              'generic_name' => 'Propranolol',        'category' => 'Antihypertensive',   'form' => 'Tablet',     'strength' => '40mg',        'default_route' => 'Oral',       'default_frequency' => 'TDS'],
            ['name' => 'Furosemide',               'generic_name' => 'Furosemide',         'category' => 'Diuretic',           'form' => 'Tablet',     'strength' => '40mg',        'default_route' => 'Oral',       'default_frequency' => 'OD'],
            ['name' => 'Furosemide',               'generic_name' => 'Furosemide',         'category' => 'Diuretic',           'form' => 'Injection',  'strength' => '20mg/2ml',    'default_route' => 'IV',         'default_frequency' => 'Stat'],
            ['name' => 'Spironolactone',           'generic_name' => 'Spironolactone',     'category' => 'Diuretic',           'form' => 'Tablet',     'strength' => '25mg',        'default_route' => 'Oral',       'default_frequency' => 'OD'],
            ['name' => 'Digoxin',                  'generic_name' => 'Digoxin',            'category' => 'Cardiovascular',     'form' => 'Tablet',     'strength' => '0.25mg',      'default_route' => 'Oral',       'default_frequency' => 'OD'],
            ['name' => 'Warfarin',                 'generic_name' => 'Warfarin',           'category' => 'Anticoagulant',      'form' => 'Tablet',     'strength' => '5mg',         'default_route' => 'Oral',       'default_frequency' => 'OD'],
            ['name' => 'Heparin',                  'generic_name' => 'Heparin Sodium',     'category' => 'Anticoagulant',      'form' => 'Injection',  'strength' => '5000IU/ml',   'default_route' => 'SC',         'default_frequency' => 'BD'],
            ['name' => 'Simvastatin',              'generic_name' => 'Simvastatin',        'category' => 'Cardiovascular',     'form' => 'Tablet',     'strength' => '20mg',        'default_route' => 'Oral',       'default_frequency' => 'OD'],

            // ═══════════════════════════════════════════════════════════════
            //  ANTIDIABETICS
            // ═══════════════════════════════════════════════════════════════
            ['name' => 'Metformin',                'generic_name' => 'Metformin HCl',      'category' => 'Antidiabetic',       'form' => 'Tablet',     'strength' => '500mg',       'default_route' => 'Oral',       'default_frequency' => 'BD'],
            ['name' => 'Metformin',                'generic_name' => 'Metformin HCl',      'category' => 'Antidiabetic',       'form' => 'Tablet',     'strength' => '850mg',       'default_route' => 'Oral',       'default_frequency' => 'BD'],
            ['name' => 'Glibenclamide',            'generic_name' => 'Glyburide',          'category' => 'Antidiabetic',       'form' => 'Tablet',     'strength' => '5mg',         'default_route' => 'Oral',       'default_frequency' => 'OD'],
            ['name' => 'Insulin Soluble',          'generic_name' => 'Regular Insulin',    'category' => 'Antidiabetic',       'form' => 'Injection',  'strength' => '100IU/ml',    'default_route' => 'SC',         'default_frequency' => 'TDS'],
            ['name' => 'Insulin NPH',              'generic_name' => 'Isophane Insulin',   'category' => 'Antidiabetic',       'form' => 'Injection',  'strength' => '100IU/ml',    'default_route' => 'SC',         'default_frequency' => 'BD'],
            ['name' => 'Insulin Mixed 70/30',      'generic_name' => 'Biphasic Insulin',   'category' => 'Antidiabetic',       'form' => 'Injection',  'strength' => '100IU/ml',    'default_route' => 'SC',         'default_frequency' => 'BD'],
            ['name' => 'Dextrose 50%',             'generic_name' => 'Glucose',            'category' => 'Antidiabetic',       'form' => 'Injection',  'strength' => '50%',         'default_route' => 'IV',         'default_frequency' => 'Stat', 'notes' => 'Hypoglycaemia rescue'],

            // ═══════════════════════════════════════════════════════════════
            //  RESPIRATORY
            // ═══════════════════════════════════════════════════════════════
            ['name' => 'Salbutamol',               'generic_name' => 'Albuterol',          'category' => 'Respiratory',        'form' => 'Inhaler',    'strength' => '100mcg/dose', 'default_route' => 'Inhaled',    'default_frequency' => 'PRN'],
            ['name' => 'Salbutamol',               'generic_name' => 'Albuterol',          'category' => 'Respiratory',        'form' => 'Nebuliser',  'strength' => '5mg/ml',      'default_route' => 'Inhaled',    'default_frequency' => 'PRN'],
            ['name' => 'Salbutamol',               'generic_name' => 'Albuterol',          'category' => 'Respiratory',        'form' => 'Tablet',     'strength' => '4mg',         'default_route' => 'Oral',       'default_frequency' => 'TDS'],
            ['name' => 'Beclomethasone',           'generic_name' => 'Beclomethasone',     'category' => 'Respiratory',        'form' => 'Inhaler',    'strength' => '100mcg/dose', 'default_route' => 'Inhaled',    'default_frequency' => 'BD'],
            ['name' => 'Prednisolone',             'generic_name' => 'Prednisolone',       'category' => 'Corticosteroid',     'form' => 'Tablet',     'strength' => '5mg',         'default_route' => 'Oral',       'default_frequency' => 'OD'],
            ['name' => 'Dexamethasone',            'generic_name' => 'Dexamethasone',      'category' => 'Corticosteroid',     'form' => 'Injection',  'strength' => '4mg/ml',      'default_route' => 'IV',         'default_frequency' => 'Stat'],
            ['name' => 'Dexamethasone',            'generic_name' => 'Dexamethasone',      'category' => 'Corticosteroid',     'form' => 'Tablet',     'strength' => '0.5mg',       'default_route' => 'Oral',       'default_frequency' => 'OD'],
            ['name' => 'Aminophylline',            'generic_name' => 'Aminophylline',      'category' => 'Respiratory',        'form' => 'Injection',  'strength' => '250mg/10ml',  'default_route' => 'IV',         'default_frequency' => 'Stat'],
            ['name' => 'Aminophylline',            'generic_name' => 'Aminophylline',      'category' => 'Respiratory',        'form' => 'Tablet',     'strength' => '100mg',       'default_route' => 'Oral',       'default_frequency' => 'TDS'],
            ['name' => 'Ipratropium',              'generic_name' => 'Ipratropium Bromide','category' => 'Respiratory',        'form' => 'Nebuliser',  'strength' => '250mcg/ml',   'default_route' => 'Inhaled',    'default_frequency' => 'TDS'],

            // ═══════════════════════════════════════════════════════════════
            //  GASTROINTESTINAL
            // ═══════════════════════════════════════════════════════════════
            ['name' => 'Omeprazole',               'generic_name' => 'Omeprazole',         'category' => 'Gastrointestinal',   'form' => 'Capsule',    'strength' => '20mg',        'default_route' => 'Oral',       'default_frequency' => 'OD'],
            ['name' => 'Omeprazole',               'generic_name' => 'Omeprazole',         'category' => 'Gastrointestinal',   'form' => 'Injection',  'strength' => '40mg',        'default_route' => 'IV',         'default_frequency' => 'OD'],
            ['name' => 'Ranitidine',               'generic_name' => 'Ranitidine',         'category' => 'Gastrointestinal',   'form' => 'Tablet',     'strength' => '150mg',       'default_route' => 'Oral',       'default_frequency' => 'BD'],
            ['name' => 'Magnesium Trisilicate',    'generic_name' => 'Antacid',            'category' => 'Gastrointestinal',   'form' => 'Tablet',     'strength' => '500mg',       'default_route' => 'Oral',       'default_frequency' => 'TDS'],
            ['name' => 'Magnesium Trisilicate',    'generic_name' => 'Antacid',            'category' => 'Gastrointestinal',   'form' => 'Suspension', 'strength' => '500mg/5ml',   'default_route' => 'Oral',       'default_frequency' => 'TDS'],
            ['name' => 'Metoclopramide',           'generic_name' => 'Metoclopramide',     'category' => 'Gastrointestinal',   'form' => 'Tablet',     'strength' => '10mg',        'default_route' => 'Oral',       'default_frequency' => 'TDS'],
            ['name' => 'Metoclopramide',           'generic_name' => 'Metoclopramide',     'category' => 'Gastrointestinal',   'form' => 'Injection',  'strength' => '10mg/2ml',    'default_route' => 'IM',         'default_frequency' => 'TDS'],
            ['name' => 'Ondansetron',              'generic_name' => 'Ondansetron',        'category' => 'Gastrointestinal',   'form' => 'Tablet',     'strength' => '4mg',         'default_route' => 'Oral',       'default_frequency' => 'TDS'],
            ['name' => 'Ondansetron',              'generic_name' => 'Ondansetron',        'category' => 'Gastrointestinal',   'form' => 'Injection',  'strength' => '4mg/2ml',     'default_route' => 'IV',         'default_frequency' => 'TDS'],
            ['name' => 'Hyoscine Butylbromide',    'generic_name' => 'Buscopan',           'category' => 'Gastrointestinal',   'form' => 'Tablet',     'strength' => '10mg',        'default_route' => 'Oral',       'default_frequency' => 'TDS'],
            ['name' => 'Hyoscine Butylbromide',    'generic_name' => 'Buscopan',           'category' => 'Gastrointestinal',   'form' => 'Injection',  'strength' => '20mg/ml',     'default_route' => 'IM',         'default_frequency' => 'TDS'],
            ['name' => 'Loperamide',               'generic_name' => 'Loperamide',         'category' => 'Gastrointestinal',   'form' => 'Capsule',    'strength' => '2mg',         'default_route' => 'Oral',       'default_frequency' => 'PRN'],
            ['name' => 'ORS',                      'generic_name' => 'Oral Rehydration Salts','category' => 'Gastrointestinal','form' => 'Sachet',     'strength' => '20.5g/L',     'default_route' => 'Oral',       'default_frequency' => 'PRN'],
            ['name' => 'Zinc Sulfate',             'generic_name' => 'Zinc',               'category' => 'Gastrointestinal',   'form' => 'Tablet',     'strength' => '20mg',        'default_route' => 'Oral',       'default_frequency' => 'OD', 'notes' => 'Paediatric diarrhoea'],
            ['name' => 'Lactulose',                'generic_name' => 'Lactulose',          'category' => 'Gastrointestinal',   'form' => 'Syrup',      'strength' => '3.35g/5ml',   'default_route' => 'Oral',       'default_frequency' => 'BD'],
            ['name' => 'Bisacodyl',                'generic_name' => 'Bisacodyl',          'category' => 'Gastrointestinal',   'form' => 'Tablet',     'strength' => '5mg',         'default_route' => 'Oral',       'default_frequency' => 'OD'],

            // ═══════════════════════════════════════════════════════════════
            //  ANTICONVULSANTS / NEUROLOGICAL
            // ═══════════════════════════════════════════════════════════════
            ['name' => 'Diazepam',                 'generic_name' => 'Diazepam',           'category' => 'Anticonvulsant',     'form' => 'Injection',  'strength' => '10mg/2ml',    'default_route' => 'IV',         'default_frequency' => 'Stat', 'is_controlled' => true],
            ['name' => 'Diazepam',                 'generic_name' => 'Diazepam',           'category' => 'Anticonvulsant',     'form' => 'Tablet',     'strength' => '5mg',         'default_route' => 'Oral',       'default_frequency' => 'TDS', 'is_controlled' => true],
            ['name' => 'Diazepam',                 'generic_name' => 'Diazepam',           'category' => 'Anticonvulsant',     'form' => 'Rectal Tube','strength' => '10mg/2.5ml',  'default_route' => 'Rectal',     'default_frequency' => 'Stat', 'is_controlled' => true],
            ['name' => 'Phenobarbital',            'generic_name' => 'Phenobarbitone',     'category' => 'Anticonvulsant',     'form' => 'Tablet',     'strength' => '30mg',        'default_route' => 'Oral',       'default_frequency' => 'OD', 'is_controlled' => true],
            ['name' => 'Phenobarbital',            'generic_name' => 'Phenobarbitone',     'category' => 'Anticonvulsant',     'form' => 'Injection',  'strength' => '200mg/ml',    'default_route' => 'IM',         'default_frequency' => 'Stat', 'is_controlled' => true],
            ['name' => 'Phenytoin',                'generic_name' => 'Phenytoin Sodium',   'category' => 'Anticonvulsant',     'form' => 'Tablet',     'strength' => '100mg',       'default_route' => 'Oral',       'default_frequency' => 'BD'],
            ['name' => 'Carbamazepine',            'generic_name' => 'Carbamazepine',      'category' => 'Anticonvulsant',     'form' => 'Tablet',     'strength' => '200mg',       'default_route' => 'Oral',       'default_frequency' => 'BD'],
            ['name' => 'Sodium Valproate',         'generic_name' => 'Valproic Acid',      'category' => 'Anticonvulsant',     'form' => 'Tablet',     'strength' => '200mg',       'default_route' => 'Oral',       'default_frequency' => 'BD'],
            ['name' => 'Magnesium Sulfate',        'generic_name' => 'MgSO4',             'category' => 'Anticonvulsant',     'form' => 'Injection',  'strength' => '50%',         'default_route' => 'IV',         'default_frequency' => 'Stat', 'notes' => 'Eclampsia/pre-eclampsia'],

            // ═══════════════════════════════════════════════════════════════
            //  ANTIRETROVIRALS (ARVs)
            // ═══════════════════════════════════════════════════════════════
            ['name' => 'TLD',                      'generic_name' => 'Tenofovir/Lamivudine/Dolutegravir','category' => 'ARV', 'form' => 'Tablet',     'strength' => '300/300/50mg','default_route' => 'Oral',       'default_frequency' => 'OD'],
            ['name' => 'TLE',                      'generic_name' => 'Tenofovir/Lamivudine/Efavirenz',  'category' => 'ARV', 'form' => 'Tablet',     'strength' => '300/300/600mg','default_route' => 'Oral',      'default_frequency' => 'OD'],
            ['name' => 'AZT/3TC/NVP',             'generic_name' => 'Zidovudine/Lamivudine/Nevirapine', 'category' => 'ARV', 'form' => 'Tablet',     'strength' => '300/150/200mg','default_route' => 'Oral',      'default_frequency' => 'BD'],
            ['name' => 'ABC/3TC',                  'generic_name' => 'Abacavir/Lamivudine',              'category' => 'ARV', 'form' => 'Tablet',     'strength' => '600/300mg',   'default_route' => 'Oral',       'default_frequency' => 'OD'],
            ['name' => 'Nevirapine',               'generic_name' => 'Nevirapine',         'category' => 'ARV',               'form' => 'Syrup',      'strength' => '10mg/ml',     'default_route' => 'Oral',       'default_frequency' => 'BD'],
            ['name' => 'Lopinavir/Ritonavir',      'generic_name' => 'LPV/r',              'category' => 'ARV',               'form' => 'Tablet',     'strength' => '200/50mg',    'default_route' => 'Oral',       'default_frequency' => 'BD'],
            ['name' => 'Atazanavir/Ritonavir',     'generic_name' => 'ATV/r',              'category' => 'ARV',               'form' => 'Tablet',     'strength' => '300/100mg',   'default_route' => 'Oral',       'default_frequency' => 'OD'],

            // ═══════════════════════════════════════════════════════════════
            //  ANTI-TB
            // ═══════════════════════════════════════════════════════════════
            ['name' => 'RHZE',                     'generic_name' => 'Rifampicin/Isoniazid/Pyrazinamide/Ethambutol','category' => 'Anti-TB','form' => 'Tablet','strength' => '150/75/400/275mg','default_route' => 'Oral','default_frequency' => 'OD'],
            ['name' => 'RH',                       'generic_name' => 'Rifampicin/Isoniazid','category' => 'Anti-TB',          'form' => 'Tablet',     'strength' => '150/75mg',    'default_route' => 'Oral',       'default_frequency' => 'OD'],
            ['name' => 'Isoniazid',                'generic_name' => 'INH',                'category' => 'Anti-TB',           'form' => 'Tablet',     'strength' => '100mg',       'default_route' => 'Oral',       'default_frequency' => 'OD'],
            ['name' => 'Pyridoxine',               'generic_name' => 'Vitamin B6',         'category' => 'Vitamin',           'form' => 'Tablet',     'strength' => '25mg',        'default_route' => 'Oral',       'default_frequency' => 'OD', 'notes' => 'Prevents INH neuropathy'],

            // ═══════════════════════════════════════════════════════════════
            //  ANTIFUNGALS
            // ═══════════════════════════════════════════════════════════════
            ['name' => 'Fluconazole',              'generic_name' => 'Fluconazole',        'category' => 'Antifungal',         'form' => 'Capsule',    'strength' => '150mg',       'default_route' => 'Oral',       'default_frequency' => 'Stat'],
            ['name' => 'Fluconazole',              'generic_name' => 'Fluconazole',        'category' => 'Antifungal',         'form' => 'Capsule',    'strength' => '200mg',       'default_route' => 'Oral',       'default_frequency' => 'OD'],
            ['name' => 'Nystatin',                 'generic_name' => 'Nystatin',           'category' => 'Antifungal',         'form' => 'Oral Drops', 'strength' => '100000IU/ml', 'default_route' => 'Oral',       'default_frequency' => 'QDS'],
            ['name' => 'Clotrimazole',             'generic_name' => 'Clotrimazole',       'category' => 'Antifungal',         'form' => 'Cream',      'strength' => '1%',          'default_route' => 'Topical',    'default_frequency' => 'BD'],
            ['name' => 'Clotrimazole',             'generic_name' => 'Clotrimazole',       'category' => 'Antifungal',         'form' => 'Pessary',    'strength' => '500mg',       'default_route' => 'Vaginal',    'default_frequency' => 'Stat'],
            ['name' => 'Miconazole',               'generic_name' => 'Miconazole',         'category' => 'Antifungal',         'form' => 'Cream',      'strength' => '2%',          'default_route' => 'Topical',    'default_frequency' => 'BD'],
            ['name' => 'Griseofulvin',             'generic_name' => 'Griseofulvin',       'category' => 'Antifungal',         'form' => 'Tablet',     'strength' => '500mg',       'default_route' => 'Oral',       'default_frequency' => 'OD'],

            // ═══════════════════════════════════════════════════════════════
            //  ANTHELMINTICS & ANTIPARASITICS
            // ═══════════════════════════════════════════════════════════════
            ['name' => 'Albendazole',              'generic_name' => 'Albendazole',        'category' => 'Anthelmintic',       'form' => 'Tablet',     'strength' => '400mg',       'default_route' => 'Oral',       'default_frequency' => 'Stat'],
            ['name' => 'Mebendazole',              'generic_name' => 'Mebendazole',        'category' => 'Anthelmintic',       'form' => 'Tablet',     'strength' => '500mg',       'default_route' => 'Oral',       'default_frequency' => 'Stat'],
            ['name' => 'Praziquantel',             'generic_name' => 'Praziquantel',       'category' => 'Anthelmintic',       'form' => 'Tablet',     'strength' => '600mg',       'default_route' => 'Oral',       'default_frequency' => 'Stat'],
            ['name' => 'Ivermectin',               'generic_name' => 'Ivermectin',         'category' => 'Anthelmintic',       'form' => 'Tablet',     'strength' => '3mg',         'default_route' => 'Oral',       'default_frequency' => 'Stat'],
            ['name' => 'Permethrin',               'generic_name' => 'Permethrin',         'category' => 'Antiparasitic',      'form' => 'Cream',      'strength' => '5%',          'default_route' => 'Topical',    'default_frequency' => 'Stat'],
            ['name' => 'Benzyl Benzoate',          'generic_name' => 'Benzyl Benzoate',    'category' => 'Antiparasitic',      'form' => 'Lotion',     'strength' => '25%',         'default_route' => 'Topical',    'default_frequency' => 'Stat'],

            // ═══════════════════════════════════════════════════════════════
            //  VITAMINS & MINERALS / HAEMATINICS
            // ═══════════════════════════════════════════════════════════════
            ['name' => 'Ferrous Sulfate',          'generic_name' => 'Iron',               'category' => 'Haematinic',         'form' => 'Tablet',     'strength' => '200mg',       'default_route' => 'Oral',       'default_frequency' => 'TDS'],
            ['name' => 'Folic Acid',               'generic_name' => 'Folic Acid',         'category' => 'Haematinic',         'form' => 'Tablet',     'strength' => '5mg',         'default_route' => 'Oral',       'default_frequency' => 'OD'],
            ['name' => 'Vitamin A',                'generic_name' => 'Retinol',            'category' => 'Vitamin',            'form' => 'Capsule',    'strength' => '200000IU',    'default_route' => 'Oral',       'default_frequency' => 'Stat'],
            ['name' => 'Vitamin B Complex',        'generic_name' => 'B-Complex',          'category' => 'Vitamin',            'form' => 'Tablet',     'strength' => null,          'default_route' => 'Oral',       'default_frequency' => 'OD'],
            ['name' => 'Vitamin C',                'generic_name' => 'Ascorbic Acid',      'category' => 'Vitamin',            'form' => 'Tablet',     'strength' => '100mg',       'default_route' => 'Oral',       'default_frequency' => 'OD'],
            ['name' => 'Vitamin K (Phytomenadione)','generic_name' => 'Vitamin K1',        'category' => 'Vitamin',            'form' => 'Injection',  'strength' => '1mg/0.5ml',   'default_route' => 'IM',         'default_frequency' => 'Stat', 'notes' => 'Neonatal prophylaxis'],
            ['name' => 'Calcium Gluconate',        'generic_name' => 'Calcium',            'category' => 'Mineral',            'form' => 'Injection',  'strength' => '10%',         'default_route' => 'IV',         'default_frequency' => 'Stat'],
            ['name' => 'Multivitamin',             'generic_name' => 'Multivitamins',      'category' => 'Vitamin',            'form' => 'Tablet',     'strength' => null,          'default_route' => 'Oral',       'default_frequency' => 'OD'],
            ['name' => 'Multivitamin',             'generic_name' => 'Multivitamins',      'category' => 'Vitamin',            'form' => 'Syrup',      'strength' => null,          'default_route' => 'Oral',       'default_frequency' => 'OD'],

            // ═══════════════════════════════════════════════════════════════
            //  IV FLUIDS
            // ═══════════════════════════════════════════════════════════════
            ['name' => 'Normal Saline',            'generic_name' => 'Sodium Chloride 0.9%','category' => 'IV Fluid',          'form' => 'IV Bag',     'strength' => '1000ml',      'default_route' => 'IV',         'default_frequency' => 'PRN'],
            ['name' => 'Normal Saline',            'generic_name' => 'Sodium Chloride 0.9%','category' => 'IV Fluid',          'form' => 'IV Bag',     'strength' => '500ml',       'default_route' => 'IV',         'default_frequency' => 'PRN'],
            ['name' => 'Ringers Lactate',          'generic_name' => 'Hartmanns Solution', 'category' => 'IV Fluid',           'form' => 'IV Bag',     'strength' => '1000ml',      'default_route' => 'IV',         'default_frequency' => 'PRN'],
            ['name' => 'Dextrose 5%',              'generic_name' => 'Glucose 5%',         'category' => 'IV Fluid',           'form' => 'IV Bag',     'strength' => '1000ml',      'default_route' => 'IV',         'default_frequency' => 'PRN'],
            ['name' => 'Dextrose 10%',             'generic_name' => 'Glucose 10%',        'category' => 'IV Fluid',           'form' => 'IV Bag',     'strength' => '500ml',       'default_route' => 'IV',         'default_frequency' => 'PRN'],
            ['name' => 'Dextrose/Saline',          'generic_name' => 'D5NS',               'category' => 'IV Fluid',           'form' => 'IV Bag',     'strength' => '1000ml',      'default_route' => 'IV',         'default_frequency' => 'PRN'],

            // ═══════════════════════════════════════════════════════════════
            //  OBSTETRIC & GYNAECOLOGICAL
            // ═══════════════════════════════════════════════════════════════
            ['name' => 'Oxytocin',                 'generic_name' => 'Oxytocin',           'category' => 'Obstetric',          'form' => 'Injection',  'strength' => '10IU/ml',     'default_route' => 'IV',         'default_frequency' => 'Stat'],
            ['name' => 'Misoprostol',              'generic_name' => 'Misoprostol',        'category' => 'Obstetric',          'form' => 'Tablet',     'strength' => '200mcg',      'default_route' => 'Sublingual', 'default_frequency' => 'Stat'],
            ['name' => 'Ergometrine',              'generic_name' => 'Ergometrine',        'category' => 'Obstetric',          'form' => 'Injection',  'strength' => '0.5mg/ml',    'default_route' => 'IM',         'default_frequency' => 'Stat'],
            ['name' => 'Tranexamic Acid',          'generic_name' => 'TXA',                'category' => 'Obstetric',          'form' => 'Injection',  'strength' => '100mg/ml',    'default_route' => 'IV',         'default_frequency' => 'Stat', 'notes' => 'PPH adjunct'],

            // ═══════════════════════════════════════════════════════════════
            //  DERMATOLOGICAL
            // ═══════════════════════════════════════════════════════════════
            ['name' => 'Silver Sulfadiazine',      'generic_name' => 'Silver Sulfadiazine','category' => 'Dermatological',     'form' => 'Cream',      'strength' => '1%',          'default_route' => 'Topical',    'default_frequency' => 'OD', 'notes' => 'Burns'],
            ['name' => 'Calamine Lotion',          'generic_name' => 'Calamine',           'category' => 'Dermatological',     'form' => 'Lotion',     'strength' => null,          'default_route' => 'Topical',    'default_frequency' => 'PRN'],
            ['name' => 'Gentian Violet',           'generic_name' => 'Crystal Violet',     'category' => 'Dermatological',     'form' => 'Solution',   'strength' => '0.5%',        'default_route' => 'Topical',    'default_frequency' => 'OD'],
            ['name' => 'Hydrocortisone Cream',     'generic_name' => 'Hydrocortisone',     'category' => 'Dermatological',     'form' => 'Cream',      'strength' => '1%',          'default_route' => 'Topical',    'default_frequency' => 'BD'],
            ['name' => 'Betamethasone Cream',      'generic_name' => 'Betamethasone',      'category' => 'Dermatological',     'form' => 'Cream',      'strength' => '0.1%',        'default_route' => 'Topical',    'default_frequency' => 'BD'],
            ['name' => 'Whitfields Ointment',      'generic_name' => 'Benzoic/Salicylic',  'category' => 'Dermatological',     'form' => 'Ointment',   'strength' => null,          'default_route' => 'Topical',    'default_frequency' => 'BD'],

            // ═══════════════════════════════════════════════════════════════
            //  OPHTHALMIC
            // ═══════════════════════════════════════════════════════════════
            ['name' => 'Chloramphenicol Eye Drops', 'generic_name' => 'Chloramphenicol',   'category' => 'Ophthalmic',         'form' => 'Eye Drops',  'strength' => '0.5%',        'default_route' => 'Ophthalmic', 'default_frequency' => 'QDS'],
            ['name' => 'Tetracycline Eye Ointment', 'generic_name' => 'Tetracycline',      'category' => 'Ophthalmic',         'form' => 'Eye Ointment','strength' => '1%',         'default_route' => 'Ophthalmic', 'default_frequency' => 'TDS'],
            ['name' => 'Gentamicin Eye Drops',      'generic_name' => 'Gentamicin',        'category' => 'Ophthalmic',         'form' => 'Eye Drops',  'strength' => '0.3%',        'default_route' => 'Ophthalmic', 'default_frequency' => 'QDS'],
            ['name' => 'Atropine Eye Drops',        'generic_name' => 'Atropine Sulfate',  'category' => 'Ophthalmic',         'form' => 'Eye Drops',  'strength' => '1%',          'default_route' => 'Ophthalmic', 'default_frequency' => 'BD'],

            // ═══════════════════════════════════════════════════════════════
            //  ENT
            // ═══════════════════════════════════════════════════════════════
            ['name' => 'Ciprofloxacin Ear Drops',  'generic_name' => 'Ciprofloxacin',      'category' => 'ENT',                'form' => 'Ear Drops',  'strength' => '0.3%',        'default_route' => 'Otic',       'default_frequency' => 'BD'],
            ['name' => 'Hydrogen Peroxide',        'generic_name' => 'H2O2',               'category' => 'ENT',                'form' => 'Ear Drops',  'strength' => '3%',          'default_route' => 'Otic',       'default_frequency' => 'TDS'],

            // ═══════════════════════════════════════════════════════════════
            //  EMERGENCY / RESUSCITATION
            // ═══════════════════════════════════════════════════════════════
            ['name' => 'Atropine',                 'generic_name' => 'Atropine Sulfate',   'category' => 'Emergency',          'form' => 'Injection',  'strength' => '0.6mg/ml',    'default_route' => 'IV',         'default_frequency' => 'Stat'],
            ['name' => 'Naloxone',                 'generic_name' => 'Naloxone',           'category' => 'Emergency',          'form' => 'Injection',  'strength' => '0.4mg/ml',    'default_route' => 'IV',         'default_frequency' => 'Stat', 'notes' => 'Opioid reversal'],
            ['name' => 'Dopamine',                 'generic_name' => 'Dopamine HCl',       'category' => 'Emergency',          'form' => 'Injection',  'strength' => '200mg/5ml',   'default_route' => 'IV',         'default_frequency' => 'PRN'],
            ['name' => 'Aminophylline',            'generic_name' => 'Aminophylline',      'category' => 'Emergency',          'form' => 'Injection',  'strength' => '250mg/10ml',  'default_route' => 'IV',         'default_frequency' => 'Stat'],
            ['name' => 'Lidocaine',                'generic_name' => 'Lignocaine',         'category' => 'Anaesthetic',        'form' => 'Injection',  'strength' => '1%',          'default_route' => 'SC',         'default_frequency' => 'Stat'],
            ['name' => 'Lidocaine',                'generic_name' => 'Lignocaine',         'category' => 'Anaesthetic',        'form' => 'Injection',  'strength' => '2%',          'default_route' => 'SC',         'default_frequency' => 'Stat'],
            ['name' => 'Ketamine',                 'generic_name' => 'Ketamine',           'category' => 'Anaesthetic',        'form' => 'Injection',  'strength' => '50mg/ml',     'default_route' => 'IV',         'default_frequency' => 'Stat', 'is_controlled' => true],

            // ═══════════════════════════════════════════════════════════════
            //  PSYCHIATRIC
            // ═══════════════════════════════════════════════════════════════
            ['name' => 'Amitriptyline',            'generic_name' => 'Amitriptyline',      'category' => 'Psychiatric',        'form' => 'Tablet',     'strength' => '25mg',        'default_route' => 'Oral',       'default_frequency' => 'OD'],
            ['name' => 'Haloperidol',              'generic_name' => 'Haloperidol',        'category' => 'Psychiatric',        'form' => 'Tablet',     'strength' => '5mg',         'default_route' => 'Oral',       'default_frequency' => 'BD'],
            ['name' => 'Haloperidol',              'generic_name' => 'Haloperidol',        'category' => 'Psychiatric',        'form' => 'Injection',  'strength' => '5mg/ml',      'default_route' => 'IM',         'default_frequency' => 'Stat'],
            ['name' => 'Chlorpromazine',           'generic_name' => 'Chlorpromazine',     'category' => 'Psychiatric',        'form' => 'Tablet',     'strength' => '100mg',       'default_route' => 'Oral',       'default_frequency' => 'TDS'],
            ['name' => 'Chlorpromazine',           'generic_name' => 'Chlorpromazine',     'category' => 'Psychiatric',        'form' => 'Injection',  'strength' => '50mg/2ml',    'default_route' => 'IM',         'default_frequency' => 'Stat'],
            ['name' => 'Fluoxetine',               'generic_name' => 'Fluoxetine',         'category' => 'Psychiatric',        'form' => 'Capsule',    'strength' => '20mg',        'default_route' => 'Oral',       'default_frequency' => 'OD'],
            ['name' => 'Risperidone',              'generic_name' => 'Risperidone',        'category' => 'Psychiatric',        'form' => 'Tablet',     'strength' => '2mg',         'default_route' => 'Oral',       'default_frequency' => 'BD'],
            ['name' => 'Fluphenazine Decanoate',   'generic_name' => 'Fluphenazine',       'category' => 'Psychiatric',        'form' => 'Injection',  'strength' => '25mg/ml',     'default_route' => 'IM',         'default_frequency' => 'Monthly'],

            // ═══════════════════════════════════════════════════════════════
            //  VACCINES (commonly stocked at health centers)
            // ═══════════════════════════════════════════════════════════════
            ['name' => 'Tetanus Toxoid',           'generic_name' => 'TT',                 'category' => 'Vaccine',            'form' => 'Injection',  'strength' => '0.5ml',       'default_route' => 'IM',         'default_frequency' => 'Stat'],
            ['name' => 'Anti-Rabies Vaccine',      'generic_name' => 'Rabies Vaccine',     'category' => 'Vaccine',            'form' => 'Injection',  'strength' => '1ml',         'default_route' => 'IM',         'default_frequency' => 'Stat'],
            ['name' => 'Hepatitis B Vaccine',      'generic_name' => 'HBV Vaccine',        'category' => 'Vaccine',            'form' => 'Injection',  'strength' => '1ml',         'default_route' => 'IM',         'default_frequency' => 'Stat'],

            // ═══════════════════════════════════════════════════════════════
            //  ANTISEPTICS & WOUND CARE
            // ═══════════════════════════════════════════════════════════════
            ['name' => 'Povidone Iodine',          'generic_name' => 'Betadine',           'category' => 'Antiseptic',         'form' => 'Solution',   'strength' => '10%',         'default_route' => 'Topical',    'default_frequency' => 'PRN'],
            ['name' => 'Chlorhexidine',            'generic_name' => 'Chlorhexidine',      'category' => 'Antiseptic',         'form' => 'Solution',   'strength' => '0.05%',       'default_route' => 'Topical',    'default_frequency' => 'PRN'],
            ['name' => 'Chlorhexidine Gel',        'generic_name' => 'Chlorhexidine',      'category' => 'Antiseptic',         'form' => 'Gel',        'strength' => '7.1%',        'default_route' => 'Topical',    'default_frequency' => 'Stat', 'notes' => 'Umbilical cord care'],
            ['name' => 'Hydrogen Peroxide',        'generic_name' => 'H2O2',               'category' => 'Antiseptic',         'form' => 'Solution',   'strength' => '3%',          'default_route' => 'Topical',    'default_frequency' => 'PRN'],
        ];
    }
}
