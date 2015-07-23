<?php

    namespace Neoform\Request\Parameters;

    use Neoform\Request;

    class Get extends Request\Parameters {

        public function __construct(array $vals) {
            foreach ($vals as &$val) {
                $val = rawurldecode($val);
            }

            parent::__construct($vals);
        }
    }