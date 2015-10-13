<?php

namespace WatchTests;

use ganglio\Watch\Watch;

class WatchTest extends \PHPUnit_Framework_TestCase
{
    private $watcher = null;

    protected function setUp()
    {
        $this->watcher = new Watch("./test/fixtures", true);
    }

    protected function tearDown()
    {
        $this->watcher = null;
    }

    public function testGetSetPath()
    {
        $this->watcher->setPath("./test/fixtures/subfolder");
        $this->assertEquals(
            "./test/fixtures/subfolder",
            $this->watcher->getPath()
        );
    }

    public function testGetSetRecursive()
    {
        $this->watcher->setRecursive(false);
        $this->assertFalse(
            $this->watcher->getRecursive()
        );
    }

    public function testNumberOfWatchedObjectsRecursive()
    {
        $this->watcher->setPath("./test/fixtures");
        $this->watcher->setRecursive(true);
        $this->assertEquals(
            9,
            $this->watcher->getNumberOfWatchedObjects()
        );
    }

    public function testNumberOfWatchedObjectsNonRecursive()
    {
        $this->watcher->setPath("./test/fixtures");
        $this->watcher->setRecursive(false);
        $this->assertEquals(
            5,
            $this->watcher->getNumberOfWatchedObjects()
        );
    }
}
