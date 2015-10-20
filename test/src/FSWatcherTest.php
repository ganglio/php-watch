<?php

namespace WatchTests;

use ganglio\Watch\FSWatcher;
use ganglio\Watch\Observer;
use ganglio\Watch\Exceptions\FileNotFoundException;

class DummyObserver implements Observer
{
    public function update($args = null)
    {
        return $args;
    }
}

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

    public function testAttachDetach() {
        $myw = new FSWatcher("./test/fixtures");
        $myo = new DummyObserver();

        $myw->attach($myo);

        $this->setExpectedException("\InvalidArgumentException", FSWatcher::ERR_OBSERVER_ALREADY_REGISTERED);

        $myw->attach($myo);

        $this->assertTrue(
            $myw->has($myo)
        );

        $myw->detach($myo);

        $this->assertFalse(
            $myw->has($myo)
        );

        $this->setExpectedException("\InvalidArgumentException", FSWatcher::ERR_UNKNOWN_OBSERVER);

        $myw->detach($myo);
    }
}
