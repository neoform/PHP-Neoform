<?php

    namespace Neoform\Sql\Parser;

    use Neoform\Sql;

    /**
     * An object representation of an SQL table
     */
    class Table {

        /**
         * Table name
         *
         * @var string
         */
        protected $name;

        /**
         * @var Field
         */
        protected $primaryKey;

        /**
         * Table fields
         *
         * @var Field[]
         */
        protected $fields = [];

        /**
         * Table primary key(s)
         *
         * @var Field[]
         */
        protected $primaryKeys = [];

        /**
         * Table unique keys, does not include primary keys
         *
         * @var Field[]
         */
        protected $uniqueKeys = [];

        /**
         * Table indexes (does not include PKs or UKs)
         *
         * @var Field[]
         */
        protected $indexes = [];

        /**
         * Foreign keys
         *
         * @var Field[]
         */
        protected $foreignKeys = [];

        /**
         * @param array $info
         */
        public function __construct(array $info) {
            $this->name   = $info['name'];
            $this->fields = $info['fields'];

            foreach ($info['primaryKeys'] as $primaryKey) {
                $this->primaryKeys[$primaryKey] = $this->fields[$primaryKey];
            }

            if ($this->primaryKeys && count($this->primaryKeys) === 1) {
                $this->primaryKey = current($this->primaryKeys);
            }

            foreach ($info['uniqueKeys'] as $k => $uniqueKey) {
                foreach ($uniqueKey as $field) {
                    $this->uniqueKeys[$k][$field] = $this->fields[$field];
                }
            }

            foreach ($info['indexes'] as $k => $index) {
                foreach ($index as $field) {
                    $this->indexes[$k][$field] = $this->fields[$field];
                }
            }
        }

        /**
         * @return string
         */
        public function getName() {
            return trim($this->name, '_');
        }

        /**
         * @return string
         */
        public function getNameAsClass() {
            return str_replace(' ', '\\', ucwords(str_replace('_', ' ', trim($this->name, '_'))));
        }

        /**
         * @return string
         */
        public function getNameTitleCase() {
            return str_replace(' ', '', ucwords(str_replace('_', ' ', trim($this->name, '_'))));
        }

        /**
         * @return string
         */
        public function getNameLabel() {
            return ucwords(str_replace('_', ' ', trim($this->name, '_')));
        }

        /**
         * @return string
         */
        public function getNameCamelCase() {
            $words = explode(' ', ucwords(str_replace('_', ' ', trim($this->name, '_'))));
            $words[0] = strtolower($words[0]);
            return join($words);
        }

        /**
         * @return Field[]
         */
        public function getFields() {
            return $this->fields;
        }

        /**
         * @return Field
         */
        public function getPrimaryKey() {
            return $this->primaryKey;
        }

        /**
         * @return Field[]
         */
        public function getPrimaryKeys() {
            return $this->primaryKeys;
        }

        /**
         * @return Field[]
         */
        public function getUniqueKeys() {
            return $this->uniqueKeys;
        }

        /**
         * @return Field[]
         */
        public function getIndexes() {
            return $this->indexes;
        }

        /**
         * Primary keys, unique keys, indexes, in one array
         *
         * @return Field[]
         */
        public function getAllIndexes() {
            return array_merge(
                [ $this->primaryKeys, ],
                $this->uniqueKeys,
                $this->indexes
            );
        }

        /**
         * @return Field[]
         */
        public function getForeignKeys() {
            $fks = [];
            foreach ($this->fields as $field) {
                if ($field->getReferencedField()) {
                    $fks[] = $field;
                }
            }
            return $fks;
        }

        /**
         * Any/all fields that reference fields in this table
         *
         * @return Field[]
         */
        public function getReferencingFields() {
            $refs = [];
            foreach ($this->fields as $field) {
                if ($field->getReferencedFields()) {
                    foreach ($field->getReferencedFields() as $ref) {
                        $refs[] = $ref;
                    }
                }
            }
            return $refs;
        }

        /**
         * Primary keys and unique keys, in one array
         *
         * @return Field[]
         */
        public function getAllUniqueIndexes() {
            return array_merge(
                [ $this->primaryKeys, ],
                $this->uniqueKeys
            );
        }

        /**
         * @return Field[]
         */
        public function getAllNonPkIndexes() {
            return array_merge(
                $this->uniqueKeys,
                $this->indexes
            );
        }

        /**
         * @return Field[]
         */
        public function getAllNonUniqueIndexes() {
            $indexed_fields = [];
            foreach ($this->indexes as $index) {
                $key = [];
                foreach ($index as $field) {
                    $key[] = $field->getName();
                }
                $indexed_fields[join(':', $key)] = $index;
            }
            foreach (array_merge($this->uniqueKeys, $this->primaryKeys) as $index) {
                if (count($index) > 1) {
                    $index = array_slice($index, 0, count($index) - 1);
                    $key = [];
                    foreach ($index as $field) {
                        $key[] = $field->getName();
                    }
                    $indexed_fields[join(':', $key)] = $index;
                }
            }
            return array_values($indexed_fields);
        }

        /**
         * @return Field[]
         */
        public function getAllIndexCombinations() {
            $key_combinations = [];
            foreach (array_merge([ $this->primaryKeys, ], $this->uniqueKeys, $this->indexes) as $index) {
                $previous        = [];
                $previous_fields = [];
                foreach ($index as $field) {
                    $previous[$field->getName()]                 = $field->getNameWithoutId();
                    $previous_fields[]                      = $field;
                    $key_combinations[join('_', $previous)] = $previous_fields;
                }
            }
            return $key_combinations;
        }

        /**
         * @return Field[]
         */
        public function getAllNonPkIndexCombinations() {
            $key_combinations = [];
            foreach (array_merge($this->uniqueKeys, $this->indexes) as $index) {

                // Skip useless indexes
                foreach ($index as $field) {
                    if (! $field->isFieldLookupable()) {
                        continue 2;
                    }
                }

                $previous = [];
                foreach ($index as $field) {
                    $previous[$field->getName()] = $field->getNameWithoutId();
                    $key_combinations[join('_', $previous)] = $previous;
                }
            }
            return $key_combinations;
        }

        /**
         * Does this table have a single primary key
         *
         * @return bool
         */
        public function isRecord() {
            return count($this->primaryKeys) === 1;
        }

        /**
         * Note, a table can technically be a link AS WELL as a record. Record type takes precedence though.
         *
         * @return bool
         */
        public function isLink() {
            foreach ($this->fields as $field) {
                if ($field->isLinkIndex()) {
                    return true;
                }
            }

            return false;
        }


        public function tableType() {
            if ($this->isRecord()) {
                return 'Record';
            } else if ($this->isLink()) {
                return 'Link';
            } else {
                throw new \Exception('Unknown table/entity configuration type. It doesn\'t match any design pattern in this framework.');
            }
        }

        /**
         * Determines the length of the longest field name
         *
         * @param bool $idless
         *
         * @return int
         */
        public function longestFieldLength($idless=false) {
            $len = 0;
            foreach ($this->fields as $field) {
                if ($len < strlen($idless ? $field->getNameWithoutId() : $field->getName())) {
                    $len = strlen($idless ? $field->getNameWithoutId() : $field->getName());
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
        public function longestIndexLength($idless=false) {
            $len = 0;
            foreach ($this->getAllIndexes() as $index) {
                foreach ($index as $field) {
                    if ($len < strlen($idless ? $field->getNameWithoutId() : $field->getName())) {
                        $len = strlen($idless ? $field->getNameWithoutId() : $field->getName());
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
        public function longestNonPkIndexCombinations() {
            $len = 0;
            foreach ($this->getAllNonPkIndexCombinations() as $key => $fields) {
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
        public function longestIndexCombinations() {
            $len = 0;
            foreach ($this->getAllIndexCombinations() as $key => $fields) {
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
        public function isTiny() {
            return Sql\Parser::isTableTiny($this);
        }
    }