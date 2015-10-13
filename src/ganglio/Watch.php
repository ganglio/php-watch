<?php

namespace ganglio\Watch;

class Watch
{
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
     * Costructor
     * @param string  $path      The path to watch
     * @param boolean $recursive Recurse subdirectory?
     */
    public function __construct($path, $recursive = true)
    {
        $this->path = $path;
        $this->recursive = $recursive;
        $this->fsObjects = $this->_gather();
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
     * @param string $recursive
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
                $objects[] = new FSObject($obj->getPathName());
            }
        }

        return $objects;
    }
}
