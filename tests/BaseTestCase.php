<?php

use PHPUnit\Framework\TestCase;

abstract class BaseTestCase extends TestCase
{
    public function getTestFilePath() {
        return __DIR__ . DIRECTORY_SEPARATOR . 'file.txt';
    }
}
