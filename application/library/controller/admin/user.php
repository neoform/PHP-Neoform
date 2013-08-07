<?php

    class controller_admin_user extends controller_admin {

        public function default_action() {

            $page = (int) core::http()->parameter('page');
            $per_page = 10;

            if ($page < 1) {
                $page = 1;
            }

            $view = new render_view;

            $view->meta_title = 'Users';

            //$users = new user_collection(entity_dao::get('user')->limit(20, 'id', 'asc', null));
            $users = new user_collection(entity_dao::get('user')->pagination('id', 'asc', ($page - 1) * $per_page, $per_page));
            $users->user_date_collection(); // preload user_dates

            $view->users    = $users;

            $view->page     = $page;
            $view->total    = entity_dao::get('user')->count();
            $view->per_page = $per_page;

            $view->render('admin/user');
        }
    }