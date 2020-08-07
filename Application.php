<?php

namespace Pantheion\Engine;

use Dotenv\Dotenv;
use Noodlehaus\Config;

class Application
{
    const PATH_CONFIG = "config/default.php";

    protected $container;

    public function __construct()
    {
        $this->container = Container::get();
        
        $this->basicBindings();
        $this->loadEnv();
    }

    public function bind(string $class, \Closure $binding)
    {
        $this->container->bind($class, $binding);
    }

    public function singleton(string $class, \Closure $binding)
    {
        $this->container->bind($class, $binding, true);
    }

    public function make(string $class, array $params = [])
    {
        return $this->container->make($class, $params);
    }

    protected function basicBindings()
    {
        $this->container->bind('config', function() {
            return new Config(Application::PATH_CONFIG);
        }, true);

        $this->container->bind('env', function () {
            return Dotenv::createImmutable($_SERVER['DOCUMENT_ROOT']);
        }, true);
    }

    protected function loadEnv()
    {
        $this->container->make('env')->load();
    }
}