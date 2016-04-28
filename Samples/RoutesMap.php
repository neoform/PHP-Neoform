<?php

    namespace MyApp;

    use Neoform\Router\Route\Model as Route;

    class Routes extends \Neoform\Router\Routes {

        /**
         * @return Route
         */
        public function get() {

            return new Route([
                'controller' => 'MyApp\Controller\Index',
                'children'   => [
                    // API
                    'api' => new Route([
                        'controller' => 'MyApp\Controller\Api',
                        'secure' => true,
                        'slugs' => [
                            0 => 'action',
                        ],
                    ]),
                    // Account
                    'account' => new Route([
                        'controller' => 'MyApp\Controller\Account',
                        'secure' => true,
                        'locale' => [
                            'en' => 'account',
                            'fr' => 'compte',
                        ],
                        'children' => [
                            'login' => new Route([
                                'controller' => 'MyApp\Controller\Account\Login',
                                'locale' => [
                                    'en' => 'login',
                                    'fr' => 'connexion',
                                ],
                            ]),
                            'create' => new Route([
                                'controller' => 'MyApp\Controller\Account\Create',
                                'locale' => [
                                    'en' => 'create',
                                    'fr' => 'créer',
                                ],
                            ]),
                            'logout' => new Route([
                                'controller' => 'MyApp\Controller\Account\Logout',
                                'locale' => [
                                    'en' => 'logout',
                                    'fr' => 'quitter',
                                ],
                            ]),
                            'passwordretreive' => new Route([
                                'controller' => 'MyApp\Controller\Account\Passwordlost',
                                'slugs'   => [
                                    0 => 'action',
                                ],
                                'locale' => [
                                    'en' => 'passwordretreive',
                                    'fr' => 'mot_de_passe_perdu',
                                ],
                            ]),
                            'passwordreset' => new Route([
                                'controller' => 'MyApp\Controller\Account\Passwordfound',
                                'locale' => [
                                    'en' => 'passwordreset',
                                    'fr' => 'mot_de_passe_trouvé',
                                ],
                                'slugs' => [
                                    0 => 'code',
                                ],
                            ]),
                            'info' => new Route([
                                'controller' => 'MyApp\Controller\Account\Info',
                                'locale' => [
                                    'en' => 'info',
                                    'fr' => 'info',
                                ],
                            ]),
                            'password' => new Route([
                                'controller' => 'MyApp\Controller\Account\Password',
                                'locale' => [
                                    'en' => 'password',
                                    'fr' => 'mot_de_passe',
                                ],
                                'slugs' => [
                                    0 => 'action',
                                ],
                            ]),
                            'email' => new Route([
                                'controller' => 'MyApp\Controller\Account\Email',
                                'locale' => [
                                    'en' => 'email',
                                    'fr' => 'courriel',
                                ],
                                'slugs'   => [
                                    0 => 'action',
                                ],
                            ]),

                            'ajax' => new Route([
                                'controller' => 'MyApp\Controller\Account\Ajax',
                                'children' => [

                                    'check' => new Route([
                                        'controller' => 'MyApp\Controller\Account\Ajax\Check',
                                        'slugs'   => [
                                            0 => 'action',
                                        ],
                                    ]),
                                    'login' => new Route([
                                        'controller' => 'MyApp\Controller\Account\Ajax\Login',
                                        'slugs'   => [
                                            0 => 'action',
                                        ],
                                    ]),
                                    'insert' => new Route([
                                        'controller' => 'MyApp\Controller\Account\Ajax\Insert',
                                    ]),
                                    'update' => new Route([
                                        'controller' => 'MyApp\Controller\Account\Ajax\Update',
                                    ]),
                                    'password_lost' => new Route([
                                        'controller' => 'MyApp\Controller\Account\Ajax\Passwordlost',
                                    ]),

                                    'dialog' => new Route([
                                        'controller' => 'MyApp\Controller\Account\Ajax\Dialog',
                                        'children' => [

                                            'login' => new Route([
                                                'controller' => 'MyApp\Controller\Account\Ajax\Dialog\Login',
                                            ]),
                                            'create' => new Route([
                                                'controller' => 'MyApp\Controller\Account\Ajax\Dialog\Create',
                                            ]),
                                            'lostpassword' => new Route([
                                                'controller' => 'MyApp\Controller\Account\Ajax\Dialog\Lostpassword',
                                            ]),
                                        ],
                                    ]),
                                ],
                            ]),
                        ],
                    ]),

                    // Info
                    'info' => new Route([
                        'controller' => 'MyApp\Controller\Info',
                        'secure' => false,
                        'locale' => [
                            'en' => 'info',
                            'fr' => 'info',
                        ],
                        'slugs' => [
                            0 => 'slug',
                        ],
                    ]),

                    // Admin
                    'admin' => new Route([
                        'controller' => 'MyApp\Controller\Admin',
                        'secure'     => true,
                        'resources'  => 'admin',
                        'children'   => [

                            // User
                            'user' => new Route([
                                'controller' => 'MyApp\Controller\Admin\User',
                                'resources'  => 'admin/user',
                                'children'   => [
                                    // Ajax
                                    'ajax' => new Route([
                                        'controller' => 'MyApp\Controller\Admin\User\Ajax',
                                        'slugs'   => [
                                            0 => 'action',
                                        ],
                                    ]),

                                    // New
                                    'new' => new Route([
                                        'controller' => 'MyApp\Controller\Admin\User\Create',
                                        'resources' => 'admin/user/create',
                                    ]),

                                    // View
                                    'view' => new Route([
                                        'controller' => 'MyApp\Controller\Admin\User\View',
                                        'resources' => 'admin/user/view',
                                    ]),

                                    // Edit
                                    'edit' => new Route([
                                        'controller' => 'MyApp\Controller\Admin\User\Edit',
                                        'resources' => 'admin/user/edit',
                                    ]),
                                ],
                            ]),

                            // ACL
                            'acl' => new Route([
                                'controller' => 'MyApp\Controller\Admin\Acl',
                                //'resources' => 'admin/acl',
                                'children' => [

                                    // Groups
                                    'group' => new Route([
                                        'controller' => 'MyApp\Controller\Admin\Acl\Group',
                                        'resources'  => 'admin/acl/group',
                                        'children'   => [
                                            // Ajax
                                            'ajax' => new Route([
                                                'controller' => 'MyApp\Controller\Admin\Acl\Group\Ajax',
                                                'slugs'      => [
                                                    0 => 'action',
                                                ],
                                            ]),

                                            // New
                                            'new' => new Route([
                                                'controller' => 'MyApp\Controller\Admin\Acl\Group\Create',
                                                'resources'  => 'admin/acl/group/create',
                                            ]),

                                            // View
                                            'view' => new Route([
                                                'controller' => 'MyApp\Controller\Admin\Acl\Group\View',
                                                'resources'  => 'admin/acl/group/view',
                                            ]),

                                            // Edit
                                            'edit' => new Route([
                                                'controller' => 'MyApp\Controller\Admin\Acl\Group\Edit',
                                                'resources'  => 'admin/acl/group/edit',
                                            ]),
                                        ],
                                    ]),

                                    // Roles
                                    'role' => new Route([
                                        'controller' => 'MyApp\Controller\Admin\Acl\Role',
                                        'resources'  => 'admin/acl/role',
                                        'children'   => [
                                            // Ajax
                                            'ajax' => new Route([
                                                'controller' => 'MyApp\Controller\Admin\Acl\Role\Ajax',
                                                'slugs' => [
                                                    0 => 'action',
                                                ],
                                            ]),

                                            // New
                                            'new' => new Route([
                                                'controller' => 'MyApp\Controller\Admin\Acl\Role\Create',
                                                'resources'  => 'admin/acl/role/create',
                                            ]),

                                            // View
                                            'view' => new Route([
                                                'controller' => 'MyApp\Controller\Admin\Acl\Role\View',
                                                'resources'  => 'admin/acl/role/view',
                                            ]),

                                            // Edit
                                            'edit' => new Route([
                                                'controller' => 'MyApp\Controller\Admin\Acl\Role\Edit',
                                                'resources'  => 'admin/acl/role/edit',
                                            ]),
                                        ],
                                    ]),

                                    // Resources
                                    'resource' => new Route([
                                        'controller' => 'MyApp\Controller\Admin\Acl\Resource',
                                        'resources' => 'admin/acl/resource',
                                        'slugs' => [
                                            0 => 'id',
                                        ],
                                        'children' => [
                                            // Ajax
                                            'ajax' => new Route([
                                                'controller' => 'MyApp\Controller\Admin\Acl\Resource\Ajax',
                                                'slugs' => [
                                                    0 => 'action',
                                                ],
                                                'children' => [
                                                    // Ajax
                                                    'dialog' => new Route([
                                                        'controller' => 'MyApp\Controller\Admin\Acl\Resource\Ajax\Dialog',
                                                        'slugs' => [
                                                            0 => 'action',
                                                        ],
                                                    ]),
                                                ],
                                            ]),

                                            // New
                                            'new' => new Route([
                                                'controller' => 'MyApp\Controller\Admin\Acl\Resource\Create',
                                                'resources'  => 'admin/acl/resource/create',
                                            ]),

                                            // View
                                            'view' => new Route([
                                                'controller' => 'MyApp\Controller\Admin\Acl\Resource\View',
                                                'resources'  => 'admin/acl/resource/view',
                                            ]),

                                            // Edit
                                            'edit' => new Route([
                                                'controller' => 'MyApp\Controller\Admin\Acl\Resource\Edit',
                                                'resources'  => 'admin/acl/resource/edit',
                                            ]),
                                        ],
                                    ]),
                                ],
                            ]),

                            // Locale
                            'locale' => new Route([
                                'controller' => 'MyApp\Controller\Admin\Locale',
                                'resources'  => 'admin/locale',
                                'children'   => [

                                    // Namespaces
                                    'namespaces' => new Route([
                                        'controller' => 'MyApp\Controller\Admin\Locale\Namespaces',
                                        'children' => [

                                            // Ajax
                                            'ajax' => new Route([
                                                'controller' => 'MyApp\Controller\Admin\Locale\Namespaces\Ajax',
                                                'slugs' => [
                                                    0 => 'action',
                                                ]
                                            ]),

                                            // Messages
                                            'messages' => new Route([
                                                'controller' => 'MyApp\Controller\Admin\Locale\Namespaces\Messages',
                                                'children' => [

                                                    // Ajax
                                                    'ajax' => new Route([
                                                        'controller' => 'MyApp\Controller\Admin\Locale\Namespaces\Messages\Ajax',
                                                        'slugs' => [
                                                            0 => 'action',
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
