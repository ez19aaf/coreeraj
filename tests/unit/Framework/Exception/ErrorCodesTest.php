<?php

namespace Tests\Unit\Framework\Exception;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use Survey54\Reap\Framework\Exception\Error;
use function is_int;
use function strlen;

class ErrorCodesTest extends TestCase
{
    // Check that code, message and statusCode exist for an error code.
    // Check that code and the constant name are the same -- means code is unique.
    // Check that message is unique and has min length of 5.
    // Check that statusCode is a 3 length int.

    public function testErrorCodes(): void
    {
        try {
            $constants  = (new ReflectionClass(Error::class))->getConstants();
            $occurrence = array_count_values(array_column($constants, 'message'));
            unset($constants['CONTACT']);

            foreach ($constants as $key => $const) {
                if (!isset($const['code'], $const['message'], $const['statusCode'])) {
                    echo "\033[31m** Error: \033[0m$key must have a code, message and statusCode.\n";
                    self::assertTrue(false); // to error
                }

                $code       = $const['code'];
                $message    = $const['message'];
                $statusCode = $const['statusCode'];

                $err = "\033[31m** Error: \033[0m$key error code ";

                if ($key !== $code) {
                    echo "$err has an invalid code \033[31m$code\033[0m, they must match.\n";
                    self::assertTrue(false); // to error
                }

                if ($occurrence[$message] > 1) {
                    echo "$err has message that already exist in another error code.\n";
                    self::assertTrue(false); // to error
                }

                if (strlen($message) < 5) {
                    echo "$err has a short message, length must be greater than 5.\n";
                    self::assertTrue(false); // to error
                }

                if (!is_int($statusCode) || strlen($statusCode) !== 3) {
                    echo "$err has an invalid statusCode \033[31m$statusCode\033[0m, it must be a 3 length integer.\n";
                    self::assertTrue(false); // to error
                }
            }

            self::assertTrue(true);
        } catch (ReflectionException $e) {
            self::assertTrue(false); // to error
        }
    }
}
