<?php

namespace SharengoCore\Traits;

trait CallableParameter
{
    /**
     * Method overloading: return/call plugins
     *
     * If the method is a callable, call it, passing the parameters provided.
     * Otherwise, return the control to the parent.
     *
     * @param  string $callable
     * @param  array  $params
     * @return mixed
     */
    public function __call($method, $params)
    {
        if (property_exists($this, $method) && is_callable($this->$method)) {
            return call_user_func_array($this->$method, $params);
        }

        return parent::__call($method, $params);
    }
}