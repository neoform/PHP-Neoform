<?php

    namespace Neoform\Test;

    use Neoform\Cli;

    abstract class Model extends Cli\Model {

        /**
         * @var array
         */
        protected $tests = [];

        /**
         * Destruct
         */
        public function __destruct() {

            echo "\nTest Results:\n";
            echo "Passed Tests:      {$this->passedTestCount()}/{$this->testCount()}\n";
            echo "Passed Assertions: {$this->passedAssertionCount()}/{$this->assertionCount()}\n";

            if ($this->failedTestCount()) {
                echo "\n" . self::color_text('TESTS CONTAINED FAILURES', 'red', true, true) . "\n";
            } else {
                echo "\n" . self::color_text('ALL TESTS PASSED', 'green', true, true) . "\n";
            }

            echo "\n";
        }

        /**
         * Run a test/assertion
         *
         * @param bool $result
         */
        protected function assertTrue($result) {
            $this->assert($result, $result === true ? 'True' : 'False');
        }

        /**
         * Run a test/assertion
         *
         * @param bool $result
         */
        protected function assertFalse($result) {
            $this->assert(! $result, (bool) $result ? 'True' : 'False');
        }

        /**
         * Run a test/assertion
         *
         * @param mixed $value
         * @param mixed $expected
         */
        protected function assertEquals($value, $expected) {
            $this->assert($value === $expected, "Test: {$value} === {$expected}");
        }

        /**
         * Run a test/assertion
         *
         * @param mixed $value
         */
        protected function assertNull($value) {
            $this->assert($value === null, "Test: Null");
        }

        /**
         * @param string $result
         * @param string $message
         */
        private function assert($result, $message) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
            $testName = trim($backtrace[2]['function']);
            $testLine = trim($backtrace[1]['line']);
            preg_match_all('`((?:^|[A-Z])[a-z]+)`', $testName, $matches);
            $testName = ucwords(join(' ', $matches[1]));

            if (! isset($this->tests[$testName])) {
                $this->tests[$testName] = [];
            }

            $this->tests[$testName][$testLine] = (bool) $result;

            echo $result ? self::color_text('PASSED:', 'green') : self::color_text('FAILED:', 'red');
            echo "\t{$testName} [{$testLine}] Test: {$message}";
            echo "\n";
        }

        /**
         * @return int
         */
        private function testCount() {
            return count($this->tests);
        }

        /**
         * @return int
         */
        private function passedTestCount() {
            return $this->testCount() - $this->failedTestCount();
        }

        /**
         * @return int
         */
        private function failedTestCount() {
            $i = 0;
            foreach ($this->tests as $results) {
                foreach ($results as $result) {
                    if (! $result) {
                        $i++;
                        break;
                    }
                }
            }

            return $i;
        }

        /**
         * @return int
         */
        private function assertionCount() {
            $i = 0;
            foreach ($this->tests as $results) {
                foreach ($results as $result) {
                    $i += count($result);
                }
            }

            return $i;
        }

        /**
         * @return int
         */
        private function passedAssertionCount() {
            $i = 0;
            foreach ($this->tests as $results) {
                foreach ($results as $result) {
                    if ($result) {
                        $i++;
                    }
                }
            }

            return $i;
        }

        /**
         * @return int
         */
        private function failedAssertionCount() {
            $i = 0;
            foreach ($this->tests as $results) {
                foreach ($results as $result) {
                    if (! $result) {
                        $i++;
                    }
                }
            }

            return $i;
        }
    }