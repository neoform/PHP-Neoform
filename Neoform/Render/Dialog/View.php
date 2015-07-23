<?php

    namespace Neoform\Render\Dialog;

    use Neoform;

    class View {

        /**
         * @var array
         */
        protected $vars;

        /**
         * @var string
         */
        protected $dialogViewPath;

        const VIEW_EXT = 'phtml';

        /**
         * @param $path
         * @param array $vars
         *
         * @throws Neoform\Render\Exception
         */
        public function __construct($path, array $vars) {
            $this->vars           = $vars;
            $this->vars['locale'] = Neoform\Locale::instance();

            $this->dialogViewPath = Neoform\Core::get()->getApplicationPath() . "/dialogs/{$path}." . self::VIEW_EXT;
        }

        /**
         * @return string
         */
        public function __toString() {
            return (string) $this->render();
        }

        /**
         * @return string
         * @throws Neoform\Render\Exception
         */
        public function render() {
            ob_start();

            if ($this->vars !== null) {
                extract($this->vars);
            }

            require($this->dialogViewPath);

            try {
                return ob_get_clean() ?: null;
            } catch (\Exception $e) {
                ob_end_clean();
                throw new Neoform\Render\Exception('Output buffer error occurred', 0, $e);
            }
        }

        /**
         * @param string $k
         *
         * @return mixed
         */
        public function __get($k) {
            if (isset($this->vars[$k])) {
                return $this->vars[$k];
            }
        }
    }

