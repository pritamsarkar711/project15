<?php

namespace App\Services;

/**
 * Minimal RFC 6238 TOTP implementation (SHA1, 6 digits, 30s period).
 * No external package required. QR codes are rendered via a public QR
 * image endpoint built from an otpauth:// URI (Google Authenticator compatible).
 */
class TotpService
{
    public const PERIOD = 30;
    public const DIGITS = 6;
    public const ISSUER = 'Huvanti';

    /** Generate a cryptographically random base32 secret (default 32 chars, no padding). */
    public static function generateSecret(int $length = 32): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= $alphabet[random_int(0, 31)];
        }
        return $secret;
    }

    /** Verify a 6-digit code allowing +/- $window time steps of drift. */
    public static function verify(string $secret, ?string $code, int $window = 1): bool
    {
        $code = preg_replace('/\s+/', '', (string) $code);
        if (!preg_match('/^\d{'.self::DIGITS.'}$/', (string) $code)) {
            return false;
        }
        $counter = intdiv(time(), self::PERIOD);
        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals(self::codeAt($secret, $counter + $i), $code)) {
                return true;
            }
        }
        return false;
    }

    /** TOTP code for a given counter (HOTP-style). */
    public static function codeAt(string $secret, int $counter): string
    {
        $binCounter = str_pad(pack('N', $counter), 8, "\0", STR_PAD_LEFT);
        $hash = hash_hmac('sha1', $binCounter, self::base32Decode($secret), true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $value = unpack('N', substr($hash, $offset, 4))[1] & 0x7FFFFFFF;
        return str_pad((string) ($value % (10 ** self::DIGITS)), self::DIGITS, '0', STR_PAD_LEFT);
    }

    /** Decode RFC 4648 base32 (no padding) to raw binary. */
    public static function base32Decode(string $b32): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $b32 = strtoupper(preg_replace('/[^A-Za-z2-7]/', '', $b32));
        $bits = '';
        foreach (str_split($b32) as $char) {
            $pos = strpos($alphabet, $char);
            if ($pos === false) continue;
            $bits .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }
        $out = '';
        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) === 8) $out .= chr(bindec($byte));
        }
        return $out;
    }

    /** otpauth:// URI understood by Google Authenticator / Authy etc. */
    public static function uri(string $email, string $secret): string
    {
        $label = rawurlencode(self::ISSUER.':'.$email);
        $query = http_build_query([
            'secret' => $secret,
            'issuer' => self::ISSUER,
            'algorithm' => 'SHA1',
            'digits' => self::DIGITS,
            'period' => self::PERIOD,
        ]);
        return 'otpauth://totp/'.$label.'?'.$query;
    }

    /** QR image URL encoding the otpauth URI. */
    public static function getQrUrl(string $email, string $secret): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data='.urlencode(self::uri($email, $secret));
    }
}
