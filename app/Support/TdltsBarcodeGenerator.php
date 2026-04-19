<?php

namespace App\Support;

class TdltsBarcodeGenerator
{
    /**
     * Generate a short TDLTS barcode in the format: HXXXXXXXX or PXXXXXXXX.
     */
    public static function generate(string $prefix, ?string $id = null): string
    {
        $safePrefix = strtoupper(trim($prefix)) === 'P' ? 'P' : 'H';
        $timestamp = (string) round(microtime(true) * 1000);
        $random = substr(base_convert((string) random_int(0, PHP_INT_MAX), 10, 36), 0, 6);
        $hashInput = ($id ?? '') . $timestamp . $random;

        $hash1 = 0;
        $hash2 = 0;

        $len = strlen($hashInput);
        for ($i = 0; $i < $len; $i++) {
            $char = ord($hashInput[$i]);
            $hash1 = (($hash1 << 5) - $hash1) + $char;
            $hash2 = (($hash2 << 7) - $hash2) + $char;

            // Force signed 32-bit integer behavior to mirror JS bitwise operations.
            $hash1 = self::toSigned32($hash1);
            $hash2 = self::toSigned32($hash2);
        }

        $combinedHash = abs(self::toSigned32($hash1 ^ $hash2));
        $hashStr = strtoupper(str_pad(base_convert((string) $combinedHash, 10, 36), 8, '0', STR_PAD_LEFT));
        $hashStr = substr($hashStr, 0, 8);

        return $safePrefix . $hashStr;
    }

    private static function toSigned32(int $value): int
    {
        $value &= 0xFFFFFFFF;

        if ($value & 0x80000000) {
            return -((~$value & 0xFFFFFFFF) + 1);
        }

        return $value;
    }
}
