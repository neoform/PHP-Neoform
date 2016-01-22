<?php

    namespace Neoform\Sql\Parser;

    use Neoform\Sql;

    /**
     * An object representation of a field in an SQL table
     */
    class Field {

        /**
         * @var array
         */
        protected $info;

        /**
         * @var Table
         */
        protected $table;

        /**
         * @var Field
         */
        protected $referencedField;

        /**
         * @var Field[]
         */
        protected $referencedFields = [];

        /**
         * Expects array passed to it to contain:
         *  string  name
         *  table   table
         *  string  type
         *  integer size
         *  field   referencedField
         *  array   referencedFields
         *
         * @param array $info
         */
        public function __construct(array $info) {
            $this->info = $info;
        }

        /**
         * Set the parent table
         *
         * @param Table $table
         */
        public function _setTable(Table $table) {
            $this->table = $table;
        }

        /**
         * Set the field this field references (if there is a FK on it)
         *
         * @param Field $field
         */
        public function _setReferencedField(Field $field) {
            $this->referencedField = $field;
            $field->_addReferencingField($this);
        }

        /**
         * Add to an array of fields that reference this one
         * This gets called by _setReferencedField() implicitly.
         *
         * @param Field $field
         */
        public function _addReferencingField(Field $field) {
            $this->referencedFields[] = $field;
        }

        /**
         * @return string
         */
        public function getName() {
            return $this->info['name'];
        }

        /**
         * @return string
         */
        public function getNameTitleCase() {
            return str_replace(' ', '', ucwords(str_replace('_', ' ', trim($this->info['name'], '_'))));
        }

        /**
         * @return string
         */
        public function getNameLabel() {
            return ucwords(str_replace('_', ' ', trim($this->info['name'], '_')));
        }

        /**
         * @return string
         */
        public function getNameTitleCaseWithoutId() {
            return str_replace(' ', '', ucwords(str_replace('_', ' ', trim($this->getNameWithoutId(), '_'))));
        }

        /**
         * @return string
         */
        public function getNameCamelCase() {
            $words = explode(' ', ucwords(str_replace('_',  ' ', trim($this->info['name'], '_'))));
            $words[0] = strtolower($words[0]);
            return join($words);
        }

        /**
         * @return string
         */
        public function getNameCamelCaseWithoutId() {
            $words = explode(' ', ucwords(str_replace('_',  ' ', trim($this->getNameWithoutId(), '_'))));
            $words[0] = strtolower($words[0]);
            return join($words);
        }

        /**
         * Get the name of the field without a "_id" suffix if it exists
         *
         * @return string
         */
        public function getNameWithoutId() {
            if (substr($this->info['name'], -3) === '_id') {
                return substr($this->info['name'], 0, -3);
            } else if (substr($this->info['name'], -2) === 'Id') { // covers camelCase DBs (yuck)
                return substr($this->info['name'], 0, -2);
            } else {
                return $this->info['name'];
            }
        }

        /**
         * @return Table
         */
        public function getTable() {
            return $this->table;
        }

        /**
         * The data type of this field
         *
         * @return string
         */
        public function getType() {
            return $this->info['type'];
        }

        /**
         * The size (in bytes) of this field
         *
         * @return int
         */
        public function getSize() {
            return $this->info['size'];
        }

        /**
         * ENUM values, or decimal length, or varchar length
         *
         * @return int
         */
        public function getVarInfo() {
            return $this->info['size'];
        }

        /**
         * The field that this field references
         *
         * @return Field
         */
        public function getReferencedField() {
            return $this->referencedField;
        }

        /**
         * All fields that reference this field
         *
         * @return Field[]
         */
        public function getReferencedFields() {
            return $this->referencedFields;
        }

        /**
         * PHP type (eg, int, string)
         *
         * @return string
         */
        public function getCasting() {
            if ($this->info['casting'] === 'decimal') {
                return 'float'; // php no support decimal type
            }
            return $this->info['casting'];
        }

        /**
         * PHP type (eg, int, string, date, datetime, bool)
         *
         * @return string
         */
        public function getCastingExtended() {
            return $this->info['castingExtended'];
        }

        /**
         * If this is a boolean value (because it's an ENUM and has an on/off type value)
         * what is the value that corresponds with "true"
         *
         * @return string
         */
        public function getBoolTrueValue() {
            return $this->info['boolTrue'];
        }

        /**
         * @return string
         */
        public function getPdoCasting() {
            if ($this->isBinary()) {
                return 'binary';
            }
            return $this->info['casting'];
        }

        /**
         * @return array
         */
        public function getInfo() {
            return $this->info;
        }
        /**
         * Is this field unsigned
         *
         * @return bool
         */
        public function isUnsigned() {
            return (bool) $this->info['unsigned'];
        }

        /**
         * Does this field auto increment
         *
         * @return bool
         */
        public function isAutoIncrement() {
            return (bool) $this->info['autoincrement'];
        }

        /**
         * Does this field auto increment
         *
         * @return bool
         */
        public function isAutoGeneratedOnInsert() {
            return (bool) $this->info['autoGeneratedInsert'];
        }

        /**
         * Does this field allow null
         *
         * @return bool
         */
        public function allowsNull() {
            return (bool) $this->info['allowNull'];
        }

        /**
         * Is this field part of a primary key
         *
         * @return bool
         */
        public function isPrimaryKey() {
            foreach ($this->table->getPrimaryKeys() as $key) {
                if ($key->getName() === $this->info['name']) {
                    return true;
                }
            }

            return false;
        }

        /**
         * Is this field part of a unique key
         *
         * @return bool
         */
        public function isUniqueKey() {
            foreach ($this->table->getUniqueKeys() as $uk) {
                foreach ($uk as $key) {
                    if ($key->getName() === $this->info['name']) {
                        return true;
                    }
                }
            }

            return false;
        }

        /**
         * Checks if this field is unique. Which means it's a single key unique, not a composite.
         *
         * @return bool
         */
        public function isUnique() {
            if (count($this->table->getPrimaryKeys()) === 1) {
                foreach ($this->table->getPrimaryKeys() as $key) {
                    if ($key->getName() === $this->info['name']) {
                        return true;
                    }
                }
            }

            foreach ($this->table->getUniqueKeys() as $uk) {
                if (count($uk) === 1) {
                    foreach ($uk as $key) {
                        if ($key->getName() === $this->info['name']) {
                            return true;
                        }
                    }
                }
            }

            return false;
        }

        /**
         * Another table references this field
         *
         * @return bool
         */
        public function isReferenced() {
            return (bool) count($this->referencedFields);
        }

        /**
         * Does this field reference another table
         *
         * @return bool
         */
        public function isReference() {
            return (bool) $this->getReferencedField();
        }

        /**
         * Does this field allow binary data
         *
         * @return bool
         */
        public function isBinary() {
            return (bool) $this->info['binary'];
        }

        /**
         * Can this field be useful for an equality lookup? (datetimes are an example of a field that is not useful)
         *
         * @return bool
         */
        public function isFieldLookupable() {
            return Sql\Parser::isFieldLookupable($this);
        }

        /**
         * Is this field part of an index
         *
         * @return bool
         */
        public function isIndexed() {
            if ($this->isPrimaryKey()) {
                return true;
            }

            foreach ($this->table->getIndexes() as $index) {
                foreach ($index as $key) {
                    if ($key->getName() === $this->info['name']) {
                        return true;
                    }
                }
            }

            return false;
        }

        /**
         * Is this field indexed by itself
         *
         * @return bool
         */
        public function isSingleKeyIndex() {
            if (count($this->table->getPrimaryKeys()) === 1) {
                if (current($pk)->getName() === $this->info['name']) {
                    return true;
                }
            }

            foreach ($this->table->getIndexes() as $index) {
                if (count($index) === 1) {
                    if (current($index)->getName() === $this->info['name']) {
                        return true;
                    }
                }
            }

            return false;
        }

        /**
         * Is this field part of a link (2-key index where both keys reference other tables) index?
         *
         * @return bool
         */
        public function isLinkIndex() {
            foreach ($this->table->getAllUniqueIndexes() as $uk) {
                if (count($uk) === 2) {
                    $found = false;
                    foreach ($uk as $field) {
                        if ($field->getName() === $field->info['name']) {
                            $found = true;
                        }
                    }

                    if ($found) {
                        // check if they both link to another table
                        $uk = array_values($uk);
                        if ($uk[0]->getReferencedField() && $uk[1]->getReferencedField()) {
                            return true;
                        }
                    }
                }
            }

            return false;
        }

        /**
         * If this field part of a link (2-key index where both keys reference other tables) index, return the other field
         * in the link.
         *
         * @return field|null
         */
        public function getOtherLinkIndexField() {
            foreach ($this->table->getAllUniqueIndexes() as $uk) {
                if (count($uk) === 2) {
                    $found = false;
                    foreach ($uk as $field) {
                        if ($field->getName() === $field->info['name']) {
                            $found = true;
                        }
                    }

                    if ($found) {
                        // check if they both link to another table
                        $uk = array_values($uk);
                        if ($uk[0]->getReferencedField() && $uk[1]->getReferencedField()) {
                            if ($uk[0] === $this) {
                                return $uk[1];
                            } else {
                                return $uk[0];
                            }
                        }
                    }
                }
            }
        }
    }