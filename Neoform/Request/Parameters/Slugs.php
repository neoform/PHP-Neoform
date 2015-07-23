<?php

    namespace Neoform\Request\Parameters;

    use exception;
    use Neoform\Request;
    use Neoform\Locale;

    class Slugs extends Request\Parameters {

        /**
         * @var Parameters
         */
        protected $parameters;

        /**
         * @var string|null
         */
        protected $localeIso2;

        /**
         * @var bool
         */
        protected $localeExtracted;

        /**
         * @param string[] $slugs
         */
        public function __construct(array $slugs) {

            $parameters = [];

            foreach (array_reverse($slugs, true) as $k => $val) {
                if (preg_match('`^([^\:]+)\:([^\:]+)$`i', $val, $match)) {
                    $parameters[$match[1]] = $match[2];
                    unset($slugs[$k]);
                } else {
                    // Don't continue once we've hit non-parameter slugs
                    break;
                }
            }

            parent::__construct($slugs);

            $this->parameters = new Parameters($parameters);
        }

        /**
         * @return Parameters
         */
        public function getParameters() {
            return $this->parameters;
        }

        /**
         * Extract the locale from the URL based on available locales
         *
         * @param integer  $offset
         * @param string[] $availableLocales
         *
         * @throws exception
         */
        public function extractLocale($offset, array $availableLocales) {

            if ($this->localeExtracted) {
                throw new Exception('Locale has already been extracted');
            }

            $this->localeExtracted = true;

            if (! isset($this->vals[(int)$offset])) {
                return;
            }

            // If locale is the first slug in the path, that becomes the currently active locale
            if (in_array($this->vals[(int)$offset], $availableLocales, true)) {
                $this->localeIso2 = $this->vals[(int)$offset];
                unset($this->vals[(int)$offset]);
                $this->vals = array_values($this->vals);
            }
        }

        /**
         * @return string|null
         */
        public function getLocaleIso2() {
            return $this->localeIso2;
        }
    }