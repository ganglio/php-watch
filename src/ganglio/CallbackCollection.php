<?php

namespace ganglio\Watch;

class CallbackCollection implements \ArrayAccess
{

    const ERR_NUM_ARGUMENTS = 1;

    /**
     * the collection of callbacks
     * @var array[Callback]
     */
    private $callbacks = [];

    /**
     * implementation of the offsetExists interface method
     * @param  string $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->callbacks[$offset]);
    }

    /**
     * implementation of the offsetget interface method
     * @param  string $offset
     * @return Callback
     */
    public function offsetGet($offset)
    {
        return $this->callbacks[$offset];
    }

    /**
     * implementation of the offsetExists interface method
     * @param  string $offset
     * @param  Callback $value
     */
    public function offsetSet($offset, $value)
    {
        if (!empty($offset)) {
            $this->callbacks[$offset] = $value;
        } else {
            $this->callbacks[] = $value;
        }

    }

    /**
     * implementation of the offsetUnset interface method
     * @param  string $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->callbacks[$offset]);
    }

    /**
     * returns all the callbacks keys
     * @return array[string]
     */
    public function keys()
    {
        return array_keys($this->callbacks);
    }

    /**
     * implementation of the __invoke magic method
     * @param  string $type
     * @param  mixed $args
     * @return array[mixed]
     */
    public function __invoke()
    {
        if (func_num_args()<1) {
            throw new \InvalidArgumentException("the __invoke method need at least one argument", self::ERR_NUM_ARGUMENTS);
        }

        $args = func_get_args();
        $type = array_shift($args);
        $out = [];

        foreach ($this->callbacks as $cid=>$callback) {
            if ($callback->type == $type) {
                $res = call_user_func_array($callback,$args);
                if (!is_null($res)) {
                    $out[$cid] = $res;
                }
            }
        }

        return $out;
    }
}