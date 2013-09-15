<?php

    namespace neoform;

    use neoform\http\route\model as route;

    class routes extends http\routes {

        public function get() {

            return new route([
                'controller' => 'controller_index',
                'children'   => [

                    // Account
                    'account' => new route([
                        'controller' => 'controller_account',
                        'secure' => true,
                        'locale' => [
                            'en' => 'account',
                            //'fr' => 'compte',
                        ],
                        'children' => [
                            'login' => new route([
                                'controller' => 'controller_account_login',
                                'locale' => [
                                    'en' => 'login',
                                    //'fr' => 'connexion',
                                ],
                            ]),
                            'create' => new route([
                                'controller' => 'controller_account_create',
                                'locale' => [
                                    'en' => 'create',
                                    //'fr' => 'créer',
                                ],
                            ]),
                            'logout' => new route([
                                'controller' => 'controller_account_logout',
                                'locale' => [
                                    'en' => 'logout',
                                    //'fr' => 'quitter',
                                ],
                            ]),
                            'passwordretreive' => new route([
                                'controller' => 'controller_account_passwordlost',
                                'segments'   => [
                                    3 => 'action',
                                ],
                                'locale' => [
                                    'en' => 'passwordretreive',
                                    //'fr' => 'mot_de_passe_perdu',
                                ],
                            ]),
                            'passwordreset' => new route([
                                'controller' => 'controller_account_passwordfound',
                                'locale' => [
                                    'en' => 'passwordreset',
                                    //'fr' => 'mot_de_passe_trouvé',
                                ],
                                'segments' => [
                                    3 => 'code',
                                ],
                            ]),
                            'info' => new route([
                                'controller' => 'controller_account_info',
                                'locale' => [
                                    'en' => 'info',
                                    //'fr' => 'info',
                                ],
                            ]),
                            'password' => new route([
                                'controller' => 'controller_account_password',
                                'locale' => [
                                    'en' => 'password',
                                    //'fr' => 'mot_de_passe',
                                ],
                                'segments' => [
                                    3 => 'action',
                                ],
                            ]),
                            'email' => new route([
                                'controller' => 'controller_account_email',
                                'locale' => [
                                    'en' => 'email',
                                    //'fr' => 'courriel',
                                ],
                                'segments'   => [
                                    3 => 'action',
                                ],
                            ]),

                            'ajax' => new route([
                                'controller' => 'controller_account_ajax',
                                'children' => [

                                    'check' => new route([
                                        'controller' => 'controller_account_ajax_check',
                                        'segments'   => [
                                            4 => 'action',
                                        ],
                                    ]),
                                    'login' => new route([
                                        'controller' => 'controller_account_ajax_login',
                                        'segments'   => [
                                            4 => 'action',
                                        ],
                                    ]),
                                    'insert' => new route([
                                        'controller' => 'controller_account_ajax_insert',
                                    ]),
                                    'update' => new route([
                                        'controller' => 'controller_account_ajax_update',
                                    ]),
                                    'password_lost' => new route([
                                        'controller' => 'controller_account_ajax_passwordlost',
                                    ]),

                                    'dialog' => new route([
                                        'controller' => 'controller_account_ajax_dialog',
                                        'children' => [

                                            'login' => new route([
                                                'controller' => 'controller_account_ajax_dialog_login',
                                            ]),
                                            'create' => new route([
                                                'controller' => 'controller_account_ajax_dialog_create',
                                            ]),
                                            'lostpassword' => new route([
                                                'controller' => 'controller_account_ajax_dialog_lostpassword',
                                            ]),
                                        ],
                                    ]),
                                ],
                            ]),
                        ],
                    ]),

                    // Admin
                    'admin' => new route([
                        'controller' => 'controller_admin',
                        'secure'     => true,
                        'resources'  => 'admin',
                        'children'   => [

                            // User
                            'users' => new route([
                                'controller' => 'controller_admin_user',
                                'resources'  => 'user',
                                'children'   => [
                                    // View
                                    'view' => new route([
                                        'controller' => 'controller_admin_user_view',
                                        'resources'  => 'user view',
                                    ]),
                                    // Ajax
                                    'ajax' => new route([
                                        'controller' => 'controller_admin_user_ajax',
                                        'segments'   => [
                                            4 => 'action',
                                        ],
                                    ]),
                                ],
                            ]),

                            // Groups
                            'groups' => new route([
                                'controller' => 'controller_admin_group',
                                //'resources' => 'group',
                                'children' => [
                                    // View
                                    'view' => new route([
                                        'controller' => 'controller_admin_group_view',
                                        //'resources' => 'group view',
                                    ]),
                                    // Ajax
                                    'ajax' => new route([
                                        'controller' => 'controller_admin_group_ajax',
                                    ]),
                                ],
                            ]),

                            // ACL
                            'acl' => new route([
                                'controller' => 'controller_admin_acl',
                                //'resources' => 'acl',
                                'children' => [

                                    // Roles
                                    'roles' => new route([
                                        'controller' => 'controller_admin_acl_role',
                                        //'resources' => 'acl role',
                                        'children' => [
                                            // Ajax
                                            'ajax' => new route([
                                                'controller' => 'controller_admin_acl_role_ajax',
                                                'segments'   => [
                                                    5 => 'action',
                                                ],
                                            ]),
                                        ],
                                    ]),

                                    // Resources
                                    'resources' => new route([
                                        'controller' => 'controller_admin_acl_resource',
                                        //'resources' => 'acl resource',
                                        'children' => [
                                            // Ajax
                                            'ajax' => new route([
                                                'controller' => 'controller_admin_acl_resource_ajax',
                                                'segments' => [
                                                    5 => 'action',
                                                ],
                                            ]),
                                        ],
                                    ]),

                                    // Ajax
                                    'ajax' => new route([
                                        'controller' => 'controller_admin_group_ajax',
                                        'segments'   => [
                                            4 => 'action',
                                        ],
                                    ]),
                                ],
                            ]),

                            // Locale
                            'locale' => new route([
                                'controller' => 'controller_admin_locale',
                                'resources'  => 'locale',
                                'children'   => [

                                    // Namespaces
                                    'namespaces' => new route([
                                        'controller' => 'controller_admin_locale_namespaces',
                                        'children' => [

                                            // Ajax
                                            'ajax' => new route([
                                                'controller' => 'controller_admin_locale_namespaces_ajax',
                                                'segments' => [
                                                    5 => 'action',
                                                ]
                                            ]),

                                            // Messages
                                            'messages' => new route([
                                                'controller' => 'controller_admin_locale_namespaces_messages',
                                                'children' => [

                                                    // Ajax
                                                    'ajax' => new route([
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
        }
    }
