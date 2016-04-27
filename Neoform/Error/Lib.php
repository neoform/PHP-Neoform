<?php

    namespace Neoform\Error;

    use Exception;
    use Neoform\Core;

    class Lib {

        /**
         * @param Exception $e
         */
        public static function log(Exception $e) {

            // This prevents obnoxious timezone warnings if the timezone has not been set
            date_default_timezone_set(@date_default_timezone_get());

            try {
                //trash anything that was going to be outputted
                while (ob_get_status() && ob_end_clean()) {

                }
            } catch (Exception $e) {

            }

            $err = "{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}\nStack Trace:\n";
            foreach ($e->getTrace() as $trace) {
                $args = [];
                if (isset($trace['args']) && is_array($trace['args'])) {
                    foreach ($trace['args'] as $arg) {
                        $args[] = is_array($arg) ? '[]' : (is_object($arg) ? '[object]' : $arg);
                    }
                }

                $err .= ' ↪  ' . (isset($trace['file']) ? $trace['file'] : 'Unknownfile') . ':' . (isset($trace['line']) ?
                        $trace['line'] : '0') . ': ' . (isset($trace['function']) ? $trace['function'] :
                        'unknownFunction') . "(" . join(', ', $args) . ")\n";
            }

            $depth = '';

            // Grab any previous errors and apply them
            while ($e = $e->getPrevious()) {
                $depth .= '    ';

                $err .= "Previous exception:\n";
                $err .= "{$depth}{$e->getMessage()} in {$e->getFile()}:{$e->getLine()}\n{$depth}Stack Trace:\n";

                foreach ($e->getTrace() as $trace) {
                    $args = [];
                    if (isset($trace['args']) && is_array($trace['args'])) {
                        foreach ($trace['args'] as $arg) {
                            $args[] = is_array($arg) ? '[]' : (is_object($arg) ? '[object]' : $arg);
                        }
                    }
                    $err .= "{$depth} ↪  " . (isset($trace['file']) ? $trace['file'] : 'Unknownfile') . ':' . (isset($trace['line']) ?
                            $trace['line'] : '0') . ': ' . (isset($trace['function']) ? $trace['function'] :
                            'unknownFunction') . "(" . join(', ', $args) . ")\n";
                }
            }

            Core::log($err, 'fatal');
        }
    }
