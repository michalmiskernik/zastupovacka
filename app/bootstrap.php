<?php

/**
 * My Application bootstrap file.
 */
use Nette\Diagnostics\Debugger,
	Nette\Application\Routers\Route;


// Load Nette Framework
require LIBS_DIR . '/nette/nette/Nette/loader.php';
require LIBS_DIR . '/.composer/autoload.php';

// Configure application
$configurator = new Nette\Config\Configurator;

// Enable Nette Debugger for error visualisation & logging
$configurator->setDebugMode(
	$_SERVER['SERVER_NAME'] === 'localhost' ||
	php_sapi_name() === 'cli-server'
);
$configurator->enableDebugger(__DIR__ . '/../log');

// Enable RobotLoader - this will load all classes automatically
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->createRobotLoader()
	->addDirectory(APP_DIR)
	->register();

// Create Dependency Injection container from config.neon file
$configurator->addConfig(__DIR__ . '/config/config.neon',
	$configurator->debugMode ? $configurator::DEVELOPMENT : $configurator::PRODUCTION);

// Setup dibi
$configurator->onCompile[] = function ($configurator, $compiler) {
	$compiler->addExtension('dibi', new DibiNetteExtension);
};

$container = $configurator->createContainer();

// Setup router
$router = $container->router;
$router[] = new Route('index.php', 'List:view', Route::ONE_WAY);
$router[] = new Route('<presenter>/<action>[/<id>]', 'List:view');


// Configure and run the application!
$application = $container->application;
//$application->catchExceptions = TRUE;
$application->errorPresenter = 'Error';
$application->run();
