<?php

/**
 * Pest Test Stub for IDE Support
 * 
 * This file helps IDEs understand that Pest test closures have access
 * to TestCase methods via $this.
 */

namespace Pest;

use Tests\TestCase;

/**
 * @template TTestCase of TestCase
 * @param callable(TTestCase): mixed $test
 */
function test(string $description, callable $test): void
{
}

