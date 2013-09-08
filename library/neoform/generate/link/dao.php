<?php

    namespace neoform;

    class generate_link_dao extends generate_dao {

        public function code() {

            $this->code .= '<?php'."\n\n";

            $this->code .= "\t/**\n";
            $this->code .= "\t * " . \ucwords(\str_replace('_', ' ', $this->table->name)) . " link DAO\n";
            $this->code .= "\t */\n";
            $this->code .= "\tclass " . $this->table->name . "_dao extends entity_link_dao implements " . $this->table->name . "_definition {\n\n";

            $this->constants();
            $this->bindings();

            $this->code .= "\t\t// READS\n\n";
            $this->selectors();

            $this->code .= "\t\t// WRITES\n\n";

            $this->insert();
            $this->insert_multi();
            $this->update();
            $this->delete();
            $this->delete_multi();

            $this->code .= "\t}\n";
        }

        protected function constants() {
            $longest_part = $this->table->longest_index_combinations();
            foreach ($this->table->all_index_combinations as $keys => $fields) {
                $this->code .= "\t\tconst " . \str_pad('BY_' . \strtoupper($keys), $longest_part + 3) . " = 'by_" . \strtolower($keys) . "';\n";
            }

            $this->code .= "\n";
        }

        protected function selectors() {

            $used_names = [];

            foreach ($this->table->all_index_combinations as $name => $index) {

                // No duplicates
                if (\in_array($name, $used_names)) {
                    continue;
                }

                $used_names[] = $name;

                // commenting
                $select_fields = [];
                $where_fields  = [];
                $params        = [];
                foreach ($this->table->fields as $field) {
                    // if there is only 1 "where" key don't select that key for the result set.
                    if (\count($index) !== 1 || $field !== \reset($index)) {
                        $select_fields[] = $field->name;
                    }
                }

                foreach ($index as $field) {
                    $where_fields[] = $field->name;
                    $params[]       = " * @param " . $field->casting . ($field->allows_null() ? '|null' : '') . " \$" . $field->name;
                }

                $this->code .= "\t\t/**\n";
                $this->code .= "\t\t * Get " . self::ander($select_fields) . " by " . self::ander($where_fields) . "\n";
                $this->code .= "\t\t *\n";
                $this->code .= "\t\t" . \join("\n\t\t", $params) . "\n";
                $this->code .= "\t\t * @param array|null \$order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)\n";
                $this->code .= "\t\t * @param integer|null \$offset get rows starting at this offset\n";
                $this->code .= "\t\t * @param integer|null \$limit max number of rows to return\n";
                $this->code .= "\t\t *\n";
                $this->code .= "\t\t * @return array result set containing " . self::ander($select_fields) . "\n";
                $this->code .= "\t\t */\n";
                // end commenting

                $function_params = [];
                $longest_part = $this->longest_length($index);
                foreach ($index as $field) {
                    $function_params[] = '$' . $field->name;
                }

                $this->code .= "\t\tpublic function by_" . $name . "(" . \join(', ', $function_params) . ", array \$order_by=null, \$offset=null, \$limit=null) {\n";
                $this->code .= "\t\t\treturn parent::_by_fields(\n";
                $this->code .= "\t\t\t\tself::BY_" . \strtoupper($name) . ",\n";

                // fields selected
                $this->code .= "\t\t\t\t[\n";
                foreach ($this->table->fields as $field) {
                    // if there is only 1 where key don't select that key for the result set.
                    if (\count($index) !== 1 || $field !== \reset($index)) {
                        $this->code .= "\t\t\t\t\t'" . $field->name . "',\n";
                    }
                }
                $this->code .= "\t\t\t\t],\n";

                // fields where
                $this->code .= "\t\t\t\t[\n";
                foreach ($index as $field) {
                    if ($field->allows_null()) {
                        $this->code .= "\t\t\t\t\t'" . \str_pad($field->name . "'", $longest_part + 1) . " => $" . $field->name . " === null ? null : (" . $field->casting . ") $" . $field->name . ",\n";
                    } else {
                        $this->code .= "\t\t\t\t\t'" . \str_pad($field->name . "'", $longest_part + 1) . " => (" . $field->casting . ") $" . $field->name . ",\n";
                    }
                }
                $this->code .= "\t\t\t\t],\n";
                $this->code .= "\t\t\t\t\$order_by,\n";
                $this->code .= "\t\t\t\t\$offset,\n";
                $this->code .= "\t\t\t\t\$limit\n";
                $this->code .= "\t\t\t);\n";
                $this->code .= "\t\t}\n\n";
            }

            // Multi
            foreach ($this->table->foreign_keys as $foreign_key_field) {

                // No duplicates
                if (\in_array($foreign_key_field->name_idless . '_multi', $used_names)) {
                    continue;
                }
                $used_names[] = $foreign_key_field->name_idless . '_multi';

                // comments
                $selected_fields = [];
                foreach ($this->table->fields as $field) {
                    if ($foreign_key_field !== $field) {
                        $selected_fields[] = $field->name;
                    }
                }

                $this->code .= "\t\t/**\n";
                $this->code .= "\t\t * Get multiple sets of " . self::ander($selected_fields) . " by a collection of " . $foreign_key_field->referenced_field->table->name . "s\n";
                $this->code .= "\t\t *\n";
                $this->code .= "\t\t * @param " . $foreign_key_field->referenced_field->table->name . "_collection|array \$" . $foreign_key_field->referenced_field->table->name . "_list\n";
                $this->code .= "\t\t * @param array|null \$order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)\n";
                $this->code .= "\t\t * @param integer|null \$offset get rows starting at this offset\n";
                $this->code .= "\t\t * @param integer|null \$limit max number of rows to return\n";
                $this->code .= "\t\t *\n";
                $this->code .= "\t\t * @return array of result sets containing " . self::ander($selected_fields) . "\n";
                $this->code .= "\t\t */\n";

                // end comments

                $this->code .= "\t\tpublic function by_" . $foreign_key_field->name_idless . "_multi($" . $foreign_key_field->referenced_field->table->name . "_list, array \$order_by=null, \$offset=null, \$limit=null) {\n";

                $this->code .= "\t\t\t\$keys = [];\n";

                $this->code .= "\t\t\tif (\$" . $foreign_key_field->referenced_field->table->name . "_list instanceof " . $foreign_key_field->referenced_field->table->name . "_collection) {\n";

                $this->code .= "\t\t\t\tforeach ($" . $foreign_key_field->referenced_field->table->name . "_list as \$k => $" . $foreign_key_field->referenced_field->table->name . ") {\n";
                $this->code .= "\t\t\t\t\t\$keys[\$k] = [\n";
                if ($foreign_key_field->allows_null()) {
                    $this->code .= "\t\t\t\t\t\t'" . $foreign_key_field->name . "' => $" . $foreign_key_field->referenced_field->table->name . "->" . $foreign_key_field->referenced_field->name . " === null ? null : (" . $foreign_key_field->referenced_field->casting . ") $" . $foreign_key_field->referenced_field->table->name . "->" . $foreign_key_field->referenced_field->name . ",\n";
                } else {
                    $this->code .= "\t\t\t\t\t\t'" . $foreign_key_field->name . "' => (" . $foreign_key_field->referenced_field->casting . ") $" . $foreign_key_field->referenced_field->table->name . "->" . $foreign_key_field->referenced_field->name . ",\n";
                }
                $this->code .= "\t\t\t\t\t];\n";
                $this->code .= "\t\t\t\t}\n\n";

                $this->code .= "\t\t\t} else {\n";

                $this->code .= "\t\t\t\tforeach ($" . $foreign_key_field->referenced_field->table->name . "_list as \$k => $" . $foreign_key_field->referenced_field->table->name . ") {\n";
                $this->code .= "\t\t\t\t\t\$keys[\$k] = [\n";
                if ($foreign_key_field->allows_null()) {
                    $this->code .= "\t\t\t\t\t\t'" . $foreign_key_field->name . "' => $" . $foreign_key_field->referenced_field->table->name . " === null ? null : (" . $foreign_key_field->referenced_field->casting . ") $" . $foreign_key_field->referenced_field->table->name . ",\n";
                } else {
                    $this->code .= "\t\t\t\t\t\t'" . $foreign_key_field->name . "' => (" . $foreign_key_field->referenced_field->casting . ") $" . $foreign_key_field->referenced_field->table->name . ",\n";
                }
                $this->code .= "\t\t\t\t\t];\n";
                $this->code .= "\t\t\t\t}\n\n";

                $this->code .= "\t\t\t}\n\n";

                $this->code .= "\t\t\treturn parent::_by_fields_multi(\n";
                $this->code .= "\t\t\t\tself::BY_" . \strtoupper($foreign_key_field->name_idless) . ",\n";

                // fields selected
                $this->code .= "\t\t\t\t[\n";
                foreach ($this->table->fields as $field) {
                    if ($field !== $foreign_key_field) {
                        $this->code .= "\t\t\t\t\t'" . $field->name . "',\n";
                    }
                }
                $this->code .= "\t\t\t\t],\n";

                $this->code .= "\t\t\t\t\$keys,\n";
                $this->code .= "\t\t\t\t\$order_by,\n";
                $this->code .= "\t\t\t\t\$offset,\n";
                $this->code .= "\t\t\t\t\$limit\n";
                $this->code .= "\t\t\t);\n";
                $this->code .= "\t\t}\n\n";
            }
        }

        protected function insert() {

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Insert " . \ucwords(\str_replace('_', ' ', $this->table->name)) . " link, created from an array of \$info\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param array \$info associative array, keys matching columns in database for this entity\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return boolean\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function insert(array \$info) {\n\n";
            $this->code .= "\t\t\treturn parent::_insert(\$info);\n";
            $this->code .= "\t\t}\n\n";
        }

        protected function insert_multi() {

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Insert multiple " . \ucwords(\str_replace('_', ' ', $this->table->name)) . " links, created from an array of arrays of \$info\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param array \$infos array of associative arrays, keys matching columns in database for this entity\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return boolean\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function insert_multi(array \$infos) {\n\n";
            $this->code .= "\t\t\treturn parent::_insert_multi(\$infos);\n";
            $this->code .= "\t\t}\n\n";
        }

        protected function update() {

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Update " . \ucwords(\str_replace('_', ' ', $this->table->name)) . " link records based on \$where inputs\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param array \$new_info the new link record data\n";
            $this->code .= "\t\t * @param array \$where associative array, matching columns with values\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return bool\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function update(array \$new_info, array \$where) {\n\n";
            $this->code .= "\t\t\t// Update link\n";
            $this->code .= "\t\t\treturn parent::_update(\$new_info, \$where);\n\n";
            $this->code .= "\t\t}\n\n";

        }

        protected function delete() {

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Delete multiple " . \ucwords(\str_replace('_', ' ', $this->table->name)) . " link records based on an array of associative arrays\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param array \$keys keys match the column names\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return bool\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function delete(array \$keys) {\n\n";
            $this->code .= "\t\t\t// Delete link\n";
            $this->code .= "\t\t\treturn parent::_delete(\$keys);\n\n";
            $this->code .= "\t\t}\n\n";
        }

        protected function delete_multi() {

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Delete multiple sets of " . \ucwords(\str_replace('_', ' ', $this->table->name)) . " link records based on an array of associative arrays\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param array \$keys_arr an array of arrays, keys match the column names\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return bool\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function delete_multi(array \$keys_arr) {\n\n";
            $this->code .= "\t\t\t// Delete links\n";
            $this->code .= "\t\t\treturn parent::_delete_multi(\$keys_arr);\n\n";
            $this->code .= "\t\t}\n";
        }
    }