<?php

    /**
     * Controller base class
     */
    abstract class controller {

        /**
         * Name of the default action used when no action is specified
         */
        const DEFAULT_ACTION = 'default_action';

        /**
         * Action called when no action is specified
         */
        abstract public function default_action();

        /**
         * Error 403 action
         */
        public static function show403() {
            core::output()->flush();
            core::locale()->set_namespace('main');
            core::output()->http_status_code(403);

            $view             = new render_view;
            $view->meta_title = core::locale()->translate('Access Denied');
            $view->pre_header = core::locale()->translate('403: Forbidden');
            $view->header     = core::locale()->translate('Access Denied');
            $view->body       = core::locale()->translate('You do not have the required permissions to access this page');

            $view->render('error');
        }
        /**
         * Error 404 action
         */
        public static function show404() {
            core::output()->flush();
            core::locale()->set_namespace('main');
            core::output()->http_status_code(404);

            $view             = new render_view;
            $view->meta_title = core::locale()->translate('Page Not Found');
            $view->pre_header = core::locale()->translate('Page Not Found');
            $view->header     = core::locale()->translate('404: Page Not Found');
            $view->body       = core::locale()->translate('The page you requested does not exist');

            $view->render('error');
        }

        /**
         * Error 500 action
         */
        public static function show500() {
            core::output()->flush();
            core::locale()->set_namespace('main');
            core::output()->http_status_code(500);

            $view             = new render_view;
            $view->meta_title = core::locale()->translate('Server Error');
            $view->pre_header = core::locale()->translate('Server Error');
            $view->header     = core::locale()->translate('500: Server Error');
            $view->body       = core::locale()->translate('There was a problem generating this page');

            $view->render('error');
        }

        /**
         * Generic error action
         *
         * @param integer|null $status_code
         * @param string|null  $title
         * @param string|null  $message
         * @param bool         $hard_error
         */
        public static function error($status_code=500, $title=null, $message=null, $hard_error=false) {

            core::output()->flush();

            if (! $hard_error) {
                core::locale()->set_namespace('main');
            }
            core::output()->http_status_code($status_code);

            $message = $message ? $message : (! $title ? 'There was a problem generating this page' : null);

            $view             = new render_view;
            $view->meta_title = $hard_error ? 'Error' : core::locale()->translate('Error');
            $view->pre_header = $hard_error ? 'Error' : core::locale()->translate('Error');
            $view->header     = $hard_error ? ($title ? $title : 'Server Error') : core::locale()->translate($title ? $title : 'Server Error');
            $view->body       = $hard_error ? $message : core::locale()->translate($message);

            $view->render('error');
        }
    }