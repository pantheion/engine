<?php

namespace Pantheion\Engine;

class Container
{
    static protected $instance;
    
    protected $bindings;
    protected $resolved;
    protected $singletons;

    protected function __construct()
    {
        $this->bindings = [];
        $this->resolved = [];
        $this->singletons = [];
    }

    public static function get()
    {
        if(static::$instance === null)
        {
            static::$instance = new Container;
        }

        return static::$instance;
    }

    public function bind(string $class, \Closure $binding, bool $singleton = false)
    {
        $this->bindings[$class] = ['binding' => $binding, 'singleton' => $singleton];
    }

    public function make(string $class, array $params = [])
    {
        if(isset($this->singletons[$class])) {
            return $this->singletons[$class];
        }

        if(!isset($this->bindings[$class])) {
            throw new \Exception("Class not bound to anything");
        }

        $binding = $this->getBinding($class);
        $resolution = $this->run($binding, $params);

        if($this->isSingleton($class)) {
            $this->singletons[$class] = $resolution;
        }

        $this->resolved[$class] = true;
        return $resolution;
    }

    protected function getBinding(string $class)
    {
        return $this->bindings[$class]['binding'];
    }

    protected function isSingleton(string $class)
    {
        return $this->bindings[$class]['singleton'];
    }

    protected function run($binding, $params = [])
    {
        return $binding($this, $params);
    }

    public function isBound($class)
    {
        return isset($this->bindings[$class]);
    }
}
