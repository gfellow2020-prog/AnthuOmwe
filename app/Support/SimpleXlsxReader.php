<?php

namespace App\Support;

use RuntimeException;
use ZipArchive;

class SimpleXlsxReader
{
    private ZipArchive $zip;

    /** @var array<int, string> */
    private array $sharedStrings = [];

    /** @var array<string, string> */
    private array $sheetPaths = [];

    public function __construct(private readonly string $filePath)
    {
        $this->zip = new ZipArchive();

        if ($this->zip->open($this->filePath) !== true) {
            throw new RuntimeException('Could not open workbook: ' . $this->filePath);
        }

        $this->loadSharedStrings();
        $this->loadSheetPaths();
    }

    /**
     * Return sheet rows as associative arrays keyed by header names.
     *
     * @return array<int, array<string, mixed>>
     */
    public function rows(string $sheetName): array
    {
        if (!isset($this->sheetPaths[$sheetName])) {
            return [];
        }

        $wsXml = $this->zip->getFromName($this->sheetPaths[$sheetName]);
        if ($wsXml === false) {
            return [];
        }

        $sheet = simplexml_load_string($wsXml);
        if ($sheet === false || !isset($sheet->sheetData->row)) {
            return [];
        }

        $headers = [];
        $rows = [];

        $isHeaderRow = true;

        foreach ($sheet->sheetData->row as $row) {
            $valuesByIndex = [];

            foreach ($row->c as $cell) {
                $ref = (string) ($cell['r'] ?? '');
                $col = preg_replace('/\d+/', '', $ref) ?: 'A';
                $colIndex = $this->columnNameToIndex($col);

                $valuesByIndex[$colIndex] = $this->cellValue($cell);
            }

            if ($isHeaderRow) {
                if ($valuesByIndex === []) {
                    return [];
                }

                $max = max(array_keys($valuesByIndex));
                for ($i = 0; $i <= $max; $i++) {
                    $headers[$i] = trim((string) ($valuesByIndex[$i] ?? ''));
                }

                $isHeaderRow = false;

                continue;
            }

            if ($headers === []) {
                continue;
            }

            $assoc = [];
            foreach ($headers as $i => $header) {
                if ($header === '') {
                    continue;
                }

                $assoc[$header] = $valuesByIndex[$i] ?? null;
            }

            if ($assoc !== []) {
                $rows[] = $assoc;
            }
        }

        return $rows;
    }

    private function loadSharedStrings(): void
    {
        $xml = $this->zip->getFromName('xl/sharedStrings.xml');
        if ($xml === false) {
            return;
        }

        $shared = simplexml_load_string($xml);
        if ($shared === false) {
            return;
        }

        foreach ($shared->si as $si) {
            if (isset($si->t)) {
                $this->sharedStrings[] = (string) $si->t;
                continue;
            }

            $joined = '';
            foreach ($si->r as $r) {
                $joined .= (string) ($r->t ?? '');
            }
            $this->sharedStrings[] = $joined;
        }
    }

    private function loadSheetPaths(): void
    {
        $wbXml = $this->zip->getFromName('xl/workbook.xml');
        $relXml = $this->zip->getFromName('xl/_rels/workbook.xml.rels');

        if ($wbXml === false || $relXml === false) {
            return;
        }

        $wb = simplexml_load_string($wbXml);
        $rels = simplexml_load_string($relXml);
        if ($wb === false || $rels === false) {
            return;
        }

        $relMap = [];
        foreach ($rels->Relationship as $rel) {
            $attrs = $rel->attributes();
            $relMap[(string) $attrs['Id']] = 'xl/' . (string) $attrs['Target'];
        }

        foreach ($wb->sheets->sheet as $sheet) {
            $name = (string) $sheet['name'];
            $rid = (string) $sheet->attributes('r', true)['id'];

            if ($name !== '' && isset($relMap[$rid])) {
                $this->sheetPaths[$name] = $relMap[$rid];
            }
        }
    }

    private function cellValue(\SimpleXMLElement $cell): mixed
    {
        $type = (string) ($cell['t'] ?? '');
        $raw = isset($cell->v) ? (string) $cell->v : null;

        if ($raw === null) {
            return null;
        }

        if ($type === 's') {
            return $this->sharedStrings[(int) $raw] ?? null;
        }

        if ($type === 'b') {
            return $raw === '1';
        }

        if (is_numeric($raw)) {
            return str_contains($raw, '.') ? (float) $raw : (int) $raw;
        }

        return trim($raw);
    }

    private function columnNameToIndex(string $column): int
    {
        $column = strtoupper($column);
        $index = 0;

        for ($i = 0; $i < strlen($column); $i++) {
            $index = ($index * 26) + (ord($column[$i]) - 64);
        }

        return max(0, $index - 1);
    }
}
