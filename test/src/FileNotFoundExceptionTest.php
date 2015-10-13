<?php

namespace WatchTests;

use \ganglio\Watch\Exceptions\FileNotFoundException;

class FileNotFoundExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testException()
    {
        $this->setExpectedException('\ganglio\Watch\Exceptions\FileNotFoundException');
        throw new FileNotFoundException("test");
    }
}