<?php

    //
    // Make a webpage
    //

    // Load some common classes/handlers
    require_once(realpath(getenv('CORE_LIB_ROOT')) . '/core.php');

    // Initialize the core
    core::init([
        'extension'   => getenv('CORE_PHP_EXT'),
        'environment' => getenv('CORE_ENVIRONMENT_NAME'),
        'application' => getenv('CORE_APP_ROOT'),
        'external'    => getenv('CORE_EXT_ROOT'),
        'logs'        => getenv('CORE_LOG_DIR'),
        'website'     => __DIR__,
    ]);

    // Create bootstrap for the MVC
    $page = (new bootstrap(
        //pass the routing path sent from the .htaccess file
        (isset($_SERVER['REQUEST_URI']) ? rawurldecode($_SERVER['REQUEST_URI']) : '/'),
        $_GET,
        $_POST,
        $_FILES,
        $_SERVER,
        $_COOKIE
    ))->execute()->send_headers();

    echo (string) $page;
