<?php

    /**
     * An object representation of an SQL table
     *
     * @var string             $name               table name
     * @var sql_parser_field[] $fields             table fields
     * @var sql_parser_field[] $primary_keys       table primary key(s)
     * @var sql_parser_field[] $unique_keys        table unique keys, does not include primary keys
     * @var sql_parser_field[] $indexes            table indexes (does not include PKs or UKs)
     * @var sql_parser_field[] $all_indexes        primary keys, unique keys, indexes, in one array
     * @var sql_parser_field[] $all_unique_indexes primary keys and unique keys, in one array
     * @var sql_parser_field[] $foreign_keys       array of foreign keys
     * @var sql_parser_field[] $referencing_fields any/all fields that reference fields in this table
     */
    class sql_parser_table {

        protected $name;
        protected $primary_key;

        protected $fields       = [];
        protected $primary_keys = [];
        protected $unique_keys  = [];
        protected $indexes      = [];
        protected $foreign_keys = [];

        /**
         * @param array $info
         */
        public function __construct(array $info) {
            $this->name   = $info['name'];
            $this->fields = $info['fields'];

            foreach ($info['primary_keys'] as $primary_key) {
                $this->primary_keys[$primary_key] = $this->fields[$primary_key];
            }

            if ($this->primary_keys && count($this->primary_keys) === 1) {
                $this->primary_key = current($this->primary_keys);
            }

            foreach ($info['unique_keys'] as $k => $unique_key) {
                foreach ($unique_key as $field) {
                    $this->unique_keys[$k][$field] = $this->fields[$field];
                }
            }

            foreach ($info['indexes'] as $k => $index) {
                foreach ($index as $field) {
                    $this->indexes[$k][$field] = $this->fields[$field];
                }
            }
        }

        public function __get($k) {

            switch ((string) $k) {

                case 'name':
                    return $this->name;

                case 'fields':
                    return $this->fields;

                case 'primary_key':
                    return $this->primary_key;

                case 'primary_keys':
                    return $this->primary_keys;

                case 'unique_keys':
                    return $this->unique_keys;

                case 'indexes':
                    return $this->indexes;

                case 'all_indexes':
                    return array_merge(
                        [ $this->primary_keys, ],
                        $this->unique_keys,
                        $this->indexes
                    );

                case 'all_unique_indexes':
                    return array_merge(
                        [ $this->primary_keys, ],
                        $this->unique_keys
                    );

                case 'all_non_unique_indexes':
                    $indexed_fields = [];
                    foreach ($this->indexes as $index) {
                        $key = [];
                        foreach ($index as $field) {
                            $key[] = $field->name;
                        }
                        $indexed_fields[join(':', $key)] = $index;
                    }
                    foreach (array_merge($this->unique_keys, $this->primary_keys) as $index) {
                        if (count($index) > 1) {
                            $index = array_slice($index, 0, count($index) - 1);
                            $key = [];
                            foreach ($index as $field) {
                                $key[] = $field->name;
                            }
                            $indexed_fields[join(':', $key)] = $index;
                        }
                    }
                    return array_values($indexed_fields);

                case 'all_non_pk_indexes':
                    return array_merge(
                        $this->unique_keys,
                        $this->indexes
                    );

                case 'all_index_combinations':
                    $key_combinations = [];
                    foreach (array_merge([ $this->primary_keys, ], $this->unique_keys, $this->indexes) as $index) {
                        $previous        = [];
                        $previous_fields = [];
                        foreach ($index as $field) {
                            $previous[$field->name]                 = $field->name_idless;
                            $previous_fields[            ]          = $field;
                            $key_combinations[join('_', $previous)] = $previous_fields;
                        }
                    }
                    return $key_combinations;

                case 'all_non_pk_index_combinations':
                    $key_combinations = [];
                    foreach (array_merge($this->unique_keys, $this->indexes) as $index) {

                        // Skip useless indexes
                        foreach ($index as $field) {
                            if (! $field->is_field_lookupable()) {
                                continue 2;
                            }
                        }

                        $previous = [];
                        foreach ($index as $field) {
                            $previous[$field->name] = $field->name_idless;
                            $key_combinations[join('_', $previous)] = $previous;
                        }
                    }
                    return $key_combinations;

                case 'foreign_keys':
                    $fks = [];
                    foreach ($this->fields as $field) {
                        if ($field->referenced_field) {
                            $fks[] = $field;
                        }
                    }
                    return $fks;

                case 'referencing_fields':
                    $refs = [];
                    foreach ($this->fields as $field) {
                        if ($field->referencing_fields) {
                            foreach ($field->referencing_fields as $ref) {
                                $refs[] = $ref;
                            }
                        }
                    }
                    return $refs;

                default:
                    throw new exception('Unknown field `' . $k . '`');
            }
        }

        /**
         * Does this table have a single primary key
         *
         * @return bool
         */
        public function is_record() {
            return count($this->primary_keys) === 1;
        }

        /**
         * Note, a table can technically be a link AS WELL as a record. Record type takes precedence though.
         *
         * @return bool
         */
        public function is_link() {
            foreach ($this->fields as $field) {
                if ($field->is_link_index()) {
                    return true;
                }
            }

            return false;
        }


        public function table_type() {
            if ($this->is_record()) {
                return 'record';
            } else if ($this->is_link()) {
                return 'link';
            } else {
                throw new exception('Unknown table/entity configuration type. It doesn\'t match any design pattern in this framework.');
            }
        }

        /**
         * Determines the length of the longest field name
         *
         * @param bool $idless
         *
         * @return int
         */
        public function longest_field_length($idless=false) {
            $len = 0;
            foreach ($this->fields as $field) {
                if ($len < strlen($idless ? $field->name_idless : $field->name)) {
                    $len = strlen($idless ? $field->name_idless : $field->name);
                }
            }
            return $len;
        }

        /**
         * Determines the length of the longest index field name
         *
         * @param bool $idless
         *
         * @return int
         */
        public function longest_index_length($idless=false) {
            $len = 0;

            foreach ($this->all_indexes as $index) {
                foreach ($index as $field) {
                    if ($len < strlen($idless ? $field->name_idless : $field->name)) {
                        $len = strlen($idless ? $field->name_idless : $field->name);
                    }
                }
            }
            return $len;
        }

        /**
         * Get all combinations of indexes (except PK). if there are indexes: (id, name), (name) then the resulting combination
         * passed back will be: id, id_name, name
         *
         * @return int
         */
        public function longest_non_pk_index_combinations() {
            $len = 0;
            foreach ($this->all_non_pk_index_combinations as $key => $fields) {
                if ($len < strlen($key)) {
                    $len = strlen($key);
                }
            }
            return $len;
        }

        /**
         * Get all combinations of indexes. if there are indexes: (id, name), (name) then the resulting combination
         * passed back will be: id, id_name, name
         *
         * @return int
         */
        public function longest_index_combinations() {
            $len = 0;
            foreach ($this->all_index_combinations as $key => $fields) {
                if ($len < strlen($key)) {
                    $len = strlen($key);
                }
            }
            return $len;
        }

        /**
         * If the size of the table is limited, (such as 255 rows max) it is considered 'tiny'.
         *
         * @return bool
         */
        public function is_tiny() {
            return sql_parser::is_table_tiny($this);
        }
    }