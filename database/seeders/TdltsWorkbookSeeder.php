<?php

namespace Database\Seeders;

use App\Support\TdltsBarcodeGenerator;
use App\Support\SimpleXlsxReader;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TdltsWorkbookSeeder extends Seeder
{
    /**
     * Seed workbook data into local SQL tables.
     */
    public function run(): void
    {
        ini_set('memory_limit', '512M');

        $workbookPath = base_path('TDLTS-Patient-Data.xlsx');

        if (!is_file($workbookPath)) {
            $this->command?->warn('Workbook not found: ' . $workbookPath);
            return;
        }

        $reader = new SimpleXlsxReader($workbookPath);

        $households = array_map(function (array $row): array {
            $householdId = $this->asString($row['Household ID'] ?? null);
            $headOfHouse = $this->asString($row['Head of House'] ?? null);
            $nrcNumber = $this->asString($row['NRC Number'] ?? null);
            $barcode = $this->asString($row['Barcode'] ?? null);

            if ($barcode === null) {
                $barcode = TdltsBarcodeGenerator::generate('H', $nrcNumber ?? $householdId ?? $headOfHouse);
            }

            return [
                'household_id'      => $householdId,
                'head_of_house'     => $headOfHouse,
                'nrc_number'        => $nrcNumber,
                'phone_number'      => $this->asString($row['Phone Number'] ?? null),
                'village'           => $this->asString($row['Village'] ?? null),
                'town'              => $this->asString($row['Town'] ?? null),
                'household_type'    => $this->asString($row['Household Type'] ?? null),
                'barcode'           => $barcode,
                'subscription_plan' => $this->asString($row['Subscription Plan'] ?? null),
                'subscription_fee'  => $this->asDecimal($row['Subscription Fee'] ?? null),
                'payment_method'    => $this->asString($row['Payment Method'] ?? null),
                'payment_status'    => $this->asString($row['Payment Status'] ?? null),
                'transaction_code'  => $this->asString($row['Transaction Code'] ?? null),
                'source_created_at' => $this->asDateTime($row['Created At'] ?? null),
                'created_at'        => now(),
                'updated_at'        => now(),
            ];
        }, $reader->rows('Households'));

        $patients = array_map(function (array $row): array {
            $patientId = $this->asString($row['Patient ID'] ?? null);
            $fullName = $this->asString($row['Full Name'] ?? null);
            $nrcNumber = $this->asString($row['NRC Number'] ?? null);
            $barcode = $this->asString($row['Barcode'] ?? null);

            if ($barcode === null) {
                $barcode = TdltsBarcodeGenerator::generate('P', $nrcNumber ?? $fullName ?? $patientId);
            }

            return [
                'patient_id'               => $patientId,
                'full_name'                => $fullName,
                'gender'                   => $this->asString($row['Gender'] ?? null),
                'date_of_birth'            => $this->asDate($row['Date of Birth'] ?? null),
                'nrc_number'               => $nrcNumber,
                'phone_number'             => $this->asString($row['Phone Number'] ?? null),
                'relationship_to_head'     => $this->asString($row['Relationship to Head'] ?? null),
                'household_head_of_house'  => $this->asString($row['Household (Head of House)'] ?? null),
                'household_id'             => $this->asString($row['Household ID'] ?? null),
                'barcode'                  => $barcode,
                'source_created_at'        => $this->asDateTime($row['Created At'] ?? null),
                'created_at'               => now(),
                'updated_at'               => now(),
            ];
        }, $reader->rows('Patients'));

        $shiftReports = array_map(fn (array $row) => [
            'report_date'          => $this->asDate($row['Date'] ?? null),
            'shift_type'           => $this->asString($row['Shift Type'] ?? null),
            'total_patients_seen'  => $this->asInt($row['Total Patients Seen'] ?? null),
            'reported_by'          => $this->asString($row['Reported By'] ?? null),
            'source_created_at'    => $this->asDateTime($row['Created At'] ?? null),
            'created_at'           => now(),
            'updated_at'           => now(),
        ], $reader->rows('Shift Reports'));

        $impactNumbers = array_map(fn (array $row) => [
            'metric'      => $this->asString($row['Metric'] ?? null),
            'value'       => $this->asString($row['Value'] ?? null),
            'description' => $this->asString($row['Description'] ?? null),
            'created_at'  => now(),
            'updated_at'  => now(),
        ], $reader->rows('Impact Numbers'));

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('patients')->truncate();
        DB::table('households')->truncate();
        DB::table('shift_reports')->truncate();
        DB::table('impact_numbers')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->insertChunks('households', $households);
        $this->insertChunks('patients', $patients);
        $this->insertChunks('shift_reports', $shiftReports);
        $this->insertChunks('impact_numbers', $impactNumbers);

        $this->command?->info('Workbook import complete.');
        $this->command?->line('Imported households: ' . number_format(count($households)));
        $this->command?->line('Imported patients: ' . number_format(count($patients)));
        $this->command?->line('Imported shift reports: ' . number_format(count($shiftReports)));
        $this->command?->line('Imported impact numbers: ' . number_format(count($impactNumbers)));
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    private function insertChunks(string $table, array $rows, int $size = 500): void
    {
        foreach (array_chunk($rows, $size) as $chunk) {
            $filtered = array_values(array_filter($chunk, function (array $row): bool {
                return collect($row)->contains(fn ($v) => $v !== null && $v !== '');
            }));

            if ($filtered !== []) {
                DB::table($table)->insert($filtered);
            }
        }
    }

    private function asString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $str = trim((string) $value);
        return $str === '' ? null : $str;
    }

    private function asInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        $n = preg_replace('/[^0-9]/', '', (string) $value);
        return $n === '' ? null : (int) $n;
    }

    private function asDecimal(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $clean = preg_replace('/[^0-9.\-]/', '', (string) $value);
        return $clean === '' ? null : (float) $clean;
    }

    private function asDate(mixed $value): ?string
    {
        $dt = $this->parseDateValue($value);
        return $dt?->toDateString();
    }

    private function asDateTime(mixed $value): ?string
    {
        $dt = $this->parseDateValue($value);
        return $dt?->toDateTimeString();
    }

    private function parseDateValue(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            // Excel serial date (base 1899-12-30)
            $days = (float) $value;
            $seconds = (int) round(($days - 25569) * 86400);

            try {
                return Carbon::createFromTimestampUTC($seconds);
            } catch (\Throwable) {
                return null;
            }
        }

        if (!is_string($value)) {
            return null;
        }

        $text = trim($value);
        if ($text === '') {
            return null;
        }

        try {
            return Carbon::parse($text);
        } catch (\Throwable) {
            return null;
        }
    }
}
