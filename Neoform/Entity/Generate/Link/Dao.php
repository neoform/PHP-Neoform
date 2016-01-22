<?php

    namespace Neoform\Entity\Generate\Link;

    use Neoform\Entity\Generate;

    class Dao extends Generate\Dao {

        public function code() {

            $this->code .= '<?php'."\n\n";
            $this->code .= "\tnamespace {$this->namespace}\\{$this->table->getNameAsClass()};\n\n";
            $this->code .= "\tuse Neoform;\n";
            if ($this->namespace !== 'Neoform') {
                $this->code .= "\tuse {$this->namespace};\n";
            }
            $this->code .= "\n";

            $this->code .= "\t/**\n";
            $this->code .= "\t * {$this->table->getNameLabel()} link DAO\n";
            $this->code .= "\t */\n";
            $this->code .= "\tclass Dao extends Neoform\\Entity\\Link\\Dao {\n\n";
            $this->code .= "\t\t// Load entity details into the class\n";
            $this->code .= "\t\tuse Details;\n\n";

            $this->constants();
            $this->bindings();

            $this->code .= "\t\t// READS\n\n";
            $this->selectors();

            $this->code .= "\t\t// WRITES\n\n";

            $this->insert();
            $this->insertMulti();
            $this->update();
            $this->delete();
            $this->deleteMulti();

            $this->code .= "\t}\n";
        }

        protected function constants() {
            $longest_part = $this->table->longestIndexCombinations();
            foreach ($this->table->getAllIndexCombinations() as $keys => $fields) {
                $this->code .= "\t\tconst " . str_pad('BY_' . strtoupper($keys), $longest_part + 3) . " = 'by" . str_replace(' ', '', ucwords(str_replace('_', ' ', $keys))) . "';\n";
            }

            $this->code .= "\n";
        }

        protected function selectors() {

            $used_names = [];

            foreach ($this->table->getAllIndexCombinations() as $name => $index) {

                // No duplicates
                if (in_array($name, $used_names)) {
                    continue;
                }

                $used_names[] = $name;

                // commenting
                $select_fields = [];
                $where_fields  = [];
                $params        = [];
                foreach ($this->table->getFields() as $field) {
                    // if there is only 1 "where" key don't select that key for the result set.
                    if (count($index) !== 1 || $field !== reset($index)) {
                        $select_fields[] = $field->getName();
                    }
                }

                foreach ($index as $field) {
                    $where_fields[] = $field->getName();
                    $params[]       = " * @param " . $field->getCasting() . ($field->allowsNull() ? '|null' : '') . " \${$field->getNameCamelCase()}";
                }

                $this->code .= "\t\t/**\n";
                $this->code .= "\t\t * Get " . self::ander($select_fields) . " by " . self::ander($where_fields) . "\n";
                $this->code .= "\t\t *\n";
                $this->code .= "\t\t" . join("\n\t\t", $params) . "\n";
                $this->code .= "\t\t * @param array|null \$orderBy array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)\n";
                $this->code .= "\t\t * @param int|null   \$offset get rows starting at this offset\n";
                $this->code .= "\t\t * @param int|null   \$limit max number of rows to return\n";
                $this->code .= "\t\t *\n";
                $this->code .= "\t\t * @return array result set containing " . self::ander($select_fields) . "\n";
                $this->code .= "\t\t */\n";
                // end commenting

                $functionParams = [];
                $longest_part = $this->longestLength($index);
                foreach ($index as $field) {
                    $functionParams[] = "\${$field->getNameCamelCase()}";
                }

                $fName = str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));

                $this->code .= "\t\tpublic function by{$fName}(" . join(', ', $functionParams) . ", array \$orderBy=null, \$offset=null, \$limit=null) {\n";
                $this->code .= "\t\t\treturn parent::_byFields(\n";
                $this->code .= "\t\t\t\tself::BY_" . strtoupper($name) . ",\n";

                // fields selected
                $this->code .= "\t\t\t\t[\n";
                foreach ($this->table->getFields() as $field) {
                    // if there is only 1 where key don't select that key for the result set.
                    if (count($index) !== 1 || $field !== reset($index)) {
                        $this->code .= "\t\t\t\t\t'{$field->getName()}',\n";
                    }
                }
                $this->code .= "\t\t\t\t],\n";

                // fields where
                $this->code .= "\t\t\t\t[\n";
                foreach ($index as $field) {
                    if ($field->allowsNull()) {
                        $this->code .= "\t\t\t\t\t'" . str_pad($field->getName() . "'", $longest_part + 1) . " => \${$field->getNameCamelCase()} === null ? null : ({$field->getCasting()}) \${$field->getNameCamelCase()},\n";
                    } else {
                        $this->code .= "\t\t\t\t\t'" . str_pad($field->getName() . "'", $longest_part + 1) . " => ({$field->getCasting()}) \${$field->getNameCamelCase()},\n";
                    }
                }
                $this->code .= "\t\t\t\t],\n";
                $this->code .= "\t\t\t\t\$orderBy,\n";
                $this->code .= "\t\t\t\t\$offset,\n";
                $this->code .= "\t\t\t\t\$limit\n";
                $this->code .= "\t\t\t);\n";
                $this->code .= "\t\t}\n\n";
            }

            // Multi
            foreach ($this->table->getForeignKeys() as $foreignKeyField) {

                // No duplicates
                if (in_array("{$foreignKeyField->getNameTitleCaseWithoutId()}Multi", $used_names)) {
                    continue;
                }
                $used_names[] = "{$foreignKeyField->getNameTitleCaseWithoutId()}Multi";

                // comments
                $selected_fields = [];
                foreach ($this->table->getFields() as $field) {
                    if ($foreignKeyField !== $field) {
                        $selected_fields[] = $field->getName();
                    }
                }

                $this->code .= "\t\t/**\n";
                $this->code .= "\t\t * Get multiple sets of " . self::ander($selected_fields) . " by a collection of {$foreignKeyField->getReferencedField()->getTable()->getNameLabel()}s\n";
                $this->code .= "\t\t *\n";
                $this->code .= "\t\t * @param {$this->namespace}\\{$foreignKeyField->getReferencedField()->getTable()->getNameAsClass()}\\Collection|array \${$foreignKeyField->getReferencedField()->getTable()->getNameCamelCase()}List\n";
                $this->code .= "\t\t * @param array|null \$orderBy array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)\n";
                $this->code .= "\t\t * @param int|null   \$offset get rows starting at this offset\n";
                $this->code .= "\t\t * @param int|null   \$limit max number of rows to return\n";
                $this->code .= "\t\t *\n";
                $this->code .= "\t\t * @return array of result sets containing " . self::ander($selected_fields) . "\n";
                $this->code .= "\t\t */\n";

                // end comments

                $this->code .= "\t\tpublic function by{$foreignKeyField->getNameTitleCaseWithoutId()}Multi(\${$foreignKeyField->getReferencedField()->getTable()->getNameCamelCase()}List, array \$orderBy=null, \$offset=null, \$limit=null) {\n";

                $this->code .= "\t\t\t\$keys = [];\n";

                $this->code .= "\t\t\tif (\${$foreignKeyField->getReferencedField()->getTable()->getNameCamelCase()}List instanceof {$this->namespace}\\{$foreignKeyField->getReferencedField()->getTable()->getNameAsClass()}\\Collection) {\n";

                $this->code .= "\t\t\t\tforeach (\${$foreignKeyField->getReferencedField()->getTable()->getNameCamelCase()}List as \$k => \${$foreignKeyField->getReferencedField()->getTable()->getNameCamelCase()}) {\n";
                $this->code .= "\t\t\t\t\t\$keys[\$k] = [\n";
                if ($foreignKeyField->allowsNull()) {
                    $this->code .= "\t\t\t\t\t\t'{$foreignKeyField->getName()}' => \${$foreignKeyField->getReferencedField()->getTable()->getNameCamelCase()}->get{$foreignKeyField->getReferencedField()->getNameTitleCase()}() === null ? null : ({$foreignKeyField->getReferencedField()->getCasting()}) \${$foreignKeyField->getReferencedField()->getTable()->getNameCamelCase()}->get{$foreignKeyField->getReferencedField()->getNameTitleCase()},\n";
                } else {
                    $this->code .= "\t\t\t\t\t\t'{$foreignKeyField->getName()}' => ({$foreignKeyField->getReferencedField()->getCasting()}) \${$foreignKeyField->getReferencedField()->getTable()->getNameCamelCase()}->get{$foreignKeyField->getReferencedField()->getNameTitleCase()}(),\n";
                }
                $this->code .= "\t\t\t\t\t];\n";
                $this->code .= "\t\t\t\t}\n\n";

                $this->code .= "\t\t\t} else {\n";

                $this->code .= "\t\t\t\tforeach (\${$foreignKeyField->getReferencedField()->getTable()->getNameCamelCase()}List as \$k => \${$foreignKeyField->getReferencedField()->getTable()->getNameCamelCase()}) {\n";
                $this->code .= "\t\t\t\t\t\$keys[\$k] = [\n";
                if ($foreignKeyField->allowsNull()) {
                    $this->code .= "\t\t\t\t\t\t'{$foreignKeyField->getName()}' => \${$foreignKeyField->getReferencedField()->getTable()->getNameCamelCase()} === null ? null : ({$foreignKeyField->getReferencedField()->getCasting()}) \${$foreignKeyField->getReferencedField()->getTable()->getNameCamelCase()},\n";
                } else {
                    $this->code .= "\t\t\t\t\t\t'{$foreignKeyField->getName()}' => ({$foreignKeyField->getReferencedField()->getCasting()}) \${$foreignKeyField->getReferencedField()->getTable()->getNameCamelCase()},\n";
                }
                $this->code .= "\t\t\t\t\t];\n";
                $this->code .= "\t\t\t\t}\n\n";

                $this->code .= "\t\t\t}\n\n";

                $this->code .= "\t\t\treturn parent::_byFieldsMulti(\n";
                $this->code .= "\t\t\t\tself::BY_" . strtoupper($foreignKeyField->getNameWithoutId()) . ",\n";

                // fields selected
                $this->code .= "\t\t\t\t[\n";
                foreach ($this->table->getFields() as $field) {
                    if ($field !== $foreignKeyField) {
                        $this->code .= "\t\t\t\t\t'{$field->getName()}',\n";
                    }
                }
                $this->code .= "\t\t\t\t],\n";

                $this->code .= "\t\t\t\t\$keys,\n";
                $this->code .= "\t\t\t\t\$orderBy,\n";
                $this->code .= "\t\t\t\t\$offset,\n";
                $this->code .= "\t\t\t\t\$limit\n";
                $this->code .= "\t\t\t);\n";
                $this->code .= "\t\t}\n\n";
            }
        }

        protected function insert() {

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Insert {$this->table->getNameLabel()} link, created from an array of \$info\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param array \$info associative array, keys matching columns in database for this Entity\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return bool\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function insert(array \$info) {\n\n";
            $this->code .= "\t\t\treturn parent::_insert(\$info);\n";
            $this->code .= "\t\t}\n\n";
        }

        protected function insertMulti() {

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Insert multiple {$this->table->getNameLabel()} links, created from an array of arrays of \$info\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param array \$infos array of associative arrays, keys matching columns in database for this Entity\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return boolean\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function insertMulti(array \$infos) {\n\n";
            $this->code .= "\t\t\treturn parent::_insertMulti(\$infos);\n";
            $this->code .= "\t\t}\n\n";
        }

        protected function update() {

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Update {$this->table->getNameLabel()} link records based on \$where inputs\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param array \$newInfo the new link record data\n";
            $this->code .= "\t\t * @param array \$where associative array, matching columns with values\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return bool\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function update(array \$newInfo, array \$where) {\n\n";
            $this->code .= "\t\t\t// Update Link\n";
            $this->code .= "\t\t\treturn parent::_update(\$newInfo, \$where);\n";
            $this->code .= "\t\t}\n\n";

        }

        protected function delete() {

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Delete multiple {$this->table->getNameLabel()} link records based on an array of associative arrays\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param array \$keys keys match the column names\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return bool\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function delete(array \$keys) {\n\n";
            $this->code .= "\t\t\t// Delete Link\n";
            $this->code .= "\t\t\treturn parent::_delete(\$keys);\n";
            $this->code .= "\t\t}\n\n";
        }

        protected function deleteMulti() {

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Delete multiple sets of {$this->table->getNameLabel()} link records based on an array of associative arrays\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param array \$keysArr an array of arrays, keys match the column names\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return bool\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function deleteMulti(array \$keysArr) {\n\n";
            $this->code .= "\t\t\t// Delete links\n";
            $this->code .= "\t\t\treturn parent::_deleteMulti(\$keysArr);\n";
            $this->code .= "\t\t}\n";
        }
    }