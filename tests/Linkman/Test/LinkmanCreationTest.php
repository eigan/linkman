<?php

namespace Linkman\Test;

use Linkman\Linkman;
use Linkman\TestCase;

use Linkman\Exception\NotInitializedException;

/**
 * Tests everything create of the Linkman object
 */
class LinkmanCreationTest extends TestCase
{
    /**
     *
     */
    public function testNotInitialized()
    {
        $this->expectException(NotInitializedException::class);
        $uniqueDir = self::getUniqueTmpDirectory();

        new Linkman($uniqueDir);
    }
}