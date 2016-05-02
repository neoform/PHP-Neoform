<?php

    namespace MyApp;

    use Neoform\Locale;
    use Neoform;

    /**
     * Controller base class
     */
    abstract class Controller extends Neoform\Router\Controller {

        /**
         * @var Neoform\Request\Model
         */
        protected $request;

        /**
         * @var Neoform\Response\Http\Builder
         */
        protected $response;

        /**
         * @var Neoform\Session\Model
         */
        protected $session;

        /**
         * @var bool
         */
        protected $isMobile = false;

        /**
         * Construct
         *
         * @param Neoform\Request\Model $request
         * @param Neoform\Response\Builder $response
         */
        public function __construct(Neoform\Request\Model $request, Neoform\Response\Builder $response) {
            $this->request  = $request;
            $this->response = $response;

            // Set the session cookie
            $session = $this->request->getSession();
            if ($session && $session->hasTokenChanged()) {
                $response->setCookie(
                    $this->request->getSession()->getSessionCookieKey(),
                    $this->request->getSession()->getToken()
                );
            }

            // Mobile status
            $this->isMobile = (bool) $this->request->getCookies()->get('mobile');
            if (! $this->isMobile) {
                $betterBrowser = new Neoform\Web\Browser\Better($request->getServer()->getAgent());
                $this->isMobile = $betterBrowser->isMobile();
            }
        }

        /**
         * Apply default variables to the view
         *
         * @param Neoform\Render\View $view
         */
        protected function applyDefaults(Neoform\Render\View $view) {
            $view->setRequest($this->request);
        }

        /**
         * Error 403 action
         *
         * @throws \Exception
         */
        public function show403() {

            $this->response->resetHeaders()
                 ->resetBody()
                 ->resetEncoding()
                 ->setResponseCode(403);

            // If JSON context
            if ($this->request->getServer()->getAccepts()->accepts('application/json')) {
                $view = new Neoform\Render\Json;
                $view->set('status', 'error');
                $view->set('message', 'Access Denied');
                $this->response->setContentType('application/json');

            // Default to HTML
            } else {
                $locale = Locale::getService()->get();
                $locale->setNamespace('main');

                $view = new Neoform\Render\Html;

                $view->meta_title = $locale->translate('Access Denied');
                $view->pre_header = $locale->translate('403: Forbidden');
                $view->header     = $locale->translate('Access Denied');
                $view->body       = $locale->translate('You do not have the required permissions to access this page');

                $view->applyTemplate('error');
                $this->response->setContentType('text/html');
            }

            $this->applyDefaults($view);

            return $view;
        }

        /**
         * Error 404 action
         *
         * @return Neoform\Render\View
         */
        public function show404() {

            $this->response->resetHeaders()
                 ->resetBody()
                 ->resetEncoding()
                 ->setResponseCode(404);

            // If JSON context
            if ($this->request->getServer()->getAccepts()->accepts('application/json')) {
                $view = new Neoform\Render\Json;
                $view->set('status', 'error');
                $view->set('message', 'Not Found');
                $this->response->setContentType('application/json');

            // Default to HTML
            } else {
                $locale = Locale::getService()->get();
                $locale->setNamespace('main');

                $view = new Neoform\Render\Html;
                $view->meta_title = $locale->translate('Page Not Found');
                $view->pre_header = $locale->translate('Page Not Found');
                $view->header     = $locale->translate('404: Page Not Found');
                $view->body       = $locale->translate('The page you requested does not exist');

                $view->applyTemplate('error');
                $this->response->setContentType('text/html');
            }

            $this->applyDefaults($view);

            return $view;
        }

        /**
         * Error 500 action
         *
         * @return Neoform\Response\Http
         * @throws \Exception
         */
        public function show500() {

            $this->response->resetHeaders()
                 ->resetBody()
                 ->resetEncoding()
                 ->setResponseCode(500);

            // If JSON context
            if ($this->request->getServer()->getAccepts()->accepts('application/json')) {
                $view = new Neoform\Render\Json;
                $view->set('status', 'error');
                $view->set('message', 'Server Error');
                $this->response->setContentType('application/json');

            // Default to HTML
            } else {
                $locale = Locale::getService()->get();
                $locale->setNamespace('main');

                $view = new Neoform\Render\Html;
                $view->meta_title = $locale->translate('Server Error');
                $view->pre_header = $locale->translate('Server Error');
                $view->header     = $locale->translate('500: Server Error');
                $view->body       = $locale->translate('There was a problem generating this page');

                $view->applyTemplate('error');
                $this->response->setContentType('text/html');
            }

            $this->applyDefaults($view);

            return $view;
        }

        /**
         * Generic error action
         *
         * @param int $statusCode
         * @param null $title
         * @param null $message
         * @param bool $hardError
         *
         * @return Neoform\Response\Http
         * @throws \Exception
         */
        public function error($statusCode=500, $title=null, $message=null, $hardError=false) {

            $this->response->resetHeaders()
                     ->resetBody()
                     ->resetEncoding()
                     ->setResponseCode($statusCode);

            if (! $hardError) {
                $locale = Locale::getService()->get();
                $locale->set_namespace('main');
            }

            $message = $message ? $message : (! $title ? 'There was a problem generating this page' : null);

            // If JSON context
            if ($this->request->getServer()->getAccepts()->accepts('application/json')) {
                $view = new Neoform\Render\Json;
                $view->set('status', 'error');
                $view->set('message', $hardError ? "Error {$message}" : $locale->translate('Error') . " {$message}");
                $this->response->setContentType('application/json');

            // Default to HTML
            } else {
                $view = new Neoform\Render\Html;
                $view->meta_title = $hardError ? "Error {$statusCode}" : $locale->translate('Error') . " {$statusCode}";
                $view->pre_header = $hardError ? "Error {$statusCode}" : $locale->translate('Error') . " {$statusCode}";
                $view->header     = $hardError ? ($title ? $title : 'Server Error')
                    : $locale->translate($title ? $title : 'Server Error');
                $view->body       = $hardError ? $message : $locale->translate($message);

                $view->applyTemplate('error');
                $this->response->setContentType('text/html');
            }

            $this->applyDefaults($view);

            return $view;
        }

        /**
         * Checks the http slugs, if they don't match the $slugRegex then user is redirected to a 404 page
         *
         * @param string $controllerSlugRegex
         * @param string $nonControllerSlugRegex
         * @param array  $permittedGets            GET parameter names that are allowed
         * @param array  $permittedParameters      /var1:val1/var2:val2/ parameter names that are allowed
         *
         * @return Neoform\Render\View|null
         */
        public function enforceUrl($controllerSlugRegex=null, $nonControllerSlugRegex=null,
                                           array $permittedGets=null, array $permittedParameters=null) {

            if ($controllerSlugRegex !== null) {
                $slugs = $this->request->getControllerSlugs()->toArray();
                array_shift($slugs); // we don't want to have root be part of this
                if (! preg_match($controllerSlugRegex, '/' . join('/', $slugs))) {
                    return $this->show404();
                }
            }

            if ($nonControllerSlugRegex !== null) {
                if (! preg_match($nonControllerSlugRegex, join('/', $this->request->getNonControllerSlugs()->toArray()))) {
                    return $this->show404();
                }
            }

            // If any get values exist that aren't permitted, 404.
            if ($permittedGets !== null) {
                if (array_diff_key($this->request->getGet()->toArray(), array_flip($permittedGets + ['rc']))) {
                    return $this->show404();
                }
            }

            // If any get values exist that aren't permitted, 404.
            if ($permittedParameters !== null) {
                if (array_diff_key($this->request->getParameters()->toArray(), array_flip($permittedParameters))) {
                    return $this->show404();
                }
            }
        }

        /**
         * Checks the http slugs, if they don't match the $slugRegex then user is redirected to a 404 page
         *
         * @param string $slugRegex
         * @param array  $permittedGets       GET parameter names that are allowed
         * @param array  $permittedParameters /var1:val1/var2:val2/ parameter names that are allowed
         *
         * @return Neoform\Render\View|null
         */
        public function enforceUrlCustom($slugRegex=null, array $permittedGets=null, array $permittedParameters=null) {

            if ($slugRegex !== null) {
                $slugs = $this->request->getNonControllerSlugs()->toArray();
                array_shift($slugs);
                if (! preg_match($slugRegex, '/' . join('/', $slugs))) {
                    return $this->show404();
                }
            }

            // If any get values exist that aren't permitted, 404.
            if ($permittedGets !== null) {
                if (array_diff_key($this->request->getGet()->toArray(), array_flip($permittedGets + ['rc']))) {
                    return $this->show404();
                }
            }

            // If any get values exist that aren't permitted, 404.
            if ($permittedParameters !== null) {
                if (array_diff_key($this->request->getParameters()->toArray(), array_flip($permittedParameters))) {
                    return $this->show404();
                }
            }
        }

        /**
         * Change the response to be a redirect
         *
         * @param string $location
         * @param int    $httpResponseCode
         */
        public function redirect($location, $httpResponseCode=303) {
            $this->response
                 ->resetHeaders()
                 ->resetBody()
                 ->resetEncoding()
                 ->setResponseCode($httpResponseCode)
                 ->setHeader('Location', (string) $location ?: $this->request->getBaseUrl()->getRegularBaseUrl());
        }

        /**
         * If returns false, the XSRF verification failed
         *
         * @return bool
         */
        protected function jsonSetup() {
            $this->response->setContentType('application/json');

            if (! $this->request->getSession()->getXsrf()->isRequestValid()) {
                $this->response->setView($this->show403());
                return false;
            }

            return true;
        }

        /**
         * @param string $status
         */
        private function jsonStatusResponse($status) {
            $json = new Neoform\Render\Json;
            $json->set('status', $status);
            $this->response->setView($json, 'application/json');
        }

        /**
         * @param string|null $bounceUrl
         * @param string|null $message
         */
        protected function requireLogin($bounceUrl=null, $message=null) {

            $this->request->getSession()->getFlash()->set('login_bounce', $bounceUrl ?: $this->request->getServer()->getUri());

            if ($message) {
                $this->request->getSession()->getFlash()->set('login_message', $message);
            }

            if ($this->request->getServer()->getAccepts()->accepts('application/json')) {
                $this->jsonStatusResponse('login');
            } else {
                $this->redirect("{$this->request->getBaseUrl()->getSecureBaseUrl()}/account/login");
            }
        }

        /**
         * @param string $resource
         *
         * @return bool
         */
        protected function userHasResource($resource) {
            return (bool) $this->request->getSession()->getAuth()->getUser()->hasResource($resource);
        }
    }