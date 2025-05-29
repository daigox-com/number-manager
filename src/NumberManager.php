<?php
declare(strict_types=1);

namespace Daigox\NumberManager;

class NumberManager
{
    /**
     * Format number with thousands separator
     */
    public static function format($number, int $decimals = 0, string $decimalSeparator = '.', string $thousandsSeparator = ','): string
    {
        return number_format($number, $decimals, $decimalSeparator, $thousandsSeparator);
    }

    /**
     * Format number as currency
     */
    public static function currency($amount, string $currency = 'USD', string $locale = 'en_US'): string
    {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'JPY' => '¥',
            'CNY' => '¥',
            'INR' => '₹',
            'KRW' => '₩',
            'RUB' => '₽',
            'BTC' => '₿',
            'ETH' => 'Ξ',
        ];

        $symbol = $symbols[$currency] ?? $currency . ' ';
        $formatted = static::format($amount, 2);
        
        return match($locale) {
            'de_DE', 'fr_FR' => $formatted . ' ' . $symbol,
            default => $symbol . $formatted,
        };
    }

    /**
     * Format as percentage
     */
    public static function percentage($number, int $decimals = 2, bool $includeSign = true): string
    {
        $formatted = static::format($number, $decimals);
        return $includeSign ? $formatted . '%' : $formatted;
    }

    /**
     * Format bytes to human readable
     */
    public static function bytes($bytes, int $precision = 2, bool $binary = true): string
    {
        $negative = $bytes < 0;
        $bytes = abs($bytes);
        
        if ($binary) {
            $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'];
            $factor = 1024;
        } else {
            $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
            $factor = 1000;
        }

        for ($i = 0; $bytes > $factor && $i < count($units) - 1; $i++) {
            $bytes /= $factor;
        }

        $formatted = round($bytes, $precision) . ' ' . $units[$i];
        return $negative ? '-' . $formatted : $formatted;
    }

    /**
     * Format number as shortened (1K, 1M, etc)
     */
    public static function shorten($number, int $precision = 1): string
    {
        $negative = $number < 0;
        $number = abs($number);
        
        $suffixes = ['', 'K', 'M', 'B', 'T', 'Q'];
        $suffixIndex = 0;
        
        while ($number >= 1000 && $suffixIndex < count($suffixes) - 1) {
            $number /= 1000;
            $suffixIndex++;
        }
        
        $formatted = round($number, $precision) . $suffixes[$suffixIndex];
        return $negative ? '-' . $formatted : $formatted;
    }

    /**
     * Format as ordinal (1st, 2nd, 3rd, etc)
     */
    public static function ordinal(int $number): string
    {
        $suffix = 'th';
        
        if (!in_array($number % 100, [11, 12, 13])) {
            switch ($number % 10) {
                case 1: $suffix = 'st'; break;
                case 2: $suffix = 'nd'; break;
                case 3: $suffix = 'rd'; break;
            }
        }
        
        return $number . $suffix;
    }

    /**
     * Format as Roman numeral
     */
    public static function roman(int $number): string
    {
        if ($number <= 0 || $number >= 4000) {
            return (string)$number;
        }
        
        $map = [
            'M' => 1000,
            'CM' => 900,
            'D' => 500,
            'CD' => 400,
            'C' => 100,
            'XC' => 90,
            'L' => 50,
            'XL' => 40,
            'X' => 10,
            'IX' => 9,
            'V' => 5,
            'IV' => 4,
            'I' => 1
        ];
        
        $result = '';
        foreach ($map as $roman => $value) {
            $count = intval($number / $value);
            if ($count) {
                $result .= str_repeat($roman, $count);
                $number %= $value;
            }
        }
        
        return $result;
    }

    /**
     * Parse Roman numeral to integer
     */
    public static function fromRoman(string $roman): int
    {
        $roman = strtoupper($roman);
        $values = [
            'I' => 1,
            'V' => 5,
            'X' => 10,
            'L' => 50,
            'C' => 100,
            'D' => 500,
            'M' => 1000
        ];
        
        $result = 0;
        $prev = 0;
        
        for ($i = strlen($roman) - 1; $i >= 0; $i--) {
            $value = $values[$roman[$i]] ?? 0;
            if ($value < $prev) {
                $result -= $value;
            } else {
                $result += $value;
                $prev = $value;
            }
        }
        
        return $result;
    }

    /**
     * Convert to words (English)
     */
    public static function toWords(int $number): string
    {
        if ($number === 0) {
            return 'zero';
        }

        $negative = $number < 0;
        $number = abs($number);
        
        $ones = ['', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine',
                'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen',
                'seventeen', 'eighteen', 'nineteen'];
        
        $tens = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];
        
        $scales = ['', 'thousand', 'million', 'billion', 'trillion', 'quadrillion', 'quintillion'];
        
        $words = [];
        $scaleIndex = 0;
        
        while ($number > 0) {
            $group = $number % 1000;
            
            if ($group > 0) {
                $groupWords = '';
                
                $hundreds = intval($group / 100);
                if ($hundreds > 0) {
                    $groupWords .= $ones[$hundreds] . ' hundred';
                }
                
                $remainder = $group % 100;
                if ($remainder >= 20) {
                    $groupWords .= ($groupWords ? ' ' : '') . $tens[intval($remainder / 10)];
                    if ($remainder % 10 > 0) {
                        $groupWords .= '-' . $ones[$remainder % 10];
                    }
                } elseif ($remainder > 0) {
                    $groupWords .= ($groupWords ? ' ' : '') . $ones[$remainder];
                }
                
                if ($scaleIndex > 0) {
                    $groupWords .= ' ' . $scales[$scaleIndex];
                }
                
                array_unshift($words, $groupWords);
            }
            
            $number = intval($number / 1000);
            $scaleIndex++;
        }
        
        $result = implode(' ', $words);
        return $negative ? 'negative ' . $result : $result;
    }

    /**
     * Round to nearest multiple
     */
    public static function roundToMultiple($number, $multiple): float
    {
        return round($number / $multiple) * $multiple;
    }

    /**
     * Round to decimal places
     */
    public static function roundTo($number, int $decimals = 0): float
    {
        return round($number, $decimals);
    }

    /**
     * Ceil to decimal places
     */
    public static function ceilTo($number, int $decimals = 0): float
    {
        $multiplier = pow(10, $decimals);
        return ceil($number * $multiplier) / $multiplier;
    }

    /**
     * Floor to decimal places
     */
    public static function floorTo($number, int $decimals = 0): float
    {
        $multiplier = pow(10, $decimals);
        return floor($number * $multiplier) / $multiplier;
    }

    /**
     * Clamp number between min and max
     */
    public static function clamp($number, $min, $max)
    {
        return max($min, min($max, $number));
    }

    /**
     * Check if number is between range
     */
    public static function between($number, $min, $max, bool $inclusive = true): bool
    {
        return $inclusive 
            ? $number >= $min && $number <= $max
            : $number > $min && $number < $max;
    }

    /**
     * Check if number is even
     */
    public static function isEven($number): bool
    {
        return $number % 2 === 0;
    }

    /**
     * Check if number is odd
     */
    public static function isOdd($number): bool
    {
        return $number % 2 !== 0;
    }

    /**
     * Check if number is prime
     */
    public static function isPrime(int $number): bool
    {
        if ($number <= 1) return false;
        if ($number <= 3) return true;
        if ($number % 2 === 0 || $number % 3 === 0) return false;
        
        for ($i = 5; $i * $i <= $number; $i += 6) {
            if ($number % $i === 0 || $number % ($i + 2) === 0) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get prime factors
     */
    public static function primeFactors(int $number): array
    {
        $factors = [];
        
        while ($number % 2 === 0) {
            $factors[] = 2;
            $number /= 2;
        }
        
        for ($i = 3; $i * $i <= $number; $i += 2) {
            while ($number % $i === 0) {
                $factors[] = $i;
                $number /= $i;
            }
        }
        
        if ($number > 2) {
            $factors[] = $number;
        }
        
        return $factors;
    }

    /**
     * Calculate factorial
     */
    public static function factorial(int $number): float
    {
        if ($number < 0) {
            throw new \InvalidArgumentException('Factorial of negative numbers is not defined');
        }
        
        if ($number === 0 || $number === 1) {
            return 1;
        }
        
        $result = 1;
        for ($i = 2; $i <= $number; $i++) {
            $result *= $i;
        }
        
        return $result;
    }

    /**
     * Calculate fibonacci number
     */
    public static function fibonacci(int $n): int
    {
        if ($n <= 0) return 0;
        if ($n === 1) return 1;
        
        $a = 0;
        $b = 1;
        
        for ($i = 2; $i <= $n; $i++) {
            $temp = $a + $b;
            $a = $b;
            $b = $temp;
        }
        
        return $b;
    }

    /**
     * Greatest Common Divisor
     */
    public static function gcd(int $a, int $b): int
    {
        while ($b !== 0) {
            $temp = $b;
            $b = $a % $b;
            $a = $temp;
        }
        
        return abs($a);
    }

    /**
     * Least Common Multiple
     */
    public static function lcm(int $a, int $b): int
    {
        return abs($a * $b) / static::gcd($a, $b);
    }

    /**
     * Convert between bases
     */
    public static function baseConvert($number, int $fromBase, int $toBase): string
    {
        return base_convert((string)$number, $fromBase, $toBase);
    }

    /**
     * Convert to binary
     */
    public static function toBinary($number): string
    {
        return decbin($number);
    }

    /**
     * Convert to hexadecimal
     */
    public static function toHex($number): string
    {
        return dechex($number);
    }

    /**
     * Convert to octal
     */
    public static function toOctal($number): string
    {
        return decoct($number);
    }

    /**
     * Parse number from string
     */
    public static function parse($value)
    {
        if (is_numeric($value)) {
            return $value + 0; // Auto convert to int or float
        }
        
        // Remove common formatting
        $value = str_replace([',', ' ', '$', '€', '£', '¥', '%'], '', $value);
        
        // Handle parentheses for negative numbers
        if (preg_match('/^\((.+)\)$/', $value, $matches)) {
            $value = '-' . $matches[1];
        }
        
        return is_numeric($value) ? $value + 0 : null;
    }

    /**
     * Generate random integer
     */
    public static function randomInt(int $min, int $max): int
    {
        return mt_rand($min, $max);
    }

    /**
     * Generate random float
     */
    public static function randomFloat(float $min, float $max, int $decimals = 2): float
    {
        $random = $min + mt_rand() / mt_getrandmax() * ($max - $min);
        return round($random, $decimals);
    }

    /**
     * Generate random bytes
     */
    public static function randomBytes(int $length): string
    {
        return random_bytes($length);
    }

    /**
     * Check if number is integer
     */
    public static function isInteger($value): bool
    {
        return is_int($value) || (is_numeric($value) && intval($value) == $value);
    }

    /**
     * Check if number is float
     */
    public static function isFloat($value): bool
    {
        return is_float($value) || (is_numeric($value) && !static::isInteger($value));
    }

    /**
     * Check if number is positive
     */
    public static function isPositive($number): bool
    {
        return $number > 0;
    }

    /**
     * Check if number is negative
     */
    public static function isNegative($number): bool
    {
        return $number < 0;
    }

    /**
     * Check if number is zero
     */
    public static function isZero($number, float $epsilon = 0.000001): bool
    {
        return abs($number) < $epsilon;
    }

    /**
     * Calculate percentage
     */
    public static function percentageOf($value, $total): float
    {
        if ($total == 0) {
            return 0;
        }
        
        return ($value / $total) * 100;
    }

    /**
     * Calculate value from percentage
     */
    public static function fromPercentage($percentage, $total): float
    {
        return ($percentage / 100) * $total;
    }

    /**
     * Calculate percentage change
     */
    public static function percentageChange($oldValue, $newValue): float
    {
        if ($oldValue == 0) {
            return $newValue == 0 ? 0 : INF;
        }
        
        return (($newValue - $oldValue) / abs($oldValue)) * 100;
    }

    /**
     * Calculate average
     */
    public static function average(array $numbers): float
    {
        $count = count($numbers);
        return $count > 0 ? array_sum($numbers) / $count : 0;
    }

    /**
     * Calculate median
     */
    public static function median(array $numbers): float
    {
        if (empty($numbers)) {
            return 0;
        }
        
        sort($numbers);
        $count = count($numbers);
        $middle = floor(($count - 1) / 2);
        
        if ($count % 2) {
            return $numbers[$middle];
        } else {
            return ($numbers[$middle] + $numbers[$middle + 1]) / 2;
        }
    }

    /**
     * Calculate mode
     */
    public static function mode(array $numbers): array
    {
        $counts = array_count_values($numbers);
        $maxCount = max($counts);
        
        return array_keys(array_filter($counts, fn($count) => $count === $maxCount));
    }

    /**
     * Calculate standard deviation
     */
    public static function standardDeviation(array $numbers, bool $population = false): float
    {
        $count = count($numbers);
        if ($count <= 1) {
            return 0;
        }
        
        $mean = static::average($numbers);
        $variance = 0;
        
        foreach ($numbers as $number) {
            $variance += pow($number - $mean, 2);
        }
        
        $variance /= $population ? $count : ($count - 1);
        
        return sqrt($variance);
    }

    /**
     * Calculate variance
     */
    public static function variance(array $numbers, bool $population = false): float
    {
        return pow(static::standardDeviation($numbers, $population), 2);
    }

    /**
     * Sum of array
     */
    public static function sum(array $numbers): float
    {
        return array_sum($numbers);
    }

    /**
     * Product of array
     */
    public static function product(array $numbers): float
    {
        return array_reduce($numbers, fn($carry, $item) => $carry * $item, 1);
    }

    /**
     * Get minimum
     */
    public static function min(array $numbers)
    {
        return min($numbers);
    }

    /**
     * Get maximum
     */
    public static function max(array $numbers)
    {
        return max($numbers);
    }

    /**
     * Calculate range
     */
    public static function range(array $numbers): float
    {
        return static::max($numbers) - static::min($numbers);
    }

    /**
     * Generate arithmetic sequence
     */
    public static function arithmeticSequence($start, $difference, int $count): array
    {
        $sequence = [];
        for ($i = 0; $i < $count; $i++) {
            $sequence[] = $start + ($i * $difference);
        }
        return $sequence;
    }

    /**
     * Generate geometric sequence
     */
    public static function geometricSequence($start, $ratio, int $count): array
    {
        $sequence = [];
        for ($i = 0; $i < $count; $i++) {
            $sequence[] = $start * pow($ratio, $i);
        }
        return $sequence;
    }

    /**
     * Calculate power
     */
    public static function power($base, $exponent): float
    {
        return pow($base, $exponent);
    }

    /**
     * Calculate square root
     */
    public static function sqrt($number): float
    {
        return sqrt($number);
    }

    /**
     * Calculate cube root
     */
    public static function cbrt($number): float
    {
        return pow($number, 1/3);
    }

    /**
     * Calculate nth root
     */
    public static function nthRoot($number, $n): float
    {
        return pow($number, 1/$n);
    }

    /**
     * Calculate logarithm
     */
    public static function log($number, $base = M_E): float
    {
        return log($number, $base);
    }

    /**
     * Calculate natural logarithm
     */
    public static function ln($number): float
    {
        return log($number);
    }

    /**
     * Calculate base 10 logarithm
     */
    public static function log10($number): float
    {
        return log10($number);
    }

    /**
     * Convert degrees to radians
     */
    public static function toRadians($degrees): float
    {
        return deg2rad($degrees);
    }

    /**
     * Convert radians to degrees
     */
    public static function toDegrees($radians): float
    {
        return rad2deg($radians);
    }

    /**
     * Calculate sine
     */
    public static function sin($angle): float
    {
        return sin($angle);
    }

    /**
     * Calculate cosine
     */
    public static function cos($angle): float
    {
        return cos($angle);
    }

    /**
     * Calculate tangent
     */
    public static function tan($angle): float
    {
        return tan($angle);
    }

    /**
     * Format number with suffix (K, M, B, etc) with more precision
     */
    public static function humanize($number, int $precision = 2): string
    {
        $negative = $number < 0;
        $number = abs($number);
        
        $units = ['', 'K', 'M', 'B', 'T', 'Q'];
        $power = $number > 0 ? floor(log($number, 1000)) : 0;
        
        if ($power >= count($units)) {
            $power = count($units) - 1;
        }
        
        $number = $number / pow(1000, $power);
        $formatted = round($number, $precision) . $units[$power];
        
        return $negative ? '-' . $formatted : $formatted;
    }

    /**
     * Calculate compound interest
     */
    public static function compoundInterest($principal, $rate, $time, $frequency = 1): float
    {
        return $principal * pow(1 + ($rate / $frequency), $frequency * $time);
    }

    /**
     * Calculate simple interest
     */
    public static function simpleInterest($principal, $rate, $time): float
    {
        return $principal * (1 + ($rate * $time));
    }

    /**
     * Convert number to different numeral systems
     */
    public static function toNumeralSystem($number, string $system = 'arabic'): string
    {
        $systems = [
            'arabic' => ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            'bengali' => ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'],
            'devanagari' => ['०', '१', '२', '३', '४', '५', '६', '७', '८', '९'],
            'persian' => ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'],
            'arabic-indic' => ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'],
            'chinese' => ['零', '一', '二', '三', '四', '五', '六', '七', '八', '九'],
        ];
        
        if (!isset($systems[$system])) {
            return (string)$number;
        }
        
        $result = '';
        $digits = str_split((string)abs($number));
        
        foreach ($digits as $digit) {
            if (is_numeric($digit)) {
                $result .= $systems[$system][$digit];
            } else {
                $result .= $digit;
            }
        }
        
        return $number < 0 ? '-' . $result : $result;
    }

    /**
     * Calculate permutations
     */
    public static function permutations(int $n, int $r): float
    {
        if ($r > $n) return 0;
        return static::factorial($n) / static::factorial($n - $r);
    }

    /**
     * Calculate combinations
     */
    public static function combinations(int $n, int $r): float
    {
        if ($r > $n) return 0;
        return static::factorial($n) / (static::factorial($r) * static::factorial($n - $r));
    }

    /**
     * Check if number is perfect square
     */
    public static function isPerfectSquare($number): bool
    {
        if ($number < 0) return false;
        $sqrt = sqrt($number);
        return $sqrt == floor($sqrt);
    }

    /**
     * Check if number is perfect cube
     */
    public static function isPerfectCube($number): bool
    {
        $cbrt = round(pow(abs($number), 1/3));
        return pow($cbrt, 3) == $number;
    }

    /**
     * Get divisors of a number
     */
    public static function divisors(int $number): array
    {
        $divisors = [];
        $number = abs($number);
        
        for ($i = 1; $i <= sqrt($number); $i++) {
            if ($number % $i === 0) {
                $divisors[] = $i;
                if ($i !== $number / $i) {
                    $divisors[] = $number / $i;
                }
            }
        }
        
        sort($divisors);
        return $divisors;
    }

    /**
     * Check if number is perfect (sum of divisors equals the number)
     */
    public static function isPerfect(int $number): bool
    {
        if ($number <= 1) return false;
        $divisors = static::divisors($number);
        array_pop($divisors); // Remove the number itself
        return array_sum($divisors) === $number;
    }

    /**
     * Convert to scientific notation
     */
    public static function toScientific($number, int $precision = 2): string
    {
        return sprintf("%.{$precision}e", $number);
    }

    /**
     * Parse from scientific notation
     */
    public static function fromScientific(string $scientific): float
    {
        return (float)$scientific;
    }

    /**
     * Calculate distance between two points
     */
    public static function distance($x1, $y1, $x2, $y2): float
    {
        return sqrt(pow($x2 - $x1, 2) + pow($y2 - $y1, 2));
    }

    /**
     * Calculate 3D distance
     */
    public static function distance3D($x1, $y1, $z1, $x2, $y2, $z2): float
    {
        return sqrt(pow($x2 - $x1, 2) + pow($y2 - $y1, 2) + pow($z2 - $z1, 2));
    }

    /**
     * Map number from one range to another
     */
    public static function map($value, $fromMin, $fromMax, $toMin, $toMax)
    {
        return ($value - $fromMin) * ($toMax - $toMin) / ($fromMax - $fromMin) + $toMin;
    }

    /**
     * Normalize number to 0-1 range
     */
    public static function normalize($value, $min, $max): float
    {
        return static::map($value, $min, $max, 0, 1);
    }

    /**
     * Linear interpolation
     */
    public static function lerp($start, $end, $amount): float
    {
        return $start + ($end - $start) * $amount;
    }

    /**
     * Get sign of number
     */
    public static function sign($number): int
    {
        return $number <=> 0;
    }

    /**
     * Calculate modulo (always positive)
     */
    public static function mod($a, $b)
    {
        return (($a % $b) + $b) % $b;
    }

    /**
     * Wrap number within range
     */
    public static function wrap($value, $min, $max)
    {
        $range = $max - $min;
        return $min + static::mod($value - $min, $range);
    }

    /**
     * Check if approximately equal
     */
    public static function approximately($a, $b, float $epsilon = 0.000001): bool
    {
        return abs($a - $b) < $epsilon;
    }

    /**
     * Format duration in seconds to human readable
     */
    public static function duration(int $seconds): string
    {
        $units = [
            'year' => 31536000,
            'month' => 2592000,
            'week' => 604800,
            'day' => 86400,
            'hour' => 3600,
            'minute' => 60,
            'second' => 1
        ];
        
        $parts = [];
        
        foreach ($units as $name => $divisor) {
            $value = floor($seconds / $divisor);
            if ($value > 0) {
                $parts[] = $value . ' ' . $name . ($value > 1 ? 's' : '');
                $seconds %= $divisor;
            }
        }
        
        return implode(', ', $parts) ?: '0 seconds';
    }
}