<?php

namespace ganglio\Watch;

use \ganglio\Watch\Observable;
use \ganglio\Watch\Observer;
use \ganglio\Watch\Pollable;
use \ganglio\Watch\Exceptions\FileNotFoundException;
use \ganglio\Watch\FSObject;

class FSWatcher implements Observable, Pollable
{
    const ERR_OBSERVER_ALREADY_REGISTERED = 1;
    const ERR_UNKNOWN_OBSERVER            = 2;

    /**
     * The wathed path
     * @var string
     */
    private $path;

    /**
     * Sets if the path is watched recursively or not
     * @var boolean
     */
    private $recurse;

    /**
     * collection of observers
     * @var array<Observers>
     */
    private $observers;

    /**
     * the list of currently watched FSObjects
     * @var array<string, FSObject>
     */
    private $fsObjects;

    /**
     * The constructor
     * @param  string  $path
     * @param  boolean $recurse
     */
    public function __construct($path, $recurse = true)
    {
        if (!file_exists($path)) {
            throw new FileNotFoundException($path);
        }

        $this->setPath($path);
        $this->setRecurse($recurse);
        $this->fsObjects = $this->gather();
    }

    /**
     * setter for attribute path
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * getter for attribute path
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * setter for attribute recurse
     * @param boolean $recurse
     */
    public function setRecurse($recurse)
    {
        $this->recurse = $recurse;
    }

    /**
     * getter for attribute recurse
     * @return boolean
     */
    public function getRecurse()
    {
        return $this->recurse;
    }

    /**
     * Implements the attach method defined by Observable
     * @param  Observer $observer
     * @return void
     */
    public function attach(Observer $observer)
    {
        $observer_hash = spl_object_hash($observer);

        if (isset($this->observers[$observer_hash])) {
            throw new \InvalidArgumentException(
                "The observer is already registered",
                self::ERR_OBSERVER_ALREADY_REGISTERED
            );
        }

        $this->observers[$observer_hash] = $observer;
    }

    /**
     * Implements the detach method defined by Observable
     * @param  Observer $observer
     * @return void
     */
    public function detach(Observer $observer)
    {
        $observer_hash = spl_object_hash($observer);

        if (!isset($this->observers[$observer_hash])) {
            throw new \InvalidArgumentException(
                "Unknown observer",
                self::ERR_UNKNOWN_OBSERVER
            );
        }
    }

    /**
     * Implements the notify method defined by Observable
     * @return void
     */
    public function notify($diff = null)
    {
        foreach ($this->observers as $observer) {
            $observer->update($diff);
        }
    }

    /**
     * Implements the poll method defined by Pollable
     * @return void
     */
    public function poll()
    {
        if (($diff = $this->isChanged()) !== false) {
            $this->notify($diff);
            $this->fsObjects = $this->gather();
        }
    }

    public function isChanged()
    {
        $diff = $this->diff($this->gather());

        $isChanged = count(array_filter($diff, function ($v) {
            return count($v)!=0;
        })) != 0;

        return $isChanged;
    }


        /**
     * Collects all the files in the current path according to the recursion setting
     * @return array<string,FSObject>
     */
    private function gather()
    {
        $objects = [];

        if (!$this->recurse) {
            $di = new \DirectoryIterator($this->path);
            $ii = new \CallbackFilterIterator($di, function ($current) {
                return $current->isFile();
            });
        } else {
            $di = new \RecursiveDirectoryIterator($this->path, \FilesystemIterator::SKIP_DOTS);
            $ii = new \RecursiveIteratorIterator($di, \RecursiveIteratorIterator::SELF_FIRST);
        }

        if (!is_null($ii)) {
            foreach ($ii as $obj) {
                $objects[$obj->getPathName()] = new FSObject($obj->getPathName());
            }
        }

        return $objects;
    }

    /**
     * Calculates the diff between the fsObjects attribute and the objects parameter
     * @param  array<string,FSObject> $objects
     * @return array<string,array<string>>
     */
    private function diff($objects)
    {

        $diff = [
            "create" => [],
            "delete" => [],
            "update" => [],
        ];

        $keys = array_unique(array_merge(array_keys($this->fsObjects), array_keys($objects)));
        foreach ($keys as $key) {
            if (!array_key_exists($key, $this->fsObjects)) {
                $diff["create"][] = $key;
            } elseif (!array_key_exists($key, $objects)) {
                $diff["delete"][] = $key;
            } elseif ($this->fsObjects[$key]->signature != $objects[$key]->signature) {
                $diff['update'][] = $key;
            }
        }

        return $diff;
    }
}
