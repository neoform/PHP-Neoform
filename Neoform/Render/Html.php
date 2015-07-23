<?php

    namespace Neoform\Render;

    use ArrayObject;
    use Neoform\Locale;
    use Neoform;

    class Html extends ArrayObject implements View {

        /**
         * @var bool
         */
        protected $viewLocked;

        /**
         * @var string
         */
        protected $viewTemplate;

        /**
         * @var string
         */
        protected $viewApplicationPath;

        /**
         * Since views/templates are loaded within the context of this object (no way around this),
         * we need to block write access to some variables
         *
         * @var string[]
         */
        protected $viewProtectedVars = [ 'this', 'viewProtectedVars', 'viewLocked', 'viewTemplate', 'viewApplicationPath', ];

        /**
         * HTML view file extension
         */
        const VIEW_EXT = 'phtml';

        /**
         * Javascript file extension
         */
        const JS_EXT = 'js';

        /**
         * @throws Exception
         */
        public function __construct() {
            $this->viewApplicationPath = Neoform\Core::get()->getApplicationPath();
        }

        /**
         * @param string $template
         *
         * @return $this
         */
        public function applyTemplate($template) {
            $this->viewTemplate = $template;
            return $this;
        }

        /**
         * @return string|null
         * @throws Exception
         */
        public function __toString() {
            return (string) $this->render();
        }

        /**
         * @return string|null
         * @throws Exception
         */
        public function render() {
            if (! $this->viewTemplate) {
                throw new Exception('Template not set');
            }

            $this->viewLocked = true;

            ob_start();

            $this->inc($this->viewTemplate);

            try {
                return ob_get_clean() ?: null;
            } catch (\Exception $e) {
                ob_end_clean();
                throw new Exception('Output buffer error occurred', 0, $e);
            }
        }

        /**
         * @param string $jsView
         *
         * @return string
         * @throws \Exception
         */
        public function js($jsView) {
            $jsView = "{$this->viewApplicationPath}/dialogs/{$jsView}." . self::JS_EXT;

            if (file_exists($jsView))    {
                try {
                    return file_get_contents($jsView);
                } catch (\Exception $e) {
                    throw new Exception('Error occured while reading JS file', 0, $e);
                }
            }
        }

        /**
         * @param string $k
         * @param mixed  $v
         *
         * @return $this
         * @throws Exception
         */
        public function set($k, $v) {
            // Don't allow any changing vars in a view once execution starts
            // code/logic/assignments should not take place in a view.
            if ($this->viewLocked) {
                throw new Exception('Cannot assign a value to variable "$' . $k . '" from within a templace once its executed.');
            }

            if (in_array($k, $this->viewProtectedVars)) {
                throw new Exception('Cannot overwrite variable "$' . $k . '", it has protected access');
            }

            $this[$k] = $v;
            return $this;
        }

        /**
         * @param string $k
         *
         * @return mixed|null
         */
        public function __get($k) {
            if (isset($this[$k])) {
                return $this[$k];
            }
        }

        /**
         * @param string $k
         * @param mixed $v
         *
         * @throws Exception
         */
        public function __set($k, $v) {
            $this->set($k, $v);
        }

        /**
         * @param string $templateName
         * @param array  $argsArr
         */
        protected function inc($templateName, array $argsArr=null) {
            if ($argsArr !== null) {
                extract($argsArr);
                unset($argsArr);
            }

            include("{$this->viewApplicationPath}/views/{$templateName}." . self::VIEW_EXT);
        }

        /**
         * @param Neoform\Request\Model $request
         *
         * @return $this
         * @throws Exception
         */
        public function setRequest(Neoform\Request\Model $request) {
            $this->set('request', $request);
            return $this;
        }
    }

