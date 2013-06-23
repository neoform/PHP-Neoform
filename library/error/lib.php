<?php

    class error_lib {

        public static function log(exception $e, $rethrow=true) {
            try {
                $err = $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() . "\nStack Trace:\n";
                foreach ($e->getTrace() as $trace) {
                    $args = [];
                    if (isset($trace['args']) && is_array($trace['args'])) {
                        foreach ($trace['args'] as $arg) {
                            $args[] = is_array($arg) ? '[]' : (is_object($arg) ? '[object]' : $arg);
                        }
                    }
                    $err .= (isset($trace['file']) ? $trace['file'] : 'Unknownfile') . ':' . (isset($trace['line']) ? $trace['line'] : '0') . ': ' . (isset($trace['function']) ? $trace['function'] : 'unknownFunction') . "(" . join(', ', $args) . ")\n";
                }

                core::log($err, 'fatal');

                if ($rethrow) {
                    throw new error_exception('An unexpected error occured.');
                }
            } catch (exception $e) {
                try {
                    //trash anything that was going to be outputted
                    while (ob_get_status() && ob_end_clean()) {

                    }
                } catch (Exception $e) {

                }
                die('An unexpected error occured.' . "\n");
            }
        }

        protected static function args($targs) {
            $args = [];
            if (is_array($targs)) {
                foreach ($targs as $arg) {
                    $args[] = is_array($arg) ? '[]' : (is_object($arg) ? '[object]' : $arg);
                }
            }
            return $args;
        }
    }
