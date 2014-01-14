<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


chdir(__DIR__);

// Setup autoloading
require 'init_autoloader.php';

// Run the application!
Zend\Mvc\Application::init(require 'config/application.config.php')->run();
