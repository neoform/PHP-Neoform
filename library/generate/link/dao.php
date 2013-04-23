<?php

    class generate_link_dao extends generate_dao {

        public function code() {

            $this->code .= '<?php'."\n\n";

            $this->code .= "\t/**\n";
            $this->code .= "\t * " . ucwords(str_replace('_', ' ', $this->table->name)) . " link DAO\n";
            $this->code .= "\t */\n";
            $this->code .= "\tclass " . $this->table->name . "_dao extends link_dao implements " . $this->table->name . "_definition {\n\n";

            $this->constants();
            $this->bindings();

            $this->code .= "\t\t// READS\n\n";
            $this->selectors();

            $this->code .= "\t\t// WRITES\n\n";

            $this->insert();
            $this->inserts();
            $this->update();
            $this->delete();
            $this->deletes();

            $this->code .= "\t}\n";
        }

        protected function constants() {
            $longest_part = $this->table->longest_index_combinations();
            foreach ($this->table->all_index_combinations as $keys => $fields) {
                $this->code .= "\t\tconst " . str_pad('BY_' . strtoupper($keys), $longest_part + 3) . " = 'by_" . strtolower($keys) . "';\n";
            }

            $this->code .= "\n";
        }

        protected function selectors() {

            foreach ($this->table->all_indexes as $index) {

                $vars         = [];
                $names        = [];
                $longest_part = $this->longest_length($index);
                foreach ($index as $field) {
                    $vars[]  = '$' . $field->name;
                    $names[] = $field->name_idless;
                }
                $name = join('_',  $names);

                // commenting
                $select_fields = [];
                $where_fields  = [];
                $params        = [];
                foreach ($this->table->fields as $field) {
                    // if there is only 1 where key don't select that key for the result set.
                    if (count($index) !== 1 || $field !== current($index)) {
                        $select_fields[] = $field->name;
                    }
                }

                foreach ($index as $field) {
                    $where_fields[] = $field->name;
                    $params[]       = " * @param " . $field->casting . ($field->allows_null() ? '|null' : '') . " \$" . $field->name;
                }

                reset($index);

                $this->code .= "\t\t/**\n";
                $this->code .= "\t\t * Get " . self::ander($select_fields) . " by " . self::ander($where_fields) . "\n";
                $this->code .= "\t\t *\n";
                $this->code .= "\t\t" . join("\n\t\t", $params) . "\n";
                $this->code .= "\t\t *\n";
                $this->code .= "\t\t * @return array result set containing " . self::ander($select_fields) . "\n";
                $this->code .= "\t\t */\n";

                // end commenting

                $this->code .= "\t\tpublic static function by_" . $name . "(" . join(', ', $vars) . ") {\n";
                $this->code .= "\t\t\treturn self::_by_fields(\n";
                $this->code .= "\t\t\t\tself::BY_" . strtoupper($name) . ",\n";

                // fields selected
                $this->code .= "\t\t\t\t[\n";
                foreach ($this->table->fields as $field) {
                    // if there is only 1 where key don't select that key for the result set.
                    if (count($index) !== 1 || $field !== current($index)) {
                        $this->code .= "\t\t\t\t\t'" . $field->name . "',\n";
                    }
                }
                $this->code .= "\t\t\t\t],\n";

                // fields where
                $this->code .= "\t\t\t\t[\n";
                foreach ($index as $field) {
                    if ($field->allows_null()) {
                        $this->code .= "\t\t\t\t\t'" . str_pad($field->name . "'", $longest_part + 1) . " => $" . $field->name . " === null ? null : (" . $field->casting . ") $" . $field->name . ",\n";
                    } else {
                        $this->code .= "\t\t\t\t\t'" . str_pad($field->name . "'", $longest_part + 1) . " => (" . $field->casting . ") $" . $field->name . ",\n";
                    }
                }
                $this->code .= "\t\t\t\t]\n";
                $this->code .= "\t\t\t);\n";
                $this->code .= "\t\t}\n\n";
            }

            // Multi
            foreach ($this->table->foreign_keys as $foreign_key_field) {

                // comments
                $selected_fields = [];
                foreach ($this->table->fields as $field) {
                    if ($foreign_key_field !== $field) {
                        $selected_fields[] = $field->name;
                    }
                }

                $this->code .= "\t\t/**\n";
                $this->code .= "\t\t * Get multiple sets of " . self::ander($selected_fields) . " by " . $foreign_key_field->name . "\n";
                $this->code .= "\t\t *\n";
                $this->code .= "\t\t * @param " . $foreign_key_field->referenced_field->table->name . "_collection \$" . $foreign_key_field->referenced_field->table->name . "_collection\n";
                $this->code .= "\t\t *\n";
                $this->code .= "\t\t * @return array of result sets containing " . self::ander($selected_fields) . "\n";
                $this->code .= "\t\t */\n";

                // end comments

                $this->code .= "\t\tpublic static function by_" . $foreign_key_field->referenced_field->table->name . "_multi(" . $foreign_key_field->referenced_field->table->name . "_collection $" . $foreign_key_field->referenced_field->table->name . "_collection) {\n";

                $this->code .= "\t\t\t\$keys = [];\n";
                $this->code .= "\t\t\tforeach ($" . $foreign_key_field->referenced_field->table->name . "_collection as \$k => $" . $foreign_key_field->referenced_field->table->name . ") {\n";
                $this->code .= "\t\t\t\t\$keys[\$k] = [\n";
                if ($foreign_key_field->allows_null()) {
                    $this->code .= "\t\t\t\t\t'" . $foreign_key_field->name . "' => $" . $foreign_key_field->referenced_field->table->name . "->" . $foreign_key_field->referenced_field->name . " === null ? null : (" . $foreign_key_field->referenced_field->casting . ") $" . $foreign_key_field->referenced_field->table->name . "->" . $foreign_key_field->referenced_field->name . ",\n";
                   } else {
                       $this->code .= "\t\t\t\t\t'" . $foreign_key_field->name . "' => (" . $foreign_key_field->referenced_field->casting . ") $" . $foreign_key_field->referenced_field->table->name . "->" . $foreign_key_field->referenced_field->name . ",\n";
                }
                $this->code .= "\t\t\t\t];\n";
                $this->code .= "\t\t\t}\n\n";

                $this->code .= "\t\t\treturn self::_by_fields_multi(\n";
                $this->code .= "\t\t\t\tself::BY_" . strtoupper($foreign_key_field->referenced_field->table->name) . ",\n";

                // fields selected
                $this->code .= "\t\t\t\t[\n";
                foreach ($this->table->fields as $field) {
                    if ($field !== $foreign_key_field) {
                        $this->code .= "\t\t\t\t\t'" . $field->name . "',\n";
                    }
                }
                $this->code .= "\t\t\t\t],\n";

                $this->code .= "\t\t\t\t\$keys\n";
                $this->code .= "\t\t\t);\n";
                $this->code .= "\t\t}\n\n";
            }
        }

        protected function insert() {

            /**
             * Insert Folder link, created from an array of $info
             *
             * @param array $info associative array, keys matching columns in database for this entity
             *
             * @return boolean
             */

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Insert " . ucwords(str_replace('_', ' ', $this->table->name)) . " link, created from an array of \$info\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param array \$info associative array, keys matching columns in database for this entity\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return boolean\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic static function insert(array \$info) {\n";

            if ($this->table->is_tiny() || count($this->table->all_indexes) || $this->all) {
                $this->code .= "\t\t\t\$return = parent::_insert(\$info);\n\n";
                $this->code .= "\t\t\t// Delete Cache\n";

                // ALL
                if ($this->table->is_tiny() || $this->all) {
                    $this->code .= "\t\t\t// BY_ALL\n";
                    $this->code .= "\t\t\tparent::_cache_delete(\n";
                    $this->code .= "\t\t\t\tparent::_build_key(self::BY_ALL)\n";
                    $this->code .= "\t\t\t);\n\n";
                }

                foreach ($this->table->all_indexes as $index) {
                    $idless        = [];
                    $issets        = [];
                    $fields        = [];
                    $longest_index = 0;
                    foreach ($index as $field) {
                        $fields[] = $field;
                        $idless[] = $field->name_idless;
                        $issets[] = "array_key_exists('" . $field->name . "', \$info)";
                        if (strlen($field->name) > $longest_index) {
                            $longest_index = strlen($field->name);
                        }
                    }
                    $name = join('_', $idless);

                    $this->code .= "\t\t\t// BY_" . strtoupper($name) . "\n";
                    $this->code .= "\t\t\tif (" . join(' && ', $issets) . ") {\n";
                    $this->code .= "\t\t\t\tparent::_cache_delete(\n";
                    $this->code .= "\t\t\t\t\tparent::_build_key(\n";
                    $this->code .= "\t\t\t\t\t\tself::BY_" . strtoupper($name) . ",\n";
                    $this->code .= "\t\t\t\t\t\t[\n";
                    foreach ($fields as $field) {
                        if ($field->allows_null()) {
                            $this->code .= "\t\t\t\t\t\t\t'" . str_pad($field->name . "'", $longest_index + 1) . " => \$info['" . $field->name . "'] === null ? null : (" . $field->casting . ") \$info['" . $field->name . "'],\n";
                        } else {
                            $this->code .= "\t\t\t\t\t\t\t'" . str_pad($field->name . "'", $longest_index + 1) . " => (" . $field->casting . ") \$info['" . $field->name . "'],\n";
                        }
                    }
                    $this->code .= "\t\t\t\t\t\t]\n";
                    $this->code .= "\t\t\t\t\t)\n";
                    $this->code .= "\t\t\t\t);\n";
                    $this->code .= "\t\t\t}\n\n";
                }

                $this->code .= "\t\t\treturn \$return;\n";
            } else {
                $this->code .= "\t\t\treturn parent::_insert(\$info);\n";
            }
            $this->code .= "\t\t}\n\n";
        }

        protected function inserts() {

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Insert multiple " . ucwords(str_replace('_', ' ', $this->table->name)) . " links, created from an array of arrays of \$info\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param array \$infos array of associative arrays, keys matching columns in database for this entity\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return boolean\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic static function inserts(array \$infos) {\n";

            if ($this->table->is_tiny() || count($this->table->all_indexes) || $this->all) {
                $this->code .= "\t\t\t\$return = parent::_inserts(\$infos);\n\n";
                $this->code .= "\t\t\t// Delete Cache\n";

                if ($this->table->is_tiny() || $this->all) {
                    $this->code .= "\t\t\t// BY_ALL\n";
                    $this->code .= "\t\t\tparent::_cache_delete(\n";
                    $this->code .= "\t\t\t\tparent::_build_key(self::BY_ALL)\n";
                    $this->code .= "\t\t\t);\n\n";
                }

                if (count($this->table->all_indexes)) {
                    $this->code .= "\t\t\tforeach (\$infos as \$info) {\n";

                    foreach ($this->table->all_indexes as $index) {
                        $fields        = [];
                        $idless        = [];
                        $issets        = [];
                        $longest_index = 0;
                        foreach ($index as $field) {
                            $fields[] = $field;
                            $idless[] = $field->name_idless;
                            $issets[] = "array_key_exists('" . $field->name . "', \$info)";
                            if (strlen($field->name) > $longest_index) {
                                $longest_index = strlen($field->name);
                            }
                        }
                        $name = join('_', $idless);

                        $this->code .= "\t\t\t\t// BY_" . strtoupper($name) . "\n";
                        $this->code .= "\t\t\t\tif (" . join(' && ', $issets) . ") {\n";
                        $this->code .= "\t\t\t\t\tparent::_cache_delete(\n";
                        $this->code .= "\t\t\t\t\t\tparent::_build_key(\n";
                        $this->code .= "\t\t\t\t\t\t\tself::BY_" . strtoupper($name) . ",\n";
                        $this->code .= "\t\t\t\t\t\t\t[\n";
                        foreach ($fields as $field) {
                            if ($field->allows_null()) {
                                $this->code .= "\t\t\t\t\t\t\t\t'" . str_pad($field->name . "'", $longest_index + 1) . " => \$info['" . $field->name . "'] === null ? null : (" . $field->casting . ") \$info['" . $field->name . "'],\n";
                            } else {
                                $this->code .= "\t\t\t\t\t\t\t\t'" . str_pad($field->name . "'", $longest_index + 1) . " => (" . $field->casting . ") \$info['" . $field->name . "'],\n";
                            }
                        }
                        $this->code .= "\t\t\t\t\t\t\t]\n";
                        $this->code .= "\t\t\t\t\t\t)\n";
                        $this->code .= "\t\t\t\t\t);\n";
                        $this->code .= "\t\t\t\t}\n\n";
                    }

                    $this->code = substr($this->code, 0, -1);
                    $this->code .= "\t\t\t}\n\n";
                }

                $this->code .= "\t\t\treturn \$return;\n";
            } else {
                $this->code .= "\t\t\treturn parent::_inserts(\$infos);\n";
            }
            $this->code .= "\t\t}\n\n";
        }

        protected function update() {

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Update " . ucwords(str_replace('_', ' ', $this->table->name)) . " link records based on \$where inputs\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param array \$new_info the new link record data\n";
            $this->code .= "\t\t * @param array \$where associative array, matching columns with values\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return bool\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic static function update(array \$new_info, array \$where) {\n";
            $this->code .= "\t\t\t\$return = parent::_update(\$new_info, \$where);\n\n";
            $this->code .= "\t\t\t// Delete Cache\n";

            foreach ($this->table->all_indexes as $index) {

                // $new_info
                $issets       = [];
                $names        = [];
                $longest_part = $this->longest_length($index);
                foreach ($index as $field) {
                    $issets[] = "array_key_exists('" . $field->name . "', \$new_info)";
                    $names[]  = $field->name_idless;
                }
                $name = join('_',  $names);

                $this->code .= "\t\t\t// BY_" . strtoupper($name) . "\n";
                $this->code .= "\t\t\tif (" . join(' && ', $issets) . ") {\n";
                $this->code .= "\t\t\t\tparent::_cache_delete(\n";
                $this->code .= "\t\t\t\t\tparent::_build_key(\n";
                $this->code .= "\t\t\t\t\t\tself::BY_" . strtoupper($name) . ",\n";
                $this->code .= "\t\t\t\t\t\t[\n";
                foreach ($index as $field) {
                    if ($field->allows_null()) {
                        $this->code .= "\t\t\t\t\t\t\t'" . str_pad($field->name . "'", $longest_part + 1) . " => \$new_info['" . $field->name . "'] === null ? null : (" . $field->casting . ") \$new_info['" . $field->name . "'],\n";
                    } else {
                        $this->code .= "\t\t\t\t\t\t\t'" . str_pad($field->name . "'", $longest_part + 1) . " => (" . $field->casting . ") \$new_info['" . $field->name . "'],\n";
                    }
                }
                $this->code .= "\t\t\t\t\t\t]\n";
                $this->code .= "\t\t\t\t\t)\n";
                $this->code .= "\t\t\t\t);\n";
                $this->code .= "\t\t\t}\n";

                // $where
                $issets = [];
                $names  = [];
                foreach ($index as $field) {
                    $issets[] = "array_key_exists('" . $field->name . "', \$where)";
                    $names[]  = $field->name_idless;
                }
                $name = join('_',  $names);

                $this->code .= "\t\t\tif (" . join(' && ', $issets) . ") {\n";
                $this->code .= "\t\t\t\tparent::_cache_delete(\n";
                $this->code .= "\t\t\t\t\tparent::_build_key(\n";
                $this->code .= "\t\t\t\t\t\tself::BY_" . strtoupper($name) . ",\n";
                $this->code .= "\t\t\t\t\t\t[\n";
                foreach ($index as $field) {
                    if ($field->allows_null()) {
                        $this->code .= "\t\t\t\t\t\t\t'" . str_pad($field->name . "'", $longest_part + 1) . " => \$where['" . $field->name . "'] === null ? null : (" . $field->casting . ") \$where['" . $field->name . "'],\n";
                    } else {
                        $this->code .= "\t\t\t\t\t\t\t'" . str_pad($field->name . "'", $longest_part + 1) . " => (" . $field->casting . ") \$where['" . $field->name . "'],\n";
                    }
                }
                $this->code .= "\t\t\t\t\t\t]\n";
                $this->code .= "\t\t\t\t\t)\n";
                $this->code .= "\t\t\t\t);\n";
                $this->code .= "\t\t\t}\n\n";
            }

            $this->code .= "\t\t\treturn \$return;\n";
            $this->code .= "\t\t}\n\n";

        }

        protected function delete() {

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Delete multiple " . ucwords(str_replace('_', ' ', $this->table->name)) . " link records based on an array of associative arrays\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param array \$keys keys match the column names\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return bool\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic static function delete(array \$keys) {\n";
            $this->code .= "\t\t\t\$return = parent::_delete(\$keys);\n\n";
            $this->code .= "\t\t\t// Delete Cache\n";

            foreach ($this->table->all_indexes as $index) {

                $names  = [];
                foreach ($index as $field) {
                    $names[] = $field->name_idless;
                }
                $name = join('_',  $names);

                $longest_part = $this->longest_length($index);

                $this->code .= "\t\t\t// BY_" . strtoupper($name) . "\n";
                $this->code .= "\t\t\tparent::_cache_delete(\n";
                $this->code .= "\t\t\t\tparent::_build_key(\n";
                $this->code .= "\t\t\t\t\tself::BY_" . strtoupper($name) . ",\n";
                $this->code .= "\t\t\t\t\t[\n";
                foreach ($index as $field) {
                    if ($field->allows_null()) {
                        $this->code .= "\t\t\t\t\t\t'" . str_pad($field->name . "'", $longest_part + 1) . " => \$keys['" . $field->name . "'] === null ? null : (" . $field->casting . ") \$keys['" . $field->name . "'],\n";
                    } else {
                        $this->code .= "\t\t\t\t\t\t'" . str_pad($field->name . "'", $longest_part + 1) . " => (" . $field->casting . ") \$keys['" . $field->name . "'],\n";
                    }
                }
                $this->code .= "\t\t\t\t\t]\n";
                $this->code .= "\t\t\t\t)\n";
                $this->code .= "\t\t\t);\n\n";
            }

            $this->code .= "\t\t\treturn \$return;\n";
            $this->code .= "\t\t}\n\n";
        }

        protected function deletes() {

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Delete multiple sets of " . ucwords(str_replace('_', ' ', $this->table->name)) . " link records based on an array of associative arrays\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param array \$keys_arr an array of arrays, keys match the column names\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return bool\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic static function deletes(array \$keys_arr) {\n";
            $this->code .= "\t\t\t\$return = parent::_deletes(\$keys_arr);\n\n";

            $this->code .= "\t\t\t// PRIMARY KEYS\n";
            $this->code .= "\t\t\tforeach (\$keys_arr as \$keys) {\n";

            $idless = [];
            foreach ($this->table->primary_keys as $field) {
                $idless[] = $field->name_idless;
                $this->code .= "\t\t\t\t\$unique_" . $field->name . "_arr[(int) \$keys['" . $field->name . "']] = (" . $field->casting . ") \$keys['" . $field->name . "'];\n";
            }

            $this->code .= "\n";

            $full_index = join('_', $idless);

            $this->code .= "\t\t\t\t// BY_" . strtoupper($full_index) . "\n";
            $this->code .= "\t\t\t\tparent::_cache_delete(\n";
            $this->code .= "\t\t\t\t\tparent::_build_key(\n";
            $this->code .= "\t\t\t\t\t\tself::BY_" . strtoupper($full_index) . ",\n";
            $this->code .= "\t\t\t\t\t\t[\n";

            $longest = $this->longest_length($this->table->primary_keys);
            foreach ($this->table->primary_keys as $field) {
                $this->code .= "\t\t\t\t\t\t\t'" . str_pad($field->name . "'", $longest +1) . " => (" . $field->casting . ") \$keys['" . $field->name . "'],\n";
            }
            $this->code .= "\t\t\t\t\t\t]\n";
            $this->code .= "\t\t\t\t\t)\n";
            $this->code .= "\t\t\t\t);\n";
            $this->code .= "\t\t\t}\n\n";

            foreach ($this->table->primary_keys as $field) {
                $this->code .= "\t\t\t// BY_" . strtoupper($field->name_idless) . "\n";
                $this->code .= "\t\t\tforeach (\$unique_" . $field->name . "_arr as $" . $field->name . ") {\n";
                $this->code .= "\t\t\t\tparent::_cache_delete(\n";
                $this->code .= "\t\t\t\t\tparent::_build_key(\n";
                $this->code .= "\t\t\t\t\t\tself::BY_" . strtoupper($field->name_idless) . ",\n";
                $this->code .= "\t\t\t\t\t\t[\n";
                $this->code .= "\t\t\t\t\t\t\t'" . $field->name . "' => (" . $field->casting . ") $" . $field->name . ",\n";
                $this->code .= "\t\t\t\t\t\t]\n";
                $this->code .= "\t\t\t\t\t)\n";
                $this->code .= "\t\t\t\t);\n";
                $this->code .= "\t\t\t}\n\n";
            }

            $this->code .= "\t\t\treturn \$return;\n";
            $this->code .= "\t\t}\n";
        }
    }