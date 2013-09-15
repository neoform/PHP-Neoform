<?php

    namespace neoform;

    class controller_admin_user extends controller_admin {

        public function default_action() {

            $page     = (int) http::instance()->parameter('page');
            $per_page = 10;

            if ($page < 1) {
                $page = 1;
            }

            $view = new render\view;

            $view->meta_title = 'Users';

            //$users = new user_collection(entity::dao('user')->limit(20, 'id', 'asc', null));
            $users = new user\collection(
                entity::dao('user')->limit(
                    [
                        'id' => entity\record\dao::SORT_ASC
                    ],
                    ($page - 1) * $per_page,
                    $per_page
                )
            );
            $users->user_date_collection(); // preload user_dates

            $view->users    = $users;

            $view->page     = $page;
            $view->total    = entity::dao('user')->count();
            $view->per_page = $per_page;

            $view->render('admin/user');
        }
    }