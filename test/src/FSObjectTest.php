<?php

namespace WatchTests;

use ganglio\Watch\Watch;
use ganglio\Watch\FSObject;

class FSObjectTest extends \PHPUnit_Framework_TestCase
{
    public function testFile()
    {
        $fso = new FSObject("./test/fixtures/file1");

        $this->assertEquals(
            "./test/fixtures/file1",
            $fso->name
        );

        $this->assertEquals(
            md5(""),
            $fso->signature
        );
    }

    public function testFolder()
    {
        $fso = new FSObject("./test/fixtures/subfolder");

        $this->assertEquals(
            "./test/fixtures/subfolder",
            $fso->name
        );

        $this->assertEquals(
            md5("./test/fixtures/subfolder"),
            $fso->signature
        );
    }

    public function testNotExistingFileException()
    {
        $this->setExpectedException('\ganglio\Watch\Exceptions\FileNotFoundException');
        $fso = new FSObject("./test/fixtures/file44");
    }
}
