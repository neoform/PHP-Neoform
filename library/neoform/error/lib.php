<?php

    namespace neoform\error;

    use neoform\core;

    class lib {

        public static function log(\exception $e, $level='fatal') {

            // This prevents obnoxious timezone warnings if the timezone has not been set
            date_default_timezone_set(@date_default_timezone_get());

            try {
                //trash anything that was going to be outputted
                while (ob_get_status() && ob_end_clean()) {

                }
            } catch (\exception $e) {

            }

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
        }
    }
