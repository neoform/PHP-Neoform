<?php

    /**
     * Generate entity files
     */
    class generate {

        protected $table;
        protected $code = '';
        protected $all  = false;

        /**
         * @param sql_parser_table $table
         * @param array            $options
         */
        public function __construct(sql_parser_table $table, array $options = []) {
            $this->table = $table;
            $this->all = (bool) in_array('--all', $options);
            $this->code();
        }

        /**
         * Get generated code
         *
         * @return string
         */
        public function get_code() {
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
         * @param array $fields
         * @param bool  $idless
         * @param bool  $lookupable
         *
         * @return int
         */
        protected function longest_length(array $fields, $idless=false, $lookupable=false) {
            $len = 0;
            foreach ($fields as $field) {

                if ($lookupable && ! $field->is_field_lookupable()) {
                    continue;
                }

                if (is_string($field)) {
                    if (strlen($field) > $len) {
                        $len = strlen($field);
                    }
                } else {
                    if (strlen($idless ? $field->name_idless : $field->name) > $len) {
                        $len = strlen($idless ? $field->name_idless : $field->name);
                    }
                }
            }
            return $len;
        }
    }