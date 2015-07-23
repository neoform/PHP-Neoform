<?php

    namespace Neoform\Request\Parameters;

    use Neoform\Request;

    class Parameters extends Request\Parameters {

        public function __construct(array $vals) {

            // Search the parameter values for | or , which defines the values as an array of values
            // eg, URL: /controller/foo:bar,bizz/bar:foo|fizz
            //  RESULT: [ 'foo' => [ 'bar', 'bizz' ], 'bar' => [ 'foo', 'fizz' ], ]
            foreach ($vals as $k => &$v) {
                if (strpos($v, '|') !== false || strpos($v, ',') !== false) {
                    $v = preg_split('`[|,]`', $v);
                }
            }

            parent::__construct($vals);
        }
    }