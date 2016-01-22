<?php

    namespace Neoform\Entity;

    use Neoform\Sql\Parser\Table;
    use Neoform\Sql\Parser\Field;

    /**
     * Generate entity files
     */
    abstract class Generate {

        /**
         * @var Table
         */
        protected $table;

        /**
         * @var string
         */
        protected $namespace;

        /**
         * @var string
         */
        protected $code = '';

        /**
         * Generate code
         */
        abstract public function code();

        /**
         * @param string $namespace
         * @param Table  $table
         * @param array  $options
         */
        public function __construct($namespace, Table $table, array $options = []) {
            $this->namespace = $namespace;
            $this->table     = $table;
            $this->code();
        }

        /**
         * Get generated code
         *
         * @return string
         */
        public function getCode() {
            return $this->code;
        }

        /**
         * Takes a bunch of names and turns: [a, b, c] and turns it into: 'a, b and c'
         *
         * @param array $arr
         *
         * @return string
         */
        protected function ander(array $arr) {
            $tail = count($arr) > 1 ? ' and ' . array_pop($arr) : '';
            return join(', ', $arr) . $tail;
        }

        /**
         * Returns the length of the longest field name
         *
         * @param Field[] $fields
         * @param bool    $idless
         * @param bool    $lookupable
         *
         * @return int
         */
        protected function longestLength(array $fields, $idless=false, $lookupable=false) {
            $len = 0;
            foreach ($fields as $field) {

                if ($lookupable && ! $field->isFieldLookupable()) {
                    continue;
                }

                if (is_string($field)) {
                    if (strlen($field) > $len) {
                        $len = strlen($field);
                    }
                } else {
                    if (strlen($idless ? $field->getNameWithoutId() : $field->getName()) > $len) {
                        $len = strlen($idless ? $field->getNameWithoutId() : $field->getName());
                    }
                }
            }
            return $len;
        }

        /**
         * Returns the length of the longest field name
         *
         * @param Field[] $fields
         * @param bool    $idless
         * @param bool    $lookupable
         *
         * @return int
         */
        protected function longestLengthCamelCase(array $fields, $idless=false, $lookupable=false) {
            $len = 0;
            foreach ($fields as $field) {

                if ($lookupable && ! $field->isFieldLookupable()) {
                    continue;
                }

                if (is_string($field)) {
                    if (strlen($field) > $len) {
                        $len = strlen($field);
                    }
                } else {
                    if (strlen($idless ? $field->getNameCamelCaseWithoutId() : $field->getNameCamelCase()) > $len) {
                        $len = strlen($idless ? $field->getNameCamelCaseWithoutId() : $field->getNameCamelCase());
                    }
                }
            }
            return $len;
        }
    }