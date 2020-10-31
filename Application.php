<?php

namespace Pantheion\Engine;

use Dotenv\Dotenv;
use Noodlehaus\Config;
use Pantheion\Database\Connection;
use Pantheion\Database\Manager;
use Pantheion\Database\Table\Manager as TableManager;
use Pantheion\Database\Migration\Manager as MigrationManager;
use Pantheion\Database\Seed\Manager as SeedManager;
use Pantheion\Hashing\Hash;
use Pantheion\Http\Client\Client;
use Pantheion\Http\Request;
use Pantheion\Routing\RouteMapper;
use Pantheion\Routing\Router;
use Pantheion\Session\Session;
use Pantheion\Session\SessionFileHandler;
use Pantheion\Logging\Manager as LogManager;

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

        if(file_exists($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "storage/sessions")) {
            $this->session();
        }

        $this->routing();
        $this->http();
        $this->database();
        $this->logging();
        $this->hashing();
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
        $this->container->bind(Client::class, fn() => new Client, true);
    }

    protected function database()
    {
        $this->container->bind(Manager::class, function() {
            $driver = "mysql";
            return new Manager($driver);
        }, true);

        $this->container->bind(Connection::class, function() {
            return $this->container->make(Manager::class)->connect([
                'host' => 'localhost',
                'port' => '3306',
                'database' => 'zephyr',
                'user' => 'root',
                'password' => '',
            ]);
        }, true);

        $this->container->bind(TableManager::class, fn() => new TableManager, true);
        $this->container->bind(MigrationManager::class, fn() => new MigrationManager, true);
        $this->container->bind(SeedManager::class, fn() => new SeedManager, true);
    }

    public function session()
    {
        $this->container->bind(SessionFileHandler::class, fn() => new SessionFileHandler, true);

        $this->container->bind(Session::class, function() {
            return new Session($this->container->make(SessionFileHandler::class));
        }, true);
    }

    public function logging()
    {
        $this->container->bind(LogManager::class, fn() => new LogManager, true);
    }

    public function hashing()
    {
        $this->container->bind(Hash::class, fn() => new Hash, true);
    }
}