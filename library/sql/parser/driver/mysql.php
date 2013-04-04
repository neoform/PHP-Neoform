<?php

    class sql_parser_driver_mysql extends sql_parser_driver {

        public function __construct() {
            $table_names = $this->get_all_tables();

            $tables_info = [];
            foreach ($table_names as $table_name) {
                $table_info = $this->parse($this->get_table_definition($table_name));
                $tables_info[$table_info['name']] = $table_info;
            }

            foreach ($tables_info as $table_name => $table_info) {
                $info = [
                    'name'         => $table_name,
                    'fields'       => [],
                    'primary_keys' => [],
                    'unique_keys'  => [],
                    'indexes'      => [],
                ];

                // Fields
                foreach ($table_info['fields'] as $field) {
                    $info['fields'][$field['name']] = new sql_parser_field($field);
                }

                // Primary Keys
                foreach ($table_info['primary_keys'] as $field_name) {
                    $info['primary_keys'][$field_name] = $info['fields'][$field_name]->name;
                }

                // Unique Keys
                foreach ($table_info['unique_keys'] as $k => $key) {
                    foreach ($key as $field_name) {
                        $info['unique_keys'][$k][$field_name] = $info['fields'][$field_name]->name;
                    }
                }

                // Indexes
                foreach ($table_info['indexes'] as $k => $key) {
                    foreach ($key as $field_name) {
                        $info['indexes'][$k][$field_name] = $info['fields'][$field_name]->name;
                    }
                }

                $this->tables[$table_name] = new sql_parser_table($info);
            }

            // add each table to each of its fields, so a given field can know who it belongs to
            foreach ($this->tables as $table) {
                foreach ($table->fields as $field) {
                    $field->_set_table($table);
                }
            }

            // Foreign keys must be done after the table has been parsed (we reference the tables to each other)
            foreach ($tables_info as $table_name => $table_info) {

                $table = $this->tables[$table_name];

                foreach ($table_info['foreign_keys'] as $fk) {

                    if (isset($this->tables[$fk['parent_table']])) {
                        $table->fields[$fk['field']]->_set_referenced_field($this->tables[$fk['parent_table']]->fields[$fk['parent_field']]);
                    } else {
                        throw new exception('The parent table `' . $fk['parent_table'] . '` was not identified during parsing, is it in this database/schema?');
                    }
                }
            }
        }

        protected function get_all_tables() {
            $sql = core::sql()->prepare("
				SHOW TABLES
			");
            $sql->execute();
            $tables = [];
            foreach ($sql->fetchAll() as $table) {
                $tables[] = current($table);
            }
            return array_values($tables);
        }

        protected function get_table_definition($table_name) {
            $sql = core::sql()->prepare("
				SHOW CREATE TABLE `" . $table_name . "`
			");
            $sql->execute();
            $describe = $sql->fetch();
            if (isset($describe['create table'])) {
                return $describe['create table'];
            } else if (isset($describe['create view'])) {
                // views are of no interest for this...
            } else {
                throw new exception("Could not find table definition of `" . $table_name . "`");
            }
        }

        protected function parse($table_definition) {

            if (! preg_match('/^CREATE\s+TABLE\s+`([a-z0-9\_]*)`\s*\(\s*(.*)$/is', $table_definition, $match)) {
                throw new exception("Could not parse table definition");
            }

            $info = [
                'name'         => trim(strtolower($match[1])),
                'fields'       => [],
                'primary_keys' => [],
                'unique_keys'  => [],
                'indexes'      => [],
                'foreign_keys' => [],
            ];

            $rows = preg_split('`\s*\n\s*`is', trim($match[2]), -1, PREG_SPLIT_NO_EMPTY);

            foreach ($rows as $row) {
                if (substr($row, 0, 1) === '`') {
                    $info['fields'][] = $this->field($row);
                } else if (substr($row, 0, 11) === 'PRIMARY KEY') {
                    $info['primary_keys'] = $this->primary_key($row);
                } else if (substr($row, 0, 10) === 'UNIQUE KEY') {
                    $info['unique_keys'][] = $this->unique_key($row);
                } else if (substr($row, 0, 3) === 'KEY') {
                    $info['indexes'][] = $this->key($row);
                } else if (substr($row, 0, 10) === 'CONSTRAINT') {
                    $info['foreign_keys'][] = $this->foreign_key($row);
                }
            }

            return $info;
        }

        //`hash_method` tinyint(3) unsigned NOT NULL,
        protected function field($field_info) {
            if (preg_match('/^`([a-z0-9\_]+)`\s*([a-z0-9]+)(?:\(([^\)]*)\))?.*$/i', $field_info, $match)) {
                return [
                    'name'                 => $match[1],
                    'type'                 => strtolower($match[2]),
                    'size'                 => isset($match[3]) ? strtolower($match[3]) : null,
                    'unsigned'             => strpos($field_info, 'unsigned') !== false,
                    'autoincrement'        => strpos($field_info, 'AUTO_INCREMENT') !== false,
                    'autogenerated_insert' => self::autogenerated_on_insert(strtolower($match[2])) || strpos($field_info, 'AUTO_INCREMENT') !== false,
                    'allow_null'           => strpos($field_info, 'NOT NULL') === false,
                    'casting'              => self::field_casting(strtolower($match[2]), isset($match[3]) ? strtolower($match[3]) : null),
                    'casting_extended'     => self::field_casting_extended(strtolower($match[2]), isset($match[3]) ? strtolower($match[3]) : null),
                    'binary'               => self::field_is_binary(strtolower($match[2])),
                    'bool_true'            => self::boolean_true_value(isset($match[3]) ? strtolower($match[3]) : null),
                ];
            }

            throw new exception("Could not parse table definition, unexpected line \"" . $field_info . "\".");
        }

        //PRIMARY KEY (`id`),
        protected function primary_key($field_info) {
            if (preg_match('/^PRIMARY\s+KEY\s+\(([a-z0-9\_,`\s]+)\).*$/i', $field_info, $match)) {
                if (preg_match_all('/`([a-z0-9\_]+)`/i', strtolower($match[1]), $keys)) {
                    return $keys[1];
                }
            }

            throw new exception("Could not parse table definition, unexpected line \"" . $field_info . "\".");
        }

        //UNIQUE KEY `key_email` (`site_id`,`email`),
        protected function unique_key($field_info) {
            if (preg_match('/^UNIQUE\s+KEY\s+`([a-z0-9\_]+)`\s*\(([a-z0-9\_,`\s]*)\).*$/i', $field_info, $match)) {
                if (preg_match_all('/`([a-z0-9\_]+)`/i', strtolower($match[2]), $keys)) {
                    return $keys[1];
                }
            }

            throw new exception("Could not parse table definition, unexpected line \"" . $field_info . "\".");
        }

        //KEY `hash_method` (`hash_method`),
        protected function key($field_info) {
            if (preg_match('/^KEY\s+`([a-z0-9\_]+)`\s*\(([a-z0-9\_,`\s]*)\).*$/i', $field_info, $match)) {
                if (preg_match_all('/`([a-z0-9\_]+)`/i', strtolower($match[2]), $keys)) {
                    return $keys[1];
                }
            }

            throw new exception("Could not parse table definition, unexpected line \"" . $field_info . "\".");
        }

        //CONSTRAINT `auth_user_ibfk_hash` FOREIGN KEY (`hash_method`) REFERENCES `auth_user_map_hashmethod` (`id`) ON UPDATE CASCADE,
        protected function foreign_key($field_info) {
            if (preg_match('/^CONSTRAINT\s+`([a-z0-9\_]+)`\s*FOREIGN\s+KEY\s+\(`([a-z0-9\_]+)`\)\s+REFERENCES\s+`([a-z0-9\_]+)`\s+\(`([a-z0-9\_]+)`\).*?$/i', $field_info, $match)) {
                return [
                    'name' 			=> strtolower($match[1]),
                    'field' 		=> strtolower($match[2]),
                    'parent_table' 	=> strtolower($match[3]),
                    'parent_field' 	=> strtolower($match[4]),
                ];
            }

            throw new exception("Could not parse table definition, unexpected line \"" . $field_info . "\". Note: composite Foreign Keys are not supported.");
        }

        /**
         * The extended type for a field in a database (eg, BIGINT -> int, VARCHAR -> string)
         *
         * @param string      $type
         * @param string|null $details details
         *
         * @return string
         */
        protected static function field_casting_extended($type, $details=null) {
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

                case 'enum':
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
        protected static function field_casting($type) {
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
        public static function autogenerated_on_insert($type) {
            switch (trim(strtolower($type))) {
                case 'timestamp':
                    return true;
            }

            return false;
        }

        public function boolean_true_value($details) {
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
        protected static function field_is_binary($type) {
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
         * @param sql_parser_field $field
         *
         * @return string
         */
        public static function api_type_validation(sql_parser_field $field) {
            //max sizes
            switch ((string) $field->type) {
                case 'bit':
                    return "->digit(0, 1)";

                case 'tinyint':
                    return $field->is_unsigned() ? "->digit(0, 255)" : "->digit(-128, 127)";

                case 'smallint':
                    return $field->is_unsigned() ? "->digit(0, 65535)" : "->digit(-32768, 32767)";

                case 'mediumint':
                    return $field->is_unsigned() ? "->digit(0, 16777215)" : "->digit(-8388608, 8388607)";

                case 'int':
                case 'integer':
                    return $field->is_unsigned() ? "->digit(0, 4294967295)" : "->digit(-2147483648, 2147483647)";

                case 'bigint':
                    return $field->is_unsigned() ? "->digit(0, 9223372036854775807)" : "->digit(-9223372036854775808, 9223372036854775807)";

                case 'varchar':
                case 'char':
                case 'varbinary':
                case 'binary':
                    return "->length(1, " . $field->size . ")";

                case 'tinytext':
                case 'tinyblob':
                    return "->length(1, 255)";

                case 'text':
                case 'blob':
                    return "->length(1, 65535)";

                case 'mediumtext':
                case 'mediumblob':
                    return "->length(1, 16777215)";

                case 'longtext':
                case 'longblob':
                    return "->length(1, 4294967295)";

                case 'timestamp':
                case 'datetime':
                    return "->is_datetime()";

                case 'date':
                    return "->is_date()";

                case 'enum':
                    return "->in([" . $field->size . "])";
            }
        }

        /**
         * Does this table have a primary key that allows only a small number of rows?
         *
         * @param sql_parser_table $table
         *
         * @return bool
         */
        public static function is_table_tiny(sql_parser_table $table) {
            if ($table->primary_key) {
                switch ((string) $table->primary_key->type) {
                    case 'tinyint':
                        return true;
                }
            }

            return false;
        }
    }