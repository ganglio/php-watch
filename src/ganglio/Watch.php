<?php

namespace ganglio\Watch;

class Watch
{
    const ERR_NOT_CLOSURE             = 1;
    const ERR_UNIDENTIFIED_EVENT_NAME = 2;
    const ERR_CALLBACK_FEW_PARAMETERS = 4;

    /**
     * Container of the watched FS objects (files and directory)
     * @var Array[FSObject]
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
    private $callbacks = [];

    /**
     * Constructor
     * @param string  $path      The path to watch
     * @param boolean $recursive Recurse subdirectory?
     */
    public function __construct($path, $recursive = true)
    {
        $this->path = $path;
        $this->recursive = $recursive;
        $this->fsObjects = $this->_gather();
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
        $this->fsObjects = $this->_gather();
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
        $this->fsObjects = $this->_gather();
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
     * @return Array[string]
     */
    public function getCallbacks()
    {
        return $this->callbacks->keys();
    }

    /**
     * Binds a callback to a change event
     * @param  string  $event
     * @param  Closure $callback
     * @return string  a unique ic for the callback. Can be used to unbind
     */
    public function on($event, $callback)
    {
        if (!($callback instanceof \Closure)) {
            throw new \InvalidArgumentException("Argument 2 need to be an instance of \Closure", self::ERR_NOT_CLOSURE);
        }

        $numArgs = (new \ReflectionFunction($callback))->getNumberOfParameters();

        if ($numArgs < 1) {
            throw new \InvalidArgumentException("Callback need at least one parameter", self::ERR_CALLBACK_FEW_PARAMETERS);
        }

        $callback_id = spl_object_hash($callback);

        if (!in_array($event, ['create', 'delete', 'update'])) {
            throw new \InvalidArgumentException("Argument 2 need to be either 'create', 'delete' or 'update'", self::ERR_UNIDENTIFIED_EVENT_NAME);
        }

        $this->callbacks[$callback_id] = new Callback($event,$callback);

        return $callback_id;
    }

    public function unbind($cid)
    {
        unset($this->callbacks[$cid]);
    }

    /**
     * Collects all the files in the current path according to the recursion setting
     * @return Array[FSObject]
     */
    private function _gather()
    {
        $objects = [];

        $ii = null;

        if (!$this->recursive) {
            $di = new \DirectoryIterator($this->path);
            $ii = new \CallbackFilterIterator($di, function ($current) {
                return !$current->isDot();
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
     * @param  Array[FSObjects] $objects
     * @return Array[String=>Array[FSObjects]]
     */
    private function _diff($objects) {

        $diff = [
            "+" => [],
            "-" => [],
            "!" => [],
        ];

        $keys = array_merge(array_keys($this->fsObjects,$objects));
        foreach ($keys as $key) {
            if (!array_key_exists($key, $this->fsObjects)) {
                $diff["+"][] = $key;
            } else if (!array_key_exists($key, $objects)) {
                $diff["-"][] = $key;
            } else if ($this->fsObjects[$key]->signature != $objects[$key]->signature) {
                $diff['!'][] = $key;
            }
        }

        return $diff;
    }
}
