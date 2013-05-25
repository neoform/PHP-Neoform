<?php

    return new http_route([
        'controller' => '/index',
        'children' => [
            'error' => new http_route([
                'controller' => '/error',
            ]),

            // Account
            'account' => new http_route([
                'controller' => '/account',
                'secure' => true,
                'locale' => [
                    'en' => 'account',
                    'fr' => 'compte',
                ],
                'children' => [
                    'login' => new http_route([
                        'controller' => '/account/login',
                        'locale' => [
                            'en' => 'login',
                            'fr' => 'connexion',
                        ],
                    ]),
                    'create' => new http_route([
                        'controller' => '/account/create',
                        'locale' => [
                            'en' => 'create',
                            'fr' => 'créer',
                        ],
                    ]),
                    'logout' => new http_route([
                        'controller' => '/account/logout',
                        'locale' => [
                            'en' => 'logout',
                            'fr' => 'quitter',
                        ],
                    ]),
                    'passwordretreive' => new http_route([
                        'controller' => '/account/password_lost',
                        'locale' => [
                            'en' => 'password_lost',
                            'fr' => 'mot_de_passe_perdu',
                        ],
                    ]),
                    'passwordreset' => new http_route([
                        'controller' => '/account/password_found',
                        'locale' => [
                            'en' => 'password_found',
                            'fr' => 'mot_de_passe_trouvé',
                        ],
                    ]),
                    'info' => new http_route([
                        'controller' => '/account/info',
                        'locale' => [
                            'en' => 'info',
                            'fr' => 'info',
                        ],
                    ]),
                    'password' => new http_route([
                        'controller' => '/account/password',
                        'locale' => [
                            'en' => 'password',
                            'fr' => 'mot_de_passe',
                        ],
                    ]),
                    'email' => new http_route([
                        'controller' => '/account/email',
                        'locale' => [
                            'en' => 'email',
                            'fr' => 'courriel',
                        ],
                    ]),

                    'ajax' => new http_route([
                        'controller' => '/account/ajax',
                        'children' => [

                            'check' => new http_route([
                                'controller' => '/account/ajax/check',
                            ]),
                            'login' => new http_route([
                                'controller' => '/account/ajax/login',
                            ]),
                            'insert' => new http_route([
                                'controller' => '/account/ajax/insert',
                            ]),
                            'update' => new http_route([
                                'controller' => '/account/ajax/update',
                            ]),
                            'password_lost' => new http_route([
                                'controller' => '/account/ajax/password_lost',
                            ]),

                            'dialog' => new http_route([
                                'controller' => '/account/ajax/dialog',
                                'children' => [

                                    'login' => new http_route([
                                        'controller' => '/account/ajax/dialog/login',
                                    ]),
                                    'create' => new http_route([
                                        'controller' => '/account/ajax/dialog/create',
                                    ]),
                                    'lostpassword' => new http_route([
                                        'controller' => '/account/ajax/dialog/lostpassword',
                                    ]),
                                ],
                            ]),
                        ],
                    ]),
                ],
            ]),

            // Admin
            'admin' => new http_route([
                'controller' => '/admin',
                'secure'     => true,
                'permission' => 'admin',
                'locale' => [
                    'en' => 'admin',
                    'fr' => 'admin',
                ],
                'children'   => [

                    // User
                    'user' => new http_route([
                        'controller' => '/admin/user',
                        'locale' => [
                            'en' => 'user',
                            'fr' => 'user',
                        ],
                        'children'   => [
                            // User
                            'view' => new http_route([
                                'controller' => '/admin/user/view',
                                'locale' => [
                                    'en' => 'view',
                                    'fr' => 'view',
                                ],
                            ]),
                            // Ajax
                            'ajax' => new http_route([
                                'controller' => '/admin/user/ajax',
                            ]),
                        ],
                    ]),

                    // Locale
                    'locale' => new http_route([
                        'controller' => '/admin/locale',
                        'locale' => [
                            'en' => 'locale',
                            'fr' => 'locale',
                        ],
                        'children' => [

                            // Messages
                            'messages' => new http_route([
                                'controller' => '/admin/locale/messages',
                                'locale' => [
                                    'en' => 'messages',
                                    'fr' => 'messages',
                                ],
                                'children' => [

                                    // Ajax
                                    'ajax' => new http_route([
                                        'controller' => '/admin/locale/messages/ajax',
                                    ]),
                                ],
                            ]),
                        ],
                    ]),
                ],
            ]),
        ],
    ]);

