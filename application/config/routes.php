<?php

    return new http_route([
        'controller' => 'controller_index',
        'children' => [
            'error' => new http_route([
                'controller' => 'controller_error',
            ]),

            // Account
            'account' => new http_route([
                'controller' => 'controller_account',
                'secure' => true,
                'locale' => [
                    'en' => 'account',
                    //'fr' => 'compte',
                ],
                'children' => [
                    'login' => new http_route([
                        'controller' => 'controller_account_login',
                        'locale' => [
                            'en' => 'login',
                            //'fr' => 'connexion',
                        ],
                    ]),
                    'create' => new http_route([
                        'controller' => 'controller_account_create',
                        'locale' => [
                            'en' => 'create',
                            //'fr' => 'créer',
                        ],
                    ]),
                    'logout' => new http_route([
                        'controller' => 'controller_account_logout',
                        'locale' => [
                            'en' => 'logout',
                            //'fr' => 'quitter',
                        ],
                    ]),
                    'passwordretreive' => new http_route([
                        'controller' => 'controller_account_passwordlost',
                        'locale' => [
                            'en' => 'passwordretreive',
                            //'fr' => 'mot_de_passe_perdu',
                        ],
                    ]),
                    'passwordreset' => new http_route([
                        'controller' => 'controller_account_passwordfound',
                        'locale' => [
                            'en' => 'passwordreset',
                            //'fr' => 'mot_de_passe_trouvé',
                        ],
                        'segments' => [
                            3 => 'code',
                        ],
                    ]),
                    'info' => new http_route([
                        'controller' => 'controller_account_info',
                        'locale' => [
                            'en' => 'info',
                            //'fr' => 'info',
                        ],
                    ]),
                    'password' => new http_route([
                        'controller' => 'controller_account_password',
                        'locale' => [
                            'en' => 'password',
                            //'fr' => 'mot_de_passe',
                        ],
                    ]),
                    'email' => new http_route([
                        'controller' => 'controller_account_email',
                        'locale' => [
                            'en' => 'email',
                            //'fr' => 'courriel',
                        ],
                    ]),

                    'ajax' => new http_route([
                        'controller' => 'controller_account_ajax',
                        'children' => [

                            'check' => new http_route([
                                'controller' => 'controller_account_ajax_check',
                            ]),
                            'login' => new http_route([
                                'controller' => 'controller_account_ajax_login',
                            ]),
                            'insert' => new http_route([
                                'controller' => 'controller_account_ajax_insert',
                            ]),
                            'update' => new http_route([
                                'controller' => 'controller_account_ajax_update',
                            ]),
                            'password_lost' => new http_route([
                                'controller' => 'controller_account_ajax_passwordlost',
                            ]),

                            'dialog' => new http_route([
                                'controller' => 'controller_account_ajax_dialog',
                                'children' => [

                                    'login' => new http_route([
                                        'controller' => 'controller_account_ajax_dialog_login',
                                    ]),
                                    'create' => new http_route([
                                        'controller' => 'controller_account_ajax_dialog_create',
                                    ]),
                                    'lostpassword' => new http_route([
                                        'controller' => 'controller_account_ajax_dialog_lostpassword',
                                    ]),
                                ],
                            ]),
                        ],
                    ]),
                ],
            ]),

            // Admin
            'admin' => new http_route([
                'controller' => 'controller_admin',
                'secure'     => true,
                'resources'  => 'admin',
                'children'   => [

                    // User
                    'users' => new http_route([
                        'controller' => 'controller_admin_user',
                        'resources'  => 'user',
                        'children'   => [
                            // View
                            'view' => new http_route([
                                'controller' => 'controller_admin_user_view',
                                'resources' => 'user view',
                            ]),
                            // Ajax
                            'ajax' => new http_route([
                                'controller' => 'controller_admin_user_ajax',
                            ]),
                        ],
                    ]),

                    // Groups
                    'groups' => new http_route([
                        'controller' => 'controller_admin_group',
                        //'resources' => 'group',
                        'children' => [
                            // View
                            'view' => new http_route([
                                'controller' => 'controller_admin_group_view',
                                //'resources' => 'group view',
                            ]),
                            // Ajax
                            'ajax' => new http_route([
                                'controller' => 'controller_admin_group_ajax',
                            ]),
                        ],
                    ]),

                    // ACL
                    'acl' => new http_route([
                        'controller' => 'controller_admin_acl',
                        //'resources' => 'acl',
                        'children' => [

                            // Roles
                            'roles' => new http_route([
                                'controller' => 'controller_admin_acl_role',
                                //'resources' => 'acl role',
                                'children' => [
                                    // Ajax
                                    'ajax' => new http_route([
                                        'controller' => 'controller_admin_acl_role_ajax',
                                    ]),
                                ],
                            ]),

                            // Resources
                            'resources' => new http_route([
                                'controller' => 'controller_admin_acl_resource',
                                //'resources' => 'acl resource',
                                'children' => [
                                    // Ajax
                                    'ajax' => new http_route([
                                        'controller' => 'controller_admin_acl_resource_ajax',
                                    ]),
                                ],
                            ]),

                            // Ajax
                            'ajax' => new http_route([
                                'controller' => 'controller_admin_group_ajax',
                            ]),
                        ],
                    ]),

                    // Locale
                    'locale' => new http_route([
                        'controller' => 'controller_admin_locale',
                        'resources'  => 'locale',
                        'children'   => [

                            // Namespaces
                            'namespaces' => new http_route([
                                'controller' => 'controller_admin_locale_namespaces',
                                'children' => [

                                    // Ajax
                                    'ajax' => new http_route([
                                        'controller' => 'controller_admin_locale_namespaces_ajax',
                                        'segments' => [
                                            5 => 'action',
                                        ]
                                    ]),

                                    // Messages
                                    'messages' => new http_route([
                                        'controller' => 'controller_admin_locale_namespaces_messages',
                                        'children' => [

                                            // Ajax
                                            'ajax' => new http_route([
                                                'controller' => 'controller_admin_locale_namespaces_messages_ajax',
                                                'segments' => [
                                                    6 => 'action',
                                                ]
                                            ]),
                                        ],
                                    ]),

                                ],
                            ]),
                        ],
                    ]),
                ],
            ]),
        ],
    ]);

