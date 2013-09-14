<?php

    namespace neoform\test;

    use neoform\cli;

    abstract class model extends cli\model {

        protected $tests    = 0;
        protected $failures = [];

        public function __destruct() {

            echo "Tests:    " . $this->tests . "\n";

            if ($this->failures) {
                echo 'Failures: ' . count($this->failures) . "\n";
                foreach ($this->failures as $failure) {
                    echo "Failed on line " . $failure . "\n";
                }
                echo self::color_text('TESTS CONTAINED FAILURES', 'red', true, true) . "\n";
            } else {
                echo "Failures: " . count($this->failures) . "\n";
                echo self::color_text('ALL TESTS PASSED', 'green', true, true) . "\n";
            }
        }

        protected function assert_true($result, $test) {
            $this->tests++;
            if (! (bool) $result) {
                $this->failures[] = $test;
            }
        }
    }