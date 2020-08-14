<?php

namespace Pantheion\Engine;

use Dotenv\Dotenv;
use Noodlehaus\Config;
use Pantheion\Http\Request;
use Pantheion\Routing\RouteMapper;
use Pantheion\Routing\Router;

class Application
{
    const PATH_CONFIG = "config/default.php";

    protected $container;

    public function __construct()
    {
        $this->container = Container::get();

        if (file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . '.env')) {
            $this->basic();
            $this->loadEnvFile();
        }

        $this->routing();
        $this->http();
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

    public function isBound(string $class)
    {
        return $this->container->isBound($class);
    }

    protected function basic()
    {
        $this->container->bind('config', fn() => new Config(Application::PATH_CONFIG), true);
        $this->container->bind('env', fn() => Dotenv::createImmutable($_SERVER['DOCUMENT_ROOT']), true);
    }

    protected function loadEnvFile()
    {
        $this->container->make('env')->load();
    }

    protected function routing()
    {
        $this->container->bind(Router::class, fn() => new Router, true);
        $this->container->bind(RouteMapper::class, fn() => new RouteMapper($this->make(Router::class)), true);
    }

    protected function http()
    {
        $this->container->bind('request', fn() => Request::capture(), true);
    }
}