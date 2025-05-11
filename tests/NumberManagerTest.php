<?php

declare(strict_types=1);

namespace Daigox\NumberManager\Tests;

use Daigox\NumberManager\NumberManager;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Random\RandomException;

final class NumberManagerTest extends TestCase
{
    // ────────────────────────────── Conversions ────────────────────────────────

    public function testConvertToEnglishNumerals(): void
    {
        $this->assertEquals('1234567890', NumberManager::convertToEnglishNumerals('۱۲۳۴۵۶۷۸۹۰'));
        $this->assertEquals('Hello 123 World', NumberManager::convertToEnglishNumerals('Hello ۱۲۳ World'));
        $this->assertEquals('No numbers here', NumberManager::convertToEnglishNumerals('No numbers here'));
    }

    public function testConvertToPersianNumerals(): void
    {
        $this->assertEquals('۱۲۳۴۵۶۷۸۹۰', NumberManager::convertToPersianNumerals('1234567890'));
        $this->assertEquals('Hello ۱۲۳ World', NumberManager::convertToPersianNumerals('Hello 123 World'));
        $this->assertEquals('No numbers here', NumberManager::convertToPersianNumerals('No numbers here'));
    }

    // ─────────────────────────────── Parity ───────────────────────────────────

    public function testIsEven(): void
    {
        $this->assertTrue(NumberManager::isEven(2));
        $this->assertTrue(NumberManager::isEven(0));
        $this->assertTrue(NumberManager::isEven(-2));
        $this->assertFalse(NumberManager::isEven(1));
        $this->assertFalse(NumberManager::isEven(-1));
    }

    public function testIsOdd(): void
    {
        $this->assertTrue(NumberManager::isOdd(1));
        $this->assertTrue(NumberManager::isOdd(-1));
        $this->assertFalse(NumberManager::isOdd(2));
        $this->assertFalse(NumberManager::isOdd(0));
        $this->assertFalse(NumberManager::isOdd(-2));
    }

    public function testIsNotEven(): void
    {
        $this->assertFalse(NumberManager::isNotEven(2));
        $this->assertFalse(NumberManager::isNotEven(0));
        $this->assertTrue(NumberManager::isNotEven(1));
    }

    public function testIsNotOdd(): void
    {
        $this->assertFalse(NumberManager::isNotOdd(1));
        $this->assertTrue(NumberManager::isNotOdd(2));
        $this->assertTrue(NumberManager::isNotOdd(0));
    }

    // ────────────────────────────── Randomness ────────────────────────────────

    public function testGenerateRandomInteger(): void
    {
        $number = NumberManager::generateRandomInteger(3);
        $this->assertIsInt($number);
        $this->assertGreaterThanOrEqual(100, $number);
        $this->assertLessThanOrEqual(999, $number);

        $number = NumberManager::generateRandomInteger(2, 4);
        $this->assertIsInt($number);
        $this->assertGreaterThanOrEqual(10, $number);
        $this->assertLessThanOrEqual(9999, $number);
    }

    public function testGenerateRandomIntegerThrowsExceptionForInvalidMinDigits(): void
    {
        $this->expectException(InvalidArgumentException::class);
        NumberManager::generateRandomInteger(0);
    }

    public function testGenerateRandomIntegerThrowsExceptionForInvalidMaxDigits(): void
    {
        $this->expectException(InvalidArgumentException::class);
        NumberManager::generateRandomInteger(3, 2);
    }

    public function testGenerateOTP(): void
    {
        $otp = NumberManager::generateOTP();
        $this->assertIsInt($otp);
        $this->assertGreaterThanOrEqual(100000, $otp);
        $this->assertLessThanOrEqual(999999, $otp);
    }

    // ───────────────────────────── Comparators ─────────────────────────────────

    public function testIsGreaterThan(): void
    {
        $this->assertTrue(NumberManager::isGreaterThan(5, 3));
        $this->assertFalse(NumberManager::isGreaterThan(3, 5));
        $this->assertFalse(NumberManager::isGreaterThan(5, 5));
    }

    public function testIsLessThan(): void
    {
        $this->assertTrue(NumberManager::isLessThan(3, 5));
        $this->assertFalse(NumberManager::isLessThan(5, 3));
        $this->assertFalse(NumberManager::isLessThan(5, 5));
    }

    public function testIsBetween(): void
    {
        $this->assertTrue(NumberManager::isBetween(5, 1, 10));
        $this->assertTrue(NumberManager::isBetween(1, 1, 10));
        $this->assertTrue(NumberManager::isBetween(10, 1, 10));
        $this->assertFalse(NumberManager::isBetween(0, 1, 10));
        $this->assertFalse(NumberManager::isBetween(11, 1, 10));
    }

    // ───────────────────────────── Arithmetic ─────────────────────────────────

    public function testAdd(): void
    {
        $this->assertEquals(8, NumberManager::add(5, 3));
        $this->assertEquals(2, NumberManager::add(-1, 3));
        $this->assertEquals(-4, NumberManager::add(-1, -3));
    }

