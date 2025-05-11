<?php
declare(strict_types=1);

namespace Daigox\NumberManager;

use InvalidArgumentException;
use Random\RandomException;

/**
 * Class NumberManager
 *
 * UTF-8-aware helpers for working with integers & numeral systems (English ⇄ Persian),
 * plus a handful of convenience math utilities.
 *
 * @author  DaigoX.com
 * @license MIT
 *
 * @psalm-immutable
 */
final class NumberManager
{
    /** Utility class: prevent instantiation & cloning. */
    private function __construct() {}
    private function __clone() {}

    // ────────────────────────────── Conversions ────────────────────────────────

    /**
     * Converts Persian digits found in the string to English (Western Arabic) digits.
     */
    public static function convertToEnglishNumerals(string $input): string
    {
        return strtr($input, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        ]);
    }

    /**
     * Converts English digits found in the string to Persian (Eastern Arabic) digits.
     */
    public static function convertToPersianNumerals(string $input): string
    {
        return strtr($input, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
    }

    // ─────────────────────────────── Parity ───────────────────────────────────

    public static function isEven(int $number): bool
    {
        return ($number & 1) === 0;
    }

    public static function isOdd(int $number): bool
    {
        return ($number & 1) === 1;
    }

    public static function isNotEven(int $number): bool
    {
        return !self::isEven($number);
    }

    public static function isNotOdd(int $number): bool
    {
        return !self::isOdd($number);
    }

    // ────────────────────────────── Randomness ────────────────────────────────

    /**
     * Generates a random integer with a digit-length between the provided bounds (inclusive).
     *
     * @throws RandomException
     */
    public static function generateRandomInteger(int $minDigits, ?int $maxDigits = null): int
    {
        if ($minDigits <= 0) {
            throw new InvalidArgumentException('Minimum number of digits must be >= 1.');
        }

        $maxDigits ??= $minDigits;
        if ($maxDigits < $minDigits) {
            throw new InvalidArgumentException('Maximum digits cannot be less than minimum digits.');
        }

        $targetLength = random_int($minDigits, $maxDigits);
        $min          = 10 ** ($targetLength - 1);
        $max          = (10 ** $targetLength) - 1;

        return random_int($min, $max);
    }

    /**
     * Generates a 6-digit numeric one-time password (OTP).
     *
     * @throws RandomException
     */
    public static function generateOTP(): int
    {
        return random_int(100_000, 999_999);
    }

    // ───────────────────────────── Comparators ─────────────────────────────────

    public static function isGreaterThan(int $input, int $number): bool
    {
        return $input > $number;
    }

    public static function isLessThan(int $input, int $number): bool
    {
        return $input < $number;
    }

    public static function isBetween(int $input, int $min, int $max): bool
    {
        return $input >= $min && $input <= $max;
    }

    // ───────────────────────────── Arithmetic ─────────────────────────────────

    public static function add(int $input, int $amount): int
    {
        return $input + $amount;
    }

    public static function subtract(int $input, int $amount): int
    {
        return $input - $amount;
    }

    public static function multiply(int $input, int $multiplier): int
    {
        return $input * $multiplier;
    }

    /** @throws InvalidArgumentException */
    public static function divide(int $input, int $divisor): float
    {
        if ($divisor === 0) {
            throw new InvalidArgumentException('Divisor cannot be zero.');
        }

        return $input / $divisor;
    }

    public static function raiseToPower(int $input, int $exponent): int
    {
        return $input ** $exponent;
    }

    public static function modulo(int $input, int $modulus): int
    {
        return $input % $modulus;
    }

    public static function increment(int &$input): void
    {
        ++$input;
    }

    public static function decrement(int &$input): void
    {
        --$input;
    }

    // ────────────────────────────── Utilities ─────────────────────────────────

    public static function sumOfDigits(int $input): int
    {
        return array_sum(array_map('intval', str_split((string) abs($input))));
    }

    /** @throws InvalidArgumentException */
    public static function factorial(int $input): int
    {
        if ($input < 0) {
            throw new InvalidArgumentException('Input must be non-negative.');
        }

        $result = 1;
        for ($i = 2; $i <= $input; ++$i) {
            $result *= $i;
        }

        return $result;
    }

    /**
     * Formats large numbers into a human-friendly abbreviated form (e.g. 2.5K, 3.1M).
     */
    public static function abbreviate(int $number, int $precision = 1): string
    {
        return match (true) {
            $number >= 1_000_000_000 => number_format($number / 1_000_000_000, $precision) . 'B',
            $number >= 1_000_000     => number_format($number / 1_000_000, $precision) . 'M',
            $number >= 1_000         => number_format($number / 1_000, $precision) . 'K',
            default                  => (string) $number,
        };
    }

    public static function clamp(int $number, int $min, int $max): int
    {
        return max($min, min($number, $max));
    }

    /**
     * Converts byte size to a readable string (B, KB, MB, GB).
     */
    public static function fileSize(int $bytes, int $precision = 1): string
    {
        return match (true) {
            $bytes < 1_024             => $bytes . ' B',
            $bytes < 1_048_576         => round($bytes / 1_024,       $precision) . ' KB',
            $bytes < 1_073_741_824     => round($bytes / 1_048_576,   $precision) . ' MB',
            default                    => round($bytes / 1_073_741_824, $precision) . ' GB',
        };
    }

    // ─────────────────────────── RegExp extraction ────────────────────────────

    public static function findFirstNumber(string $input): ?string
    {
        preg_match('/\d+/', $input, $matches);
        return $matches[0] ?? null;
    }

    public static function findAllNumbers(string $input): array
    {
        preg_match_all('/\d+/', $input, $matches);
        return $matches[0];
    }

    public static function findLastNumber(string $input): ?string
    {
        preg_match_all('/\d+/', $input, $matches);
        return $matches[0] !== [] ? end($matches[0]) : null;
    }

    // ───────────────────────────── Miscellaneous ──────────────────────────────

    public static function absoluteValue(int $input): int
    {
        return abs($input);
    }
}
