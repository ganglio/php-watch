<?php

namespace WatchTests;

use ganglio\Watch\FSWatcher;
use ganglio\Watch\Exceptions\FileNotFoundException;

class FSWatcherTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorInvalidPath()
    {
        $this->setExpectedException('ganglio\Watch\Exceptions\FileNotFoundException');
        new FSWatcher("./testtesttest");
    }

    public function testGetSetPathAndRecurse()
    {
        $myw = new FSWatcher("./test/fixtures", false);

        $this->assertEquals(
            "./test/fixtures",
            $myw->getPath()
        );

        $this->assertFalse(
            $myw->getRecurse()
        );
    }

    public function testIsChanged()
    {
        $myw = new FSWatcher("./test/fixtures");

        file_put_contents("./test/fixtures/subfolder/file5", "test");

        $isChanged = $myw->isChanged();

        file_put_contents("./test/fixtures/subfolder/file5", "");

        $this->assertTrue($isChanged);
    }
}
