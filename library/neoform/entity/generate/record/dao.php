<?php

    namespace neoform\entity\generate\record;

    use neoform\entity\generate;

    class dao extends generate\dao {

        public function code() {

            // Code
            $this->code .= '<?php'."\n\n";
            $this->code .= "\tnamespace neoform\\" . str_replace('_', '\\', $this->table->name) . ";\n\n";

            $this->code .= "\t/**\n";
            $this->code .= "\t * " . ucwords(str_replace('_', ' ', $this->table->name)) . " DAO\n";
            $this->code .= "\t */\n";

            $this->code .= "\tclass dao extends \\neoform\\entity\\record\\dao implements definition {\n\n";

            $this->constants();
            $this->bindings();

            if (count($this->table->all_non_pk_indexes)) {
                $this->code .= "\t\t// READS\n\n";
                $this->selectors();
            }

            $this->code .= "\t\t// WRITES\n\n";

            $this->insert();
            $this->insert_multi();
            $this->update();
            $this->delete();
            $this->delete_multi();

            $this->code .= "\t}\n";
        }

        protected function constants() {

            $used_names = [];

            $longest_part = $this->table->longest_non_pk_index_combinations();

            foreach ($this->table->all_non_pk_index_combinations as $keys => $fields) {

                // No duplicates
                if (in_array(strtolower($keys), $used_names)) {
                    continue;
                }
                $used_names[] = strtolower($keys);

                $this->code .= "\t\tconst " . str_pad('BY_' . strtoupper($keys), $longest_part + 3) . " = 'by_" . strtolower($keys) . "';\n";
            }

            $this->code .= "\n";
        }

        protected function selectors() {

            $used_names = [];

            foreach ($this->table->all_non_unique_indexes as $index) {

                $vars   = [];
                $names  = [];
                $fields = [];

                foreach ($index as $index_field) {

                    if (! $index_field->is_field_lookupable()) {
                        continue;
                    }

                    $fields[] = $index_field;
                    $vars[]   = "\${$index_field->name}";
                    $names[]  = $index_field->name_idless;
                    $name     = join('_', $names);

                    // No duplicates
                    if (in_array($name, $used_names)) {
                        continue;
                    }
                    $used_names[] = $name;

                    // Generate code
                    $this->code .= "\t\t/**\n";
                    $this->code .= "\t\t * Get " . ucwords(str_replace('_', ' ', $this->table->name)) . " {$this->table->primary_key->name}s by " . self::ander($names) . "\n";
                    $this->code .= "\t\t *\n";
                    foreach ($fields as $field) {
                        $this->code .= "\t\t * @param {$field->casting} \${$field->name}\n";
                    }
                    $this->code .= "\t\t * @param array \$order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)\n";
                    $this->code .= "\t\t * @param integer|null \$offset get PKs starting at this offset\n";
                    $this->code .= "\t\t * @param integer|null \$limit max number of PKs to return\n";
                    $this->code .= "\t\t *\n";
                    $this->code .= "\t\t * @return array of " . ucwords(str_replace('_', ' ', $this->table->name)) . " {$this->table->primary_key->name}s\n";
                    $this->code .= "\t\t */\n";

                    $this->code .= "\t\tpublic function by_{$name}(" . join(', ', $vars) . ", array \$order_by=null, \$offset=null, \$limit=null) {\n";

                    $this->code .= "\t\t\treturn parent::_by_fields(\n";
                    $this->code .= "\t\t\t\tself::BY_" . strtoupper($name) . ",\n";
                    $this->code .= "\t\t\t\t[\n";
                    $longest_part = $this->longest_length($fields, false, true);
                    foreach ($fields as $field) {
                        if ($field->allows_null()) {
                            $this->code .= "\t\t\t\t\t'" . str_pad("{$field->name}'", $longest_part + 1) . " => \${$field->name} === null ? null : ({$field->casting}) \${$field->name},\n";
                        } else {
                            $this->code .= "\t\t\t\t\t'" . str_pad("{$field->name}'", $longest_part + 1) . " => ({$field->casting}) \${$field->name},\n";
                        }
                    }
                    $this->code .= "\t\t\t\t],\n";
                    $this->code .= "\t\t\t\t\$order_by,\n";
                    $this->code .= "\t\t\t\t\$offset,\n";
                    $this->code .= "\t\t\t\t\$limit\n";
                    $this->code .= "\t\t\t);\n";

                    $this->code .= "\t\t}\n\n";
                }
            }

            foreach ($this->table->unique_keys as $index) {

                $vars         = [];
                $names        = [];
                $fields       = [];

                foreach ($index as $index_field) {

                    if (! $index_field->is_field_lookupable()) {
                        continue;
                    }

                    $fields[] = $index_field;
                    $vars[]   = "\${$index_field->name}";
                    $names[]  = $index_field->name_idless;
                    $name     = join('_', $names);

                    // No duplicates
                    if (in_array($name, $used_names)) {
                        continue;
                    }
                    $used_names[] = $name;

                    // Generate code
                    $this->code .= "\t\t/**\n";
                    $this->code .= "\t\t * Get " . ucwords(str_replace('_', ' ', $this->table->name)) . " {$this->table->primary_key->name}s by " . self::ander($names) . "\n";
                    $this->code .= "\t\t *\n";
                    foreach ($fields as $field) {
                        $this->code .= "\t\t * @param {$field->casting} \${$field->name}\n";
                    }
                    $this->code .= "\t\t *\n";
                    $this->code .= "\t\t * @return array of " . ucwords(str_replace('_', ' ', $this->table->name)) . " {$this->table->primary_key->name}s\n";
                    $this->code .= "\t\t */\n";

                    $this->code .= "\t\tpublic function by_{$name}(" . join(', ', $vars) . ") {\n";
                    $this->code .= "\t\t\treturn parent::_by_fields(\n";
                    $this->code .= "\t\t\t\tself::BY_" . strtoupper($name) . ",\n";
                    $this->code .= "\t\t\t\t[\n";
                    $longest_part = $this->longest_length($fields, false, true);
                    foreach ($fields as $field) {
                        if ($field->allows_null()) {
                            $this->code .= "\t\t\t\t\t'" . str_pad("{$field->name}'", $longest_part + 1) . " => \${$field->name} === null ? null : ({$field->casting}) \${$field->name},\n";
                        } else {
                            $this->code .= "\t\t\t\t\t'" . str_pad("{$field->name}'", $longest_part + 1) . " => ({$field->casting}) \${$field->name},\n";
                        }
                    }
                    $this->code .= "\t\t\t\t]\n";
                    $this->code .= "\t\t\t);\n";
                    $this->code .= "\t\t}\n\n";
                }
            }


            // Multi - applies only to foreign keys
            foreach ($this->table->foreign_keys as $field) {

                // No duplicates
                if (in_array($field->name_idless . '_multi', $used_names)) {
                    continue;
                }
                $used_names[] = $field->name_idless . '_multi';

                $this->code .= "\t\t/**\n";
                $this->code .= "\t\t * Get multiple sets of " . ucwords(str_replace('_', ' ', $this->table->name)) . " {$this->table->primary_key->name}s by {$field->referenced_field->table->name}\n";
                $this->code .= "\t\t *\n";
                $this->code .= "\t\t * @param \\neoform\\" . str_replace('_', '\\', $field->referenced_field->table->name) . "\\collection|array \${$field->referenced_field->table->name}_list\n";
                $this->code .= "\t\t * @param array \$order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)\n";
                $this->code .= "\t\t * @param integer|null \$offset get PKs starting at this offset\n";
                $this->code .= "\t\t * @param integer|null \$limit max number of PKs to return\n";
                $this->code .= "\t\t *\n";
                $this->code .= "\t\t * @return array of arrays containing " . ucwords(str_replace('_', ' ', $this->table->name)) . " {$this->table->primary_key->name}s\n";
                $this->code .= "\t\t */\n";

                $this->code .= "\t\tpublic function by_{$field->name_idless}_multi(\${$field->referenced_field->table->name}_list, array \$order_by=null, \$offset=null, \$limit=null) {\n";
                $this->code .= "\t\t\t\$keys = [];\n";

                $this->code .= "\t\t\tif (\${$field->referenced_field->table->name}_list instanceof \\neoform\\" . str_replace('_', '\\', $field->referenced_field->table->name) . "\\collection) {\n";

                $this->code .= "\t\t\t\tforeach (\${$field->referenced_field->table->name}_list as \$k => \${$field->referenced_field->table->name}) {\n";
                $this->code .= "\t\t\t\t\t\$keys[\$k] = [\n";
                if ($field->allows_null()) {
                    $this->code .= "\t\t\t\t\t\t'{$field->name}' => \${$field->referenced_field->table->name}->{$field->referenced_field->name} === null ? null : ({$field->casting}) \${$field->referenced_field->table->name}->{$field->referenced_field->name},\n";
                } else {
                    $this->code .= "\t\t\t\t\t\t'{$field->name}' => ({$field->casting}) \${$field->referenced_field->table->name}->{$field->referenced_field->name},\n";
                }
                $this->code .= "\t\t\t\t\t];\n";
                $this->code .= "\t\t\t\t}\n";

                $this->code .= "\t\t\t} else {\n";

                $this->code .= "\t\t\t\tforeach (\${$field->referenced_field->table->name}_list as \$k => \${$field->referenced_field->table->name}) {\n";
                $this->code .= "\t\t\t\t\t\$keys[\$k] = [\n";
                if ($field->allows_null()) {
                    $this->code .= "\t\t\t\t\t\t'{$field->name}' => \${$field->referenced_field->table->name} === null ? null : ({$field->casting}) \${$field->referenced_field->table->name},\n";
                } else {
                    $this->code .= "\t\t\t\t\t\t'{$field->name}' => ({$field->casting}) \${$field->referenced_field->table->name},\n";
                }
                $this->code .= "\t\t\t\t\t];\n";
                $this->code .= "\t\t\t\t}\n";

                $this->code .= "\t\t\t}\n";

                $this->code .= "\t\t\treturn parent::_by_fields_multi(\n";
                $this->code .= "\t\t\t\tself::BY_" . strtoupper($field->name_idless) . ",\n";
                $this->code .= "\t\t\t\t\$keys,\n";
                $this->code .= "\t\t\t\t\$order_by,\n";
                $this->code .= "\t\t\t\t\$offset,\n";
                $this->code .= "\t\t\t\t\$limit\n";
                $this->code .= "\t\t\t);\n";

                $this->code .= "\t\t}\n\n";
            }

            // Multi lookups on all other indexes that are not foreign keys
            foreach ($this->table->all_non_unique_indexes as $index) {

                $vars         = [];
                $names        = [];
                $fields       = [];
                $field_names  = [];
                foreach ($index as $index_field) {

                    if (! $index_field->is_field_lookupable()) {
                        continue;
                    }

                    $fields[]      = $index_field;
                    $vars[]        = 'array $' . $index_field->name;
                    $names[]       = $index_field->name_idless;
                    $field_names[] = $index_field->name . "s";
                    $name          = join('_', $names);

                    // No duplicates
                    if (in_array($name . '_multi', $used_names)) {
                        continue;
                    }
                    $used_names[] = $name . '_multi';

                    // Generate code
                    $this->code .= "\t\t/**\n";
                    $this->code .= "\t\t * Get " . ucwords(str_replace('_', ' ', $this->table->name)) . " {$this->table->primary_key->name}_arr by an array of " . self::ander($names) . "s\n";
                    $this->code .= "\t\t *\n";
                    if (count($field_names) === 1) {
                        $this->code .= "\t\t * @param array \${$name}_arr an array containing " . self::ander($field_names) . "\n";
                    } else {
                        $this->code .= "\t\t * @param array \${$name}_arr an array of arrays containing " . self::ander($field_names) . "\n";
                    }
                    $this->code .= "\t\t * @param array \$order_by array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)\n";
                    $this->code .= "\t\t * @param integer|null \$offset get PKs starting at this offset\n";
                    $this->code .= "\t\t * @param integer|null \$limit max number of PKs to return\n";
                    $this->code .= "\t\t *\n";
                    $this->code .= "\t\t * @return array of arrays of " . ucwords(str_replace('_', ' ', $this->table->name)) . " {$this->table->primary_key->name}s\n";
                    $this->code .= "\t\t */\n";

                    $this->code .= "\t\tpublic function by_{$name}_multi(array \${$name}_arr, array \$order_by=null, \$offset=null, \$limit=null) {\n";
                    $this->code .= "\t\t\t\$keys_arr = [];\n";
                    $this->code .= "\t\t\tforeach (\${$name}_arr as \$k => \${$name}) {\n";
                    if (count($fields) === 1) {
                        $this->code .= "\t\t\t\t\$keys_arr[\$k] = [ '{$index_field->name}' => ({$index_field->casting}) \${$index_field->name}, ];\n";
                    } else {
                        $this->code .= "\t\t\t\t\$keys_arr[\$k] = [\n";
                        $longest_part = $this->longest_length($fields, false, true);
                        foreach ($fields as $field) {
                            $this->code .= "\t\t\t\t\t'" . str_pad("{$field->name}'", $longest_part + 1) . " => ({$field->casting}) \${$name}['{$field->name}'],\n";
                        }
                        $this->code .= "\t\t\t\t];\n";
                    }
                    $this->code .= "\t\t\t}\n";

                    $this->code .= "\t\t\treturn parent::_by_fields_multi(\n";
                    $this->code .= "\t\t\t\tself::BY_" . strtoupper($name) . ",\n";
                    $this->code .= "\t\t\t\t\$keys_arr,\n";
                    $this->code .= "\t\t\t\t\$order_by,\n";
                    $this->code .= "\t\t\t\t\$offset,\n";
                    $this->code .= "\t\t\t\t\$limit\n";
                    $this->code .= "\t\t\t);\n";

                    $this->code .= "\t\t}\n\n";
                }
            }

            // Multi lookups on all other indexes that are not foreign keys
            foreach ($this->table->unique_keys as $index) {

                $vars         = [];
                $names        = [];
                $fields       = [];
                $field_names  = [];
                foreach ($index as $index_field) {

                    if (! $index_field->is_field_lookupable()) {
                        continue;
                    }

                    $fields[]      = $index_field;
                    $vars[]        = "array \${$index_field->name}";
                    $names[]       = $index_field->name_idless;
                    $field_names[] = "{$index_field->name}s";
                    $name          = join('_', $names);

                    // No duplicates
                    if (in_array($name . '_multi', $used_names)) {
                        continue;
                    }
                    $used_names[] = $name . '_multi';

                    // Generate code
                    $this->code .= "\t\t/**\n";
                    $this->code .= "\t\t * Get " . ucwords(str_replace('_', ' ', $this->table->name)) . " {$this->table->primary_key->name}_arr by an array of " . self::ander($names) . "s\n";
                    $this->code .= "\t\t *\n";
                    if (count($field_names) === 1) {
                        $this->code .= "\t\t * @param array \${$name}_arr an array containing " . self::ander($field_names) . "\n";
                    } else {
                        $this->code .= "\t\t * @param array \${$name}_arr an array of arrays containing " . self::ander($field_names) . "\n";
                    }
                    $this->code .= "\t\t *\n";
                    $this->code .= "\t\t * @return array of arrays of " . ucwords(str_replace('_', ' ', $this->table->name)) . " {$this->table->primary_key->name}s\n";
                    $this->code .= "\t\t */\n";

                    $this->code .= "\t\tpublic function by_{$name}_multi(array \${$name}_arr) {\n";
                    $this->code .= "\t\t\t\$keys_arr = [];\n";
                    $this->code .= "\t\t\tforeach (\${$name}_arr as \$k => \${$name}) {\n";
                    if (count($fields) === 1) {
                        $this->code .= "\t\t\t\t\$keys_arr[\$k] = [ '{$index_field->name}' => ({$index_field->casting}) \${$index_field->name}, ];\n";
                    } else {
                        $this->code .= "\t\t\t\t\$keys_arr[\$k] = [\n";
                        $longest_part = $this->longest_length($fields, false, true);
                        foreach ($fields as $field) {
                            $this->code .= "\t\t\t\t\t'" . str_pad("{$field->name}'", $longest_part + 1) . " => ({$field->casting}) \${$name}['{$field->name}'],\n";
                        }
                        $this->code .= "\t\t\t\t];\n";
                    }
                    $this->code .= "\t\t\t}\n";

                    $this->code .= "\t\t\treturn parent::_by_fields_multi(\n";
                    $this->code .= "\t\t\t\tself::BY_" . strtoupper($name) . ",\n";
                    $this->code .= "\t\t\t\t\$keys_arr\n";
                    $this->code .= "\t\t\t);\n";

                    $this->code .= "\t\t}\n\n";
                }
            }
        }

        protected function insert() {

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Insert " . ucwords(str_replace('_', ' ', $this->table->name)) . " record, created from an array of \$info\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param array \$info associative array, keys matching columns in database for this entity\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return model\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function insert(array \$info) {\n\n";
            $this->code .= "\t\t\t// Insert record\n";
            $this->code .= "\t\t\treturn parent::_insert(\$info);\n";
            $this->code .= "\t\t}\n\n";
        }

        protected function insert_multi() {

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Insert multiple " . ucwords(str_replace('_', ' ', $this->table->name)) . " records, created from an array of arrays of \$info\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param array \$infos array of associative arrays, keys matching columns in database for this entity\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return collection\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function insert_multi(array \$infos) {\n\n";
            $this->code .= "\t\t\t// Insert record\n";
            $this->code .= "\t\t\treturn parent::_insert_multi(\$infos);\n";
            $this->code .= "\t\t}\n\n";
        }

        protected function update() {

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Updates a " . ucwords(str_replace('_', ' ', $this->table->name)) . " record with new data\n";
            $this->code .= "\t\t *   only fields that are specified in the \$info array will be written\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param model \${$this->table->name} record to be updated\n";
            $this->code .= "\t\t * @param array \$info data to write to the record\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return model updated model\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function update(model \${$this->table->name}, array \$info) {\n\n";
            $this->code .= "\t\t\t// Update record\n";
            $this->code .= "\t\t\treturn parent::_update(\${$this->table->name}, \$info);\n";
            $this->code .= "\t\t}\n\n";
        }

        protected function delete() {

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Delete a " . ucwords(str_replace('_', ' ', $this->table->name)) . " record\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param model \${$this->table->name} record to be deleted\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return bool\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function delete(model \${$this->table->name}) {\n\n";
            $this->code .= "\t\t\t// Delete record\n";
            $this->code .= "\t\t\treturn parent::_delete(\${$this->table->name});\n";
            $this->code .= "\t\t}\n\n";
        }

        protected function delete_multi() {

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Delete multiple " . ucwords(str_replace('_', ' ', $this->table->name)) . " records\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param collection \${$this->table->name}_collection records to be deleted\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return bool\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function delete_multi(collection \${$this->table->name}_collection) {\n\n";
            $this->code .= "\t\t\t// Delete records\n";
            $this->code .= "\t\t\treturn parent::_delete_multi(\${$this->table->name}_collection);\n";
            $this->code .= "\t\t}\n";
        }
    }