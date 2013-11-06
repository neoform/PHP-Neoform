<?php

    namespace neoform\http;

    use neoform\output;
    use neoform\locale;
    use neoform\render\view;
    use neoform\render\json;

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
            $output      = output::instance();
            $output_type = $output->output_type();

            $output->flush();
            locale::instance()->set_namespace('main');
            $output->http_status_code(403);

            if ($output_type === output\model::JSON) {
                $json          = new json;
                $json->status  = 'error';
                $json->message = 'Access Denied';
                $json->render();
            } else {
                $view             = new view;
                $view->meta_title = locale::instance()->translate('Access Denied');
                $view->pre_header = locale::instance()->translate('403: Forbidden');
                $view->header     = locale::instance()->translate('Access Denied');
                $view->body       = locale::instance()->translate('You do not have the required permissions to access this page');

                $view->render('error');
            }
        }
        /**
         * Error 404 action
         */
        public static function show404() {
            $output      = output::instance();
            $output_type = $output->output_type();

            $output->flush();
            locale::instance()->set_namespace('main');
            $output->http_status_code(404);

            if ($output_type === output\model::JSON) {
                $json          = new json;
                $json->status  = 'error';
                $json->message = 'Not Found';
                $json->render();
            } else {
                $view             = new view;
                $view->meta_title = locale::instance()->translate('Page Not Found');
                $view->pre_header = locale::instance()->translate('Page Not Found');
                $view->header     = locale::instance()->translate('404: Page Not Found');
                $view->body       = locale::instance()->translate('The page you requested does not exist');

                $view->render('error');
            }
        }

        /**
         * Error 500 action
         */
        public static function show500() {
            $output      = output::instance();
            $output_type = $output->output_type();

            $output->flush();
            locale::instance()->set_namespace('main');
            $output->http_status_code(500);

            if ($output_type === output\model::JSON) {
                $json          = new json;
                $json->status  = 'error';
                $json->message = 'Server Error';
                $json->render();
            } else {
                $view             = new view;
                $view->meta_title = locale::instance()->translate('Server Error');
                $view->pre_header = locale::instance()->translate('Server Error');
                $view->header     = locale::instance()->translate('500: Server Error');
                $view->body       = locale::instance()->translate('There was a problem generating this page');

                $view->render('error');
            }
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

            $output      = output::instance();
            $output_type = $output->output_type();

            $output->flush();

            if (! $hard_error) {
                locale::instance()->set_namespace('main');
            }
            $output->http_status_code($status_code);

            $message = $message ? $message : (! $title ? 'There was a problem generating this page' : null);

            if ($output_type === output\model::JSON) {
                $json          = new json;
                $json->status  = 'error';
                $json->message = $hard_error ? 'Error' : locale::instance()->translate('Error');
                $json->render();
            } else {
                $view             = new view;
                $view->meta_title = $hard_error ? 'Error' : locale::instance()->translate('Error');
                $view->pre_header = $hard_error ? 'Error' : locale::instance()->translate('Error');
                $view->header     = $hard_error ? ($title ? $title : 'Server Error') : locale::instance()->translate($title ? $title : 'Server Error');
                $view->body       = $hard_error ? $message : locale::instance()->translate($message);

                $view->render('error');
            }
        }
    }