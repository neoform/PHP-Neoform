<?php

    namespace neoform;

    class controller_admin_user extends controller_admin {

        public function default_action() {

            $page  = (int) http::instance()->parameter('page');
            $sort  = (string) http::instance()->parameter('sort') ?: 'id';
            $order = (string) http::instance()->parameter('order') ?: 'asc';

            $valid_sorts = ['id', 'email', 'status_id', 'created_on', 'last_login', ];

            if ($sort && ! in_array($sort, $valid_sorts)) {
                self::error(500, 'Invalid sort', "You cannot sort by \"{$sort}\"");
                return;
            }

            $per_page = 10;

            if ($page < 1) {
                $page = 1;
            }

            $view = new render\view;

            $view->meta_title = 'Users';

            //$users = new user_collection(entity::dao('user')->limit(20, 'id', 'asc', null));
            switch ($sort) {
                case 'id':
                case 'email':
                case 'status_id':
                    $users = new user\collection(
                        entity::dao_cacheless('user')->limit(
                            [
                                $sort => $order === 'asc' ? entity\record\dao::SORT_ASC : entity\record\dao::SORT_DESC
                            ],
                            ($page - 1) * $per_page,
                            $per_page
                        )
                    );
                    break;

                case 'created_on':
                case 'last_login':
                    $users = new user\collection(
                        entity::dao_cacheless('user\date')->limit(
                            [
                                $sort => $order === 'asc' ? entity\record\dao::SORT_ASC : entity\record\dao::SORT_DESC
                            ],
                            ($page - 1) * $per_page,
                            $per_page
                        )
                    );
                    break;
            }

            $users->user_date_collection(); // preload user_dates
            $users->site_collection();      // preload sites

            $view->users    = $users;

            $view->page     = $page;
            $view->sorter   = new render\sort($sort, $order, $valid_sorts);
            $view->total    = entity::dao('user')->count();
            $view->per_page = $per_page;

            $view->render('admin/user');
        }
    }
