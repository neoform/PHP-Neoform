<?php

    namespace Neoform\Request\Parameters;

    use Neoform\Request;

    class Files extends Request\Parameters {

        /**
         * @param array $vals
         */
        public function __construct(array $vals) {

            /**
             * The $_FILES var is structured quite stupidly, and needs to be fixed
             */
            $files = [];
            foreach ($vals as $k1 => $v1) {
                foreach ($v1 as $k2 => $v2) {
                    $files[$k2][$k1] = $v2;
                }
            }

            $vals = $files;

            parent::__construct($vals);
        }
    }