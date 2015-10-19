<?php

namespace ganglio\Watch;

class Callback
{
    /**
     * a qualifier associated with the callable
     * @var string
     */
    public $type;

    /**
     * the actual callable
     * @var callable
     */
    private $callback;

    /**
     * the constructor
     * @param string   $type
     * @param callable $callback
     */
    public function __construct($type, callable $callback)
    {
        $this->type = $type;
        $this->callback = $callback;
    }

    /**
     * __invoke magic method to expose the class as a callable
     * @param  mixed $args
     * @return mixed
     */
    public function __invoke($args=null)
    {
        $args = func_get_args();
        return call_user_func_array($this->callback, $args);
    }
}
