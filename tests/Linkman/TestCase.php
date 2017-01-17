<?php

namespace Linkman;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    public function getUniqueTmpDirectory()
    {
        $attempts = 5;
        $root = sys_get_temp_dir();
        do {
            $unique = $root . DIRECTORY_SEPARATOR . uniqid('composer-test-' . rand(1000, 9000));
            if (!file_exists($unique) && mkdir($unique, 0777)) {
                return realpath($unique);
            }
        } while (--$attempts);

        throw new \RuntimeException('Failed to create a unique temporary directory.');
    }
}