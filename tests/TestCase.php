<?php
declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Base test case with common testing utilities.
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * Assert that an exception is thrown with a specific message.
     */
    protected function assertExceptionThrown(string $exceptionClass, string $message, callable $callback): void
    {
        $this->expectException($exceptionClass);
        $this->expectExceptionMessage($message);
        $callback();
    }

    /**
     * Generate a temporary file path for testing.
     */
    protected function getTempFilePath(string $suffix = '.tmp'): string
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('test_') . $suffix;
    }

    /**
     * Create a temporary directory for testing.
     */
    protected function getTempDir(): string
    {
        $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('test_dir_');
        mkdir($dir, 0777, true);
        return $dir;
    }
}
