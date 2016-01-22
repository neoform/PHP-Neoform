<?php

    namespace Neoform\Sql\Parser\Driver;

    use Neoform\Sql\Parser\Field;
    use Neoform\Sql\Parser\Table;
    use Neoform\Sql\Parser\Driver;
    use Neoform\Sql;

    class Mysql extends Driver {

        public function __construct() {
            $tableNames = $this->getAllTables();

            $tables_info = [];
            foreach ($tableNames as $tableName) {
                $table_info = $this->parse($this->getTableDefinition($tableName));
                $tables_info[$table_info['name']] = $table_info;
            }

            foreach ($tables_info as $tableName => $table_info) {
                $info = [
                    'name'        => $tableName,
                    'fields'      => [],
                    'primaryKeys' => [],
                    'uniqueKeys'  => [],
                    'indexes'     => [],
                ];

                // Fields
                foreach ($table_info['fields'] as $field) {
                    $info['fields'][$field['name']] = new Field($field);
                }

                // Primary Keys
                foreach ($table_info['primaryKeys'] as $field_name) {
                    $info['primaryKeys'][$field_name] = $info['fields'][$field_name]->getName();
                }

                // Unique Keys
                foreach ($table_info['uniqueKeys'] as $k => $key) {
                    foreach ($key as $field_name) {
                        $info['uniqueKeys'][$k][$field_name] = $info['fields'][$field_name]->getName();
                    }
                }

                // Indexes
                foreach ($table_info['indexes'] as $k => $key) {
                    foreach ($key as $field_name) {
                        $info['indexes'][$k][$field_name] = $info['fields'][$field_name]->getName();
                    }
                }

                $this->tables[$tableName] = new Table($info);
            }

            // add each table to each of its fields, so a given field can know who it belongs to
            foreach ($this->tables as $table) {
                foreach ($table->getFields() as $field) {
                    $field->_setTable($table);
                }
            }

            // Foreign keys must be done after the table has been parsed (we reference the tables to each other)
            foreach ($tables_info as $tableName => $table_info) {

                $table = $this->tables[$tableName];

                foreach ($table_info['foreignKeys'] as $fk) {

                    if (isset($this->tables[$fk['parentTable']])) {
                        $table->getFields()[$fk['field']]->_setReferencedField($this->tables[$fk['parentTable']]->getFields()[$fk['parentField']]);
                    } else {
                        throw new \Exception("The parent table `{$fk['parentTable']}` was not identified during parsing, is it in this database/schema?");
                    }
                }
            }
        }

        protected function getAllTables() {
            $sql = Sql::instance()->prepare("
                SHOW FULL TABLES WHERE Table_Type = 'BASE TABLE'
            ");
            $sql->execute();
            $tables = [];
            foreach ($sql->fetchAll() as $table) {
                $tables[] = current($table);
            }
            return array_values($tables);
        }

        protected function getTableDefinition($tableName) {
            $sql = Sql::instance()->prepare("
                SHOW CREATE TABLE `{$tableName}`
            ");
            $sql->execute();
            $describe = $sql->fetch();

            $create_table = $create_view = null;
            foreach ($describe as $k => $v) {
                if (strtolower($k) === 'create table') {
                    $create_table = $v;
                } else if (strtolower($k) === 'create view') {
                    $create_view = $v;
                }
            }

            if ($create_table) {
                return $create_table;
            } else if ($create_view) {
                // views are of no interest for this...
            } else {
                throw new \Exception("Could not find table definition of `{$tableName}`");
            }
        }

        protected function parse($table_definition) {

            if (! preg_match('/^CREATE\s+TABLE\s+`([a-z0-9\_]*)`\s*\(\s*(.*)$/is', $table_definition, $match)) {
                throw new \Exception("Could not parse table definition");
            }

            $info = [
                'name'        => trim(strtolower($match[1])),
                'fields'      => [],
                'primaryKeys' => [],
                'uniqueKeys'  => [],
                'indexes'     => [],
                'foreignKeys' => [],
            ];

            $rows = preg_split('`\s*\n\s*`is', trim($match[2]), -1, PREG_SPLIT_NO_EMPTY);

            foreach ($rows as $row) {
                if (substr($row, 0, 1) === '`') {
                    $info['fields'][] = $this->field($row);
                } else if (substr($row, 0, 11) === 'PRIMARY KEY') {
                    $info['primaryKeys'] = $this->primaryKey($row);
                } else if (substr($row, 0, 10) === 'UNIQUE KEY') {
                    $info['uniqueKeys'][] = $this->uniqueKey($row);
                } else if (substr($row, 0, 3) === 'KEY') {
                    $info['indexes'][] = $this->key($row);
                } else if (substr($row, 0, 10) === 'CONSTRAINT') {
                    $info['foreignKeys'][] = $this->foreignKey($row);
                }
            }

            return $info;
        }

        //`hash_method` tinyint(3) unsigned NOT NULL,
        protected function field($fieldInfo) {
            if (preg_match('/^`([a-z0-9\_]+)`\s*([a-z0-9]+)(?:\(([^\)]*)\))?.*$/i', $fieldInfo, $match)) {
                return [
                    'name'                => $match[1],
                    'type'                => strtolower($match[2]),
                    'size'                => isset($match[3]) ? strtolower($match[3]) : null,
                    'unsigned'            => strpos($fieldInfo, 'unsigned') !== false,
                    'autoincrement'       => strpos($fieldInfo, 'AUTO_INCREMENT') !== false,
                    'autoGeneratedInsert' => self::autoGeneratedOnInsert(strtolower($match[2])) || strpos($fieldInfo, 'AUTO_INCREMENT') !== false,
                    'allowNull'           => strpos($fieldInfo, 'NOT NULL') === false,
                    'casting'             => self::fieldCasting(strtolower($match[2])),
                    'castingExtended'     => self::fieldCastingExtended(strtolower($match[2]), isset($match[3]) ? strtolower($match[3]) : null),
                    'binary'              => self::fieldIsBinary(strtolower($match[2])),
                    'boolTrue'            => self::booleanTrueValue(isset($match[3]) ? strtolower($match[3]) : null),
                ];
            }

            throw new \Exception("Could not parse table definition, unexpected line \"{$fieldInfo}\".");
        }

        //PRIMARY KEY (`id`),
        protected function primaryKey($fieldInfo) {
            if (preg_match('/^PRIMARY\s+KEY\s+\(([a-z0-9\_,`\s]+)\).*$/i', $fieldInfo, $match)) {
                if (preg_match_all('/`([a-z0-9\_]+)`/i', strtolower($match[1]), $keys)) {
                    return $keys[1];
                }
            }

            throw new \Exception("Could not parse table definition, unexpected line \"{$fieldInfo}\".");
        }

        //UNIQUE KEY `key_email` (`site_id`,`email`),
        protected function uniqueKey($fieldInfo) {
            if (preg_match('/^UNIQUE\s+KEY\s+`([a-z0-9\_]+)`\s*\(([a-z0-9\_,`\s]*)\).*$/i', $fieldInfo, $match)) {
                if (preg_match_all('/`([a-z0-9\_]+)`/i', strtolower($match[2]), $keys)) {
                    return $keys[1];
                }
            }

            throw new \Exception("Could not parse table definition, unexpected line \"" . $fieldInfo . "\".");
        }

        //KEY `hash_method` (`hash_method`),
        protected function key($fieldInfo) {
            if (preg_match('/^KEY\s+`([a-z0-9\_]+)`\s*\(([a-z0-9\_,`\s\(\)]*)\).*$/i', $fieldInfo, $match)) {
                if (preg_match_all('/`([a-z0-9\_]+)`/i', strtolower($match[2]), $keys)) {
                    return $keys[1];
                }
            }

            throw new \Exception("Could not parse table definition, unexpected line \"" . $fieldInfo . "\".");
        }

        //CONSTRAINT `auth_user_ibfk_hash` FOREIGN KEY (`hash_method`) REFERENCES `auth_user_map_hashmethod` (`id`) ON UPDATE CASCADE,
        protected function foreignKey($fieldInfo) {
            if (preg_match('/^CONSTRAINT\s+`([a-z0-9\_]+)`\s*FOREIGN\s+KEY\s+\(`([a-z0-9\_]+)`\)\s+REFERENCES\s+`([a-z0-9\_]+)`\s+\(`([a-z0-9\_]+)`\).*?$/i', $fieldInfo, $match)) {
                return [
                    'name'        => strtolower($match[1]),
                    'field'       => strtolower($match[2]),
                    'parentTable' => strtolower($match[3]),
                    'parentField' => strtolower($match[4]),
                ];
            }

            throw new \Exception("Could not parse table definition, unexpected line \"" . $fieldInfo . "\". Note: composite Foreign Keys are not supported.");
        }

        /**
         * The extended type for a field in a database (eg, BIGINT -> int, VARCHAR -> string)
         *
         * @param string      $type
         * @param string|null $details details
         *
         * @return string
         */
        protected static function fieldCastingExtended($type, $details=null) {
            switch (trim(strtolower($type))) {
                case 'bit':
                case 'tinyint':
                case 'smallint':
                case 'mediumint':
                case 'int':
                case 'integer':
                case 'bigint':
                    return 'int';

                case 'real':
                case 'double':
                case 'float':
                case 'decimal':
                case 'numeric':
                    return 'float';

                case 'date':
                    return 'date';

                case 'datetime':
                case 'timestamp':
                    return 'datetime';

                case 'binary':
                case 'varbinary':
                    return 'binary';

                case 'enum': // break missing intentionally
                    if (array_key_exists(strtolower($details), self::$enum_values)) {
                        return 'bool';
                    }

                default:
                    return 'string';
            }
        }

        /**
         * The primitive type for a field in a database (eg, BIGINT -> int, VARCHAR -> string)
         *
         * @param string      $type
         *
         * @return string
         */
        protected static function fieldCasting($type) {
            switch (trim(strtolower($type))) {
                case 'bit':
                case 'tinyint':
                case 'smallint':
                case 'mediumint':
                case 'int':
                case 'integer':
                case 'bigint':
                    return 'int';

                case 'real':
                case 'double':
                case 'float':
                case 'numeric':
                    return 'float';

                case 'decimal':
                    return 'decimal';

                case 'binary':
                case 'varbinary':
                    return 'binary';

                default:
                    return 'string';
            }
        }

        /**
         * Returns true if the field autogenerates its content on insert
         *
         * @param $type
         *
         * @return bool
         */
        public static function autoGeneratedOnInsert($type) {
            switch (trim(strtolower($type))) {
                case 'timestamp':
                    return true;
            }

            return false;
        }

        public function booleanTrueValue($details) {
            if (array_key_exists(strtolower($details), self::$enum_values)) {
                return self::$enum_values[strtolower($details)];
            }
        }

        /**
         * Does this field allow binary data
         *
         * @param string $type
         *
         * @return bool
         */
        protected static function fieldIsBinary($type) {
            return in_array(
                trim(strtolower($type)),
                [
                    'binary',
                    'varbinary',
                    'blob',
                    'tinyblob',
                    'mediumblob',
                    'longblob',
                ]
            );
        }

        /**
         * Identify driver specific validation for this field
         *
         * @param field $field
         *
         * @return string
         */
        public static function apiTypeValidation(Field $field) {
            //max sizes
            switch ((string) $field->getType()) {
                case 'bit':
                    return "->requireDigit(0, 1)";

                case 'tinyint':
                    return $field->isUnsigned() ? "->requireDigit(0, 255)" : "->requireDigit(-128, 127)";

                case 'smallint':
                    return $field->isUnsigned() ? "->requireDigit(0, 65535)" : "->requireDigit(-32768, 32767)";

                case 'mediumint':
                    return $field->isUnsigned() ? "->requireDigit(0, 16777215)" : "->requireDigit(-8388608, 8388607)";

                case 'int':
                case 'integer':
                    return $field->isUnsigned() ? "->requireDigit(0, 4294967295)" : "->requireDigit(-2147483648, 2147483647)";

                case 'bigint':
                    return $field->isUnsigned() ? "->requireDigit(0, 9223372036854775807)" : "->requireDigit(-9223372036854775808, 9223372036854775807)";

                case 'varchar':
                case 'char':
                case 'varbinary':
                case 'binary':
                    return "->requireLength(1, {$field->getSize()})";

                case 'tinytext':
                case 'tinyblob':
                    return "->requireLength(1, 255)";

                case 'text':
                case 'blob':
                    return "->requireLength(1, 65535)";

                case 'mediumtext':
                case 'mediumblob':
                    return "->requireLength(1, 16777215)";

                case 'longtext':
                case 'longblob':
                    return "->requireLength(1, 4294967295)";

                case 'timestamp':
                case 'datetime':
                    return "->isDateTime()";

                case 'date':
                    return "->isDate()";

                case 'enum':
                    return "->isIn([{$field->getVarInfo()}])";
            }
        }

        /**
         * Does this table have a primary key that allows only a small number of rows?
         *
         * @param table $table
         *
         * @return bool
         */
        public static function isTableTiny(Table $table) {
            if ($table->getPrimaryKey()) {
                switch ((string) $table->getPrimaryKey()->getType()) {
                    case 'tinyint':
                        return true;
                }
            }

            return false;
        }

        /**
         * @param field $field
         *
         * @return bool
         */
        public static function isFieldLookupable(Field $field) {
            switch ((string) $field->getType()) {
                case 'timestamp':
                case 'datetime':
                    return false;

                default:
                    return true;
            }
        }
    }