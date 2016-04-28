<?php

    // Include the framework's core
    require_once(realpath(__DIR__ . "/../library/Neoform/Core.php"));

    $core = Neoform\Core::build(
        __DIR__ . '/..',
        'MyApp\Environment\\ProductionEnvironment'
    );

    // Create bootstrap for the MVC
    $bootstrap = new MyApp\Bootstrap(
        (isset($_SERVER['REQUEST_URI']) ? rawurldecode($_SERVER['REQUEST_URI']) : '/'),
        $_GET,
        $_POST,
        $_FILES,
        $_SERVER,
        $_COOKIE
    );

    $bootstrap
        ->buildResponse()
        ->render();
