<?php

namespace ganglio\Watch;

class Watch
{
    const ERR_NOT_CLOSURE             = 1;
    const ERR_UNIDENTIFIED_EVENT_NAME = 2;
    const ERR_CALLBACK_FEW_PARAMETERS = 4;

    /**
     * Container of the watched FS objects (files and directory)
     * @var array<string, array<FSObject>>
     */
    private $fsObjects;

    /**
     * The watched path
     * @var string
     */
    private $path;

    /**
     * Recurse subdirectory
     * @var boolean
     */
    private $recursive;

    /**
     * The list of all the registered callbacks
     * @var CallbackCollection
     */
    private $callbacks = null;

    /**
     * Constructor
     * @param string  $path      The path to watch
     * @param boolean $recursive Recurse subdirectory?
     */
    public function __construct($path, $recursive = true)
    {
        $this->path = $path;
        $this->recursive = $recursive;
        $this->fsObjects = $this->gather();
        $this->callbacks = new CallbackCollection();
    }

    /**
     * Get the watched path
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set the watched path
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
        $this->fsObjects = $this->gather();
    }

    /**
     * Gets the recursive
     * @return boolean
     */
    public function getRecursive()
    {
        return $this->recursive;
    }

    /**
     * Set the recursive
     * @param boolean $recursive
     */
    public function setRecursive($recursive)
    {
        $this->recursive = $recursive;
        $this->fsObjects = $this->gather();
    }

    /**
     * Returns the number of watched objects
     * @return integer
     */
    public function getNumberOfWatchedObjects()
    {
        return count($this->fsObjects);
    }

    /**
     * returns the list of create callbacks ids
     * @return array<string>
     */
    public function getCallbacks()
    {
        return $this->callbacks->keys();
    }

    /**
     * Binds a callback to a change event
     * @param  string  $event
     * @param  Closure $callback
     * @return string  a unique id for the callback. Can be used to unbind
     */
    public function on($event, $callback)
    {
        if (!($callback instanceof \Closure)) {
            throw new \InvalidArgumentException(
                "Argument 2 need to be an instance of \Closure",
                self::ERR_NOT_CLOSURE
            );
        }

        $numArgs = (new \ReflectionFunction($callback))->getNumberOfParameters();

        if ($numArgs < 1) {
            throw new \InvalidArgumentException(
                "Callback need at least one parameter",
                self::ERR_CALLBACK_FEW_PARAMETERS
            );
        }

        $callback_id = spl_object_hash($callback);

        if (!in_array($event, ['create', 'delete', 'update'])) {
            throw new \InvalidArgumentException(
                "Argument 2 need to be either 'create', 'delete' or 'update'",
                self::ERR_UNIDENTIFIED_EVENT_NAME
            );
        }

        $this->callbacks[$callback_id] = new Callback($event, $callback);

        return $callback_id;
    }

    /**
     * Unbind callback
     * @param  string $cid
     */
    public function unbind($cid)
    {
        unset($this->callbacks[$cid]);
    }

    /**
     * runs the watch once
     */
    public function once()
    {
        $diff = $this->diff($this->gather());

        $callbacks = $this->callbacks; // ugly php<5.6 hack

        foreach ($diff as $type => $changes) {
            $callbacks($type, $changes);
        }
    }

    /**
     * Collects all the files in the current path according to the recursion setting
     * @return array<string,FSObject>
     */
    private function gather()
    {
        $objects = [];

        if (!$this->recursive) {
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