    public function testSubtract(): void
    {
        $this->assertEquals(2, NumberManager::subtract(5, 3));
        $this->assertEquals(-4, NumberManager::subtract(-1, 3));
        $this->assertEquals(2, NumberManager::subtract(-1, -3));
    }

    public function testMultiply(): void
    {
        $this->assertEquals(15, NumberManager::multiply(5, 3));
        $this->assertEquals(-15, NumberManager::multiply(-5, 3));
        $this->assertEquals(15, NumberManager::multiply(-5, -3));
    }

    public function testDivide(): void
    {
        $this->assertEquals(2.5, NumberManager::divide(5, 2));
        $this->assertEquals(-2.5, NumberManager::divide(-5, 2));
        $this->assertEquals(2.5, NumberManager::divide(-5, -2));
    }

    public function testDivideThrowsExceptionForZeroDivisor(): void
    {
        $this->expectException(InvalidArgumentException::class);
        NumberManager::divide(5, 0);
    }

    public function testRaiseToPower(): void
    {
        $this->assertEquals(8, NumberManager::raiseToPower(2, 3));
        $this->assertEquals(1, NumberManager::raiseToPower(2, 0));
        $this->assertEquals(0.125, NumberManager::raiseToPower(2, -3));
    }

    public function testModulo(): void
    {
        $this->assertEquals(1, NumberManager::modulo(5, 2));
        $this->assertEquals(0, NumberManager::modulo(6, 2));
        $this->assertEquals(1, NumberManager::modulo(-5, 2));
    }

    public function testIncrement(): void
    {
        $number = 5;
        NumberManager::increment($number);
        $this->assertEquals(6, $number);
    }

    public function testDecrement(): void
    {
        $number = 5;
        NumberManager::decrement($number);
        $this->assertEquals(4, $number);
    }

    // ────────────────────────────── Utilities ─────────────────────────────────

    public function testSumOfDigits(): void
    {
        $this->assertEquals(15, NumberManager::sumOfDigits(12345));
        $this->assertEquals(15, NumberManager::sumOfDigits(-12345));
        $this->assertEquals(0, NumberManager::sumOfDigits(0));
    }

    public function testFactorial(): void
    {
        $this->assertEquals(1, NumberManager::factorial(0));
        $this->assertEquals(1, NumberManager::factorial(1));
        $this->assertEquals(120, NumberManager::factorial(5));
    }

    public function testFactorialThrowsExceptionForNegativeInput(): void
    {
        $this->expectException(InvalidArgumentException::class);
        NumberManager::factorial(-1);
    }

    public function testAbbreviate(): void
    {
        $this->assertEquals('1.5K', NumberManager::abbreviate(1500));
        $this->assertEquals('2.5M', NumberManager::abbreviate(2500000));
        $this->assertEquals('3.1B', NumberManager::abbreviate(3100000000));
        $this->assertEquals('999', NumberManager::abbreviate(999));
    }

    public function testClamp(): void
    {
        $this->assertEquals(5, NumberManager::clamp(5, 1, 10));
        $this->assertEquals(1, NumberManager::clamp(0, 1, 10));
        $this->assertEquals(10, NumberManager::clamp(15, 1, 10));
    }

    public function testFileSize(): void
    {
        $this->assertEquals('500 B', NumberManager::fileSize(500));
        $this->assertEquals('1.5 KB', NumberManager::fileSize(1500));
        $this->assertEquals('1.5 MB', NumberManager::fileSize(1500000));
        $this->assertEquals('1.5 GB', NumberManager::fileSize(1500000000));
    }

    // ─────────────────────────── RegExp extraction ────────────────────────────

    public function testFindFirstNumber(): void
    {
        $this->assertEquals('123', NumberManager::findFirstNumber('abc123def456'));
        $this->assertEquals('456', NumberManager::findFirstNumber('abc456def'));
        $this->assertNull(NumberManager::findFirstNumber('no numbers here'));
    }

    public function testFindAllNumbers(): void
    {
        $this->assertEquals(['123', '456'], NumberManager::findAllNumbers('abc123def456'));
        $this->assertEquals(['456'], NumberManager::findAllNumbers('abc456def'));
        $this->assertEquals([], NumberManager::findAllNumbers('no numbers here'));
    }

    public function testFindLastNumber(): void
    {
        $this->assertEquals('456', NumberManager::findLastNumber('abc123def456'));
        $this->assertEquals('456', NumberManager::findLastNumber('abc456def'));
        $this->assertNull(NumberManager::findLastNumber('no numbers here'));
    }

    // ───────────────────────────── Miscellaneous ──────────────────────────────

    public function testAbsoluteValue(): void
    {
        $this->assertEquals(5, NumberManager::absoluteValue(5));
        $this->assertEquals(5, NumberManager::absoluteValue(-5));
        $this->assertEquals(0, NumberManager::absoluteValue(0));
    }
} 