<?php

    namespace Neoform\Entity\Generate\Record;

    use Neoform\Entity\Generate;

    class Dao extends Generate\Dao {

        public function code() {

            // Code
            $this->code .= '<?php'."\n\n";
            $this->code .= "\tnamespace {$this->namespace}\\{$this->table->getNameAsClass()};\n\n";

            $this->code .= "\tuse Neoform;\n";
            if ($this->namespace !== "Neoform") {
                $this->code .= "\tuse {$this->namespace};\n\n";
            }

            $this->code .= "\t/**\n";
            $this->code .= "\t * {$this->table->getNameLabel()} DAO\n";
            $this->code .= "\t */\n";

            $this->code .= "\tclass Dao extends Neoform\\Entity\\Record\\Dao {\n\n";
            $this->code .= "\t\t// Load entity details into the class\n";
            $this->code .= "\t\tuse Details;\n\n";

            $this->constants();
            $this->bindings();

            if (count($this->table->getAllNonPkIndexes())) {
                $this->code .= "\t\t// READS\n\n";
                $this->selectors();
            }

            $this->code .= "\t\t// WRITES\n\n";

            $this->insert();
            $this->insertMulti();
            $this->update();
            $this->delete();
            $this->deleteMulti();

            $this->code .= "\t}\n";
        }

        protected function constants() {

            $usedNames = [];

            $longest_part = $this->table->longestNonPkIndexCombinations();

            foreach ($this->table->getAllNonPkIndexCombinations() as $keys => $fields) {

                // No duplicates
                if (in_array(strtolower($keys), $usedNames)) {
                    continue;
                }
                $usedNames[]   = strtolower($keys);
                $nameTitleCase = str_replace(' ', '', ucwords(str_replace('_', ' ', $keys)));

                $this->code .= "\t\tconst " . str_pad('BY_' . strtoupper($keys), $longest_part + 3) . " = 'by{$nameTitleCase}';\n";
            }

            $this->code .= "\n";
        }

        protected function selectors() {

            $usedNames = [];

            foreach ($this->table->getAllNonUniqueIndexes() as $index) {

                $vars       = [];
                $names      = [];
                $fields     = [];
                $namesTCase = [];
                $namesConst = [];

                foreach ($index as $indexField) {

                    if (! $indexField->isFieldLookupable()) {
                        continue;
                    }

                    $fields[]     = $indexField;
                    $vars[]       = "\${$indexField->getNameCamelCase()}";
                    $names[]      = $indexField->getNameWithoutId();
                    $name         = join($names);
                    $namesTCase[] = $indexField->getNameTitleCaseWithoutId();
                    $nameTCase    = join($namesTCase);
                    $namesConst[] = $indexField->getNameWithoutId();
                    $nameConst    = strtoupper(join('_', $namesConst));

                    // No duplicates
                    if (in_array($name, $usedNames)) {
                        continue;
                    }
                    $usedNames[] = $name;

                    // Generate code
                    $this->code .= "\t\t/**\n";
                    $this->code .= "\t\t * Get {$this->table->getNameLabel()} {$this->table->getPrimaryKey()->getNameLabel()}s by " . self::ander($names) . "\n";
                    $this->code .= "\t\t *\n";
                    foreach ($fields as $field) {
                        $this->code .= "\t\t * @param {$field->getCasting()}   \${$field->getNameCamelCase()}\n";
                    }
                    $this->code .= "\t\t * @param array    \$orderBy array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)\n";
                    $this->code .= "\t\t * @param int|null \$offset get PKs starting at this offset\n";
                    $this->code .= "\t\t * @param int|null \$limit max number of PKs to return\n";
                    $this->code .= "\t\t *\n";
                    $this->code .= "\t\t * @return array of {$this->table->getNameLabel()} {$this->table->getPrimaryKey()->getNameLabel()}s\n";
                    $this->code .= "\t\t */\n";

                    $this->code .= "\t\tpublic function by{$nameTCase}(" . join(', ', $vars) . ", array \$orderBy=null, \$offset=null, \$limit=null) {\n";

                    $this->code .= "\t\t\treturn parent::_byFields(\n";
                    $this->code .= "\t\t\t\tself::BY_{$nameConst},\n";
                    $this->code .= "\t\t\t\t[\n";
                    $longest_part = $this->longestLength($fields, false, true);
                    foreach ($fields as $field) {
                        if ($field->allowsNull()) {
                            $this->code .= "\t\t\t\t\t'" . str_pad("{$field->getName()}'", $longest_part + 1) . " => \${$field->getNameCamelCase()} === null ? null : ({$field->getCasting()}) \${$field->getNameCamelCase()},\n";
                        } else {
                            $this->code .= "\t\t\t\t\t'" . str_pad("{$field->getName()}'", $longest_part + 1) . " => ({$field->getCasting()}) \${$field->getNameCamelCase()},\n";
                        }
                    }
                    $this->code .= "\t\t\t\t],\n";
                    $this->code .= "\t\t\t\t\$orderBy,\n";
                    $this->code .= "\t\t\t\t\$offset,\n";
                    $this->code .= "\t\t\t\t\$limit\n";
                    $this->code .= "\t\t\t);\n";

                    $this->code .= "\t\t}\n\n";
                }
            }

            foreach ($this->table->getUniqueKeys() as $index) {

                $vars       = [];
                $names      = [];
                $fields     = [];
                $namesTCase = [];
                $namesConst = [];

                foreach ($index as $indexField) {

                    if (! $indexField->isFieldLookupable()) {
                        continue;
                    }

                    $fields[]     = $indexField;
                    $vars[]       = "\${$indexField->getNameCamelCase()}";
                    $names[]      = $indexField->getNameWithoutId();
                    $name         = join($names);
                    $namesTCase[] = $indexField->getNameTitleCaseWithoutId();
                    $nameTCase    = join($namesTCase);
                    $namesConst[] = $indexField->getNameWithoutId();
                    $nameConst    = strtoupper(join('_', $namesConst));

                    // No duplicates
                    if (in_array($name, $usedNames)) {
                        continue;
                    }
                    $usedNames[] = $name;

                    // Generate code
                    $this->code .= "\t\t/**\n";
                    $this->code .= "\t\t * Get {$this->table->getNameLabel()} {$this->table->getPrimaryKey()->getNameLabel()}s by " . self::ander($names) . "\n";
                    $this->code .= "\t\t *\n";
                    foreach ($fields as $field) {
                        $this->code .= "\t\t * @param {$field->getCasting()} \${$field->getNameCamelCase()}\n";
                    }
                    $this->code .= "\t\t *\n";
                    $this->code .= "\t\t * @return array of {$this->table->getNameLabel()} {$this->table->getPrimaryKey()->getNameLabel()}s\n";
                    $this->code .= "\t\t */\n";

                    $this->code .= "\t\tpublic function by{$nameTCase}(" . join(', ', $vars) . ") {\n";
                    $this->code .= "\t\t\treturn parent::_byFields(\n";
                    $this->code .= "\t\t\t\tself::BY_{$nameConst},\n";
                    $this->code .= "\t\t\t\t[\n";
                    $longest_part = $this->longestLength($fields, false, true);
                    foreach ($fields as $field) {
                        if ($field->allowsNull()) {
                            $this->code .= "\t\t\t\t\t'" . str_pad("{$field->getName()}'", $longest_part + 1) . " => \${$field->getNameCamelCase()} === null ? null : ({$field->getCasting()}) \${$field->getNameCamelCase()},\n";
                        } else {
                            $this->code .= "\t\t\t\t\t'" . str_pad("{$field->getName()}'", $longest_part + 1) . " => ({$field->getCasting()}) \${$field->getNameCamelCase()},\n";
                        }
                    }
                    $this->code .= "\t\t\t\t]\n";
                    $this->code .= "\t\t\t);\n";
                    $this->code .= "\t\t}\n\n";
                }
            }


            // Multi - applies only to foreign keys
            foreach ($this->table->getForeignKeys() as $field) {

                // No duplicates
                if (in_array($field->getNameCamelCaseWithoutId() . 'Multi', $usedNames)) {
                    continue;
                }
                $usedNames[] = $field->getNameCamelCaseWithoutId() . 'Multi';

                $this->code .= "\t\t/**\n";
                $this->code .= "\t\t * Get multiple sets of {$this->table->getNameLabel()} {$this->table->getPrimaryKey()->getNameLabel()}s by {$field->getReferencedField()->getTable()->getName()}\n";
                $this->code .= "\t\t *\n";
                $this->code .= "\t\t * @param {$this->namespace}\\{$field->getReferencedField()->getTable()->getNameAsClass()}\\Collection|array \${$field->getReferencedField()->getTable()->getNameCamelCase()}List\n";
                $this->code .= "\t\t * @param array|null \$orderBy array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)\n";
                $this->code .= "\t\t * @param int|null   \$offset get PKs starting at this offset\n";
                $this->code .= "\t\t * @param int|null   \$limit max number of PKs to return\n";
                $this->code .= "\t\t *\n";
                $this->code .= "\t\t * @return array of arrays containing " . ucwords(str_replace('_', ' ', $this->table->getName())) . " {$this->table->getPrimaryKey()->getName()}s\n";
                $this->code .= "\t\t */\n";

                $this->code .= "\t\tpublic function by{$field->getNameTitleCaseWithoutId()}Multi(\${$field->getReferencedField()->getTable()->getNameCamelCase()}List, array \$orderBy=null, \$offset=null, \$limit=null) {\n";
                $this->code .= "\t\t\t\$keys = [];\n";

                $this->code .= "\t\t\tif (\${$field->getReferencedField()->getTable()->getNameCamelCase()}List instanceof {$this->namespace}\\{$field->getReferencedField()->getTable()->getNameAsClass()}\\Collection) {\n";

                $this->code .= "\t\t\t\tforeach (\${$field->getReferencedField()->getTable()->getNameCamelCase()}List as \$k => \${$field->getReferencedField()->getTable()->getNameCamelCase()}) {\n";
                $this->code .= "\t\t\t\t\t\$keys[\$k] = [\n";
                if ($field->allowsNull()) {
                    $this->code .= "\t\t\t\t\t\t'{$field->getName()}' => \${$field->getReferencedField()->getTable()->getNameCamelCase()}->get{$field->getReferencedField()->getNameTitleCase()}() === null ? null : ({$field->getCasting()}) \${$field->getReferencedField()->getTable()->getNameCamelCase()}->get{$field->getReferencedField()->getNameTitleCase()}(),\n";
                } else {
                    $this->code .= "\t\t\t\t\t\t'{$field->getName()}' => ({$field->getCasting()}) \${$field->getReferencedField()->getTable()->getNameCamelCase()}->get{$field->getReferencedField()->getNameTitleCase()}(),\n";
                }
                $this->code .= "\t\t\t\t\t];\n";
                $this->code .= "\t\t\t\t}\n";

                $this->code .= "\t\t\t} else {\n";

                $this->code .= "\t\t\t\tforeach (\${$field->getReferencedField()->getTable()->getNameCamelCase()}List as \$k => \${$field->getReferencedField()->getTable()->getNameCamelCase()}) {\n";
                $this->code .= "\t\t\t\t\t\$keys[\$k] = [\n";
                if ($field->allowsNull()) {
                    $this->code .= "\t\t\t\t\t\t'{$field->getName()}' => \${$field->getReferencedField()->getTable()->getNameCamelCase()} === null ? null : ({$field->getCasting()}) \${$field->getReferencedField()->getTable()->getNameCamelCase()},\n";
                } else {
                    $this->code .= "\t\t\t\t\t\t'{$field->getName()}' => ({$field->getCasting()}) \${$field->getReferencedField()->getTable()->getNameCamelCase()},\n";
                }
                $this->code .= "\t\t\t\t\t];\n";
                $this->code .= "\t\t\t\t}\n";

                $this->code .= "\t\t\t}\n";

                $this->code .= "\t\t\treturn parent::_byFieldsMulti(\n";
                $this->code .= "\t\t\t\tself::BY_" . strtoupper($field->getNameWithoutId()) . ",\n";
                $this->code .= "\t\t\t\t\$keys,\n";
                $this->code .= "\t\t\t\t\$orderBy,\n";
                $this->code .= "\t\t\t\t\$offset,\n";
                $this->code .= "\t\t\t\t\$limit\n";
                $this->code .= "\t\t\t);\n";

                $this->code .= "\t\t}\n\n";
            }

            // Multi lookups on all other indexes that are not foreign keys
            foreach ($this->table->getAllNonUniqueIndexes() as $index) {

                $vars       = [];
                $names      = [];
                $fields     = [];
                $fieldNames = [];
                $namesTCase = [];
                $namesConst = [];

                foreach ($index as $indexField) {

                    if (! $indexField->isFieldLookupable()) {
                        continue;
                    }

                    $fields[]     = $indexField;
                    $vars[]       = 'array $' . $indexField->getNameCamelCase();
                    $names[]      = $names ? $indexField->getNameTitleCaseWithoutId() : $indexField->getNameCamelCaseWithoutId();
                    $fieldNames[] = $indexField->getName() . "s";
                    $name         = join($names);
                    $namesTCase[] = $indexField->getNameTitleCaseWithoutId();
                    $nameTCase    = join($namesTCase);
                    $namesConst[] = $indexField->getNameWithoutId();
                    $nameConst    = strtoupper(join('_', $namesConst));

                    // No duplicates
                    if (in_array($name . 'Multi', $usedNames)) {
                        continue;
                    }
                    $usedNames[] = $name . 'Multi';

                    // Generate code
                    $this->code .= "\t\t/**\n";
                    $this->code .= "\t\t * Get {$this->table->getNameLabel()} {$this->table->getPrimaryKey()->getNameCamelCase()}Arr by an array of " . self::ander($names) . "s\n";
                    $this->code .= "\t\t *\n";
                    if (count($fieldNames) === 1) {
                        $this->code .= "\t\t * @param array      \${$name}Arr an array containing " . self::ander($fieldNames) . "\n";
                    } else {
                        $this->code .= "\t\t * @param array      \${$name}Arr an array of arrays containing " . self::ander($fieldNames) . "\n";
                    }
                    $this->code .= "\t\t * @param array|null \$orderBy array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)\n";
                    $this->code .= "\t\t * @param int|null   \$offset get PKs starting at this offset\n";
                    $this->code .= "\t\t * @param int|null   \$limit max number of PKs to return\n";
                    $this->code .= "\t\t *\n";
                    $this->code .= "\t\t * @return array of arrays of {$this->table->getNameLabel()} {$this->table->getPrimaryKey()->getName()}s\n";
                    $this->code .= "\t\t */\n";

                    $this->code .= "\t\tpublic function by{$nameTCase}Multi(array \${$name}Arr, array \$orderBy=null, \$offset=null, \$limit=null) {\n";
                    $this->code .= "\t\t\t\$keysArr = [];\n";
                    $this->code .= "\t\t\tforeach (\${$name}Arr as \$k => \${$name}) {\n";
                    if (count($fields) === 1) {
                        $this->code .= "\t\t\t\t\$keysArr[\$k] = [ '{$indexField->getName()}' => ({$indexField->getCasting()}) \${$name}, ];\n";
                    } else {
                        $this->code .= "\t\t\t\t\$keysArr[\$k] = [\n";
                        $longest_part = $this->longestLength($fields, false, true);
                        foreach ($fields as $field) {
                            $this->code .= "\t\t\t\t\t'" . str_pad("{$field->getName()}'", $longest_part + 1) . " => ({$field->getCasting()}) \${$name}['{$field->getName()}'],\n";
                        }
                        $this->code .= "\t\t\t\t];\n";
                    }
                    $this->code .= "\t\t\t}\n";

                    $this->code .= "\t\t\treturn parent::_byFieldsMulti(\n";
                    $this->code .= "\t\t\t\tself::BY_{$nameConst},\n";
                    $this->code .= "\t\t\t\t\$keysArr,\n";
                    $this->code .= "\t\t\t\t\$orderBy,\n";
                    $this->code .= "\t\t\t\t\$offset,\n";
                    $this->code .= "\t\t\t\t\$limit\n";
                    $this->code .= "\t\t\t);\n";

                    $this->code .= "\t\t}\n\n";
                }
            }

            // Multi lookups on all other indexes that are not foreign keys
            foreach ($this->table->getUniqueKeys() as $index) {

                $vars       = [];
                $names      = [];
                $fields     = [];
                $fieldNames = [];
                $namesTCase = [];
                $namesConst = [];

                foreach ($index as $indexField) {

                    if (! $indexField->isFieldLookupable()) {
                        continue;
                    }

                    $fields[]     = $indexField;
                    $vars[]       = "array \${$indexField->getNameCamelCase()}";
                    $fieldNames[] = "{$indexField->getNameLabel()}s";
                    $names[]      = $names ? $indexField->getNameTitleCaseWithoutId() : $indexField->getNameCamelCaseWithoutId();
                    $name         = join($names);
                    $namesTCase[] = $indexField->getNameTitleCaseWithoutId();
                    $nameTCase    = join($namesTCase);
                    $namesConst[] = $indexField->getNameWithoutId();
                    $nameConst    = strtoupper(join('_', $namesConst));

                    // No duplicates
                    if (in_array($name . 'Multi', $usedNames)) {
                        continue;
                    }
                    $usedNames[] = $name . 'Multi';

                    // Generate code
                    $this->code .= "\t\t/**\n";
                    $this->code .= "\t\t * Get {$this->table->getNameLabel()} {$this->table->getPrimaryKey()->getNameLabel()}Arr by an array of " . self::ander($names) . "s\n";
                    $this->code .= "\t\t *\n";
                    if (count($fieldNames) === 1) {
                        $this->code .= "\t\t * @param array \${$name}Arr an array containing " . self::ander($fieldNames) . "\n";
                    } else {
                        $this->code .= "\t\t * @param array \${$name}Arr an array of arrays containing " . self::ander($fieldNames) . "\n";
                    }
                    $this->code .= "\t\t *\n";
                    $this->code .= "\t\t * @return array of arrays of {$this->table->getNameLabel()} {$this->table->getPrimaryKey()->getNameCamelCase()}s\n";
                    $this->code .= "\t\t */\n";

                    $this->code .= "\t\tpublic function by{$nameTCase}Multi(array \${$name}Arr) {\n";
                    $this->code .= "\t\t\t\$keysArr = [];\n";
                    $this->code .= "\t\t\tforeach (\${$name}Arr as \$k => \${$name}) {\n";
                    if (count($fields) === 1) {
                        $this->code .= "\t\t\t\t\$keysArr[\$k] = [ '{$indexField->getName()}' => ({$indexField->getCasting()}) \${$name}, ];\n";
                    } else {
                        $this->code .= "\t\t\t\t\$keysArr[\$k] = [\n";
                        $longest_part = $this->longestLength($fields, false, true);
                        foreach ($fields as $field) {
                            $this->code .= "\t\t\t\t\t'" . str_pad("{$field->getName()}'", $longest_part + 1) . " => ({$field->getCasting()}) \${$name}['{$field->getName()}'],\n";
                        }
                        $this->code .= "\t\t\t\t];\n";
                    }
                    $this->code .= "\t\t\t}\n";

                    $this->code .= "\t\t\treturn parent::_byFieldsMulti(\n";
                    $this->code .= "\t\t\t\tself::BY_{$nameConst},\n";
                    $this->code .= "\t\t\t\t\$keysArr\n";
                    $this->code .= "\t\t\t);\n";

                    $this->code .= "\t\t}\n\n";
                }
            }
        }

        protected function insert() {

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Insert {$this->table->getNameLabel()} record, created from an array of \$info\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param array \$info associative array, keys matching columns in database for this Entity\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return Model\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function insert(array \$info) {\n\n";
            $this->code .= "\t\t\t// Insert Record\n";
            $this->code .= "\t\t\treturn parent::_insert(\$info);\n";
            $this->code .= "\t\t}\n\n";
        }

        protected function insertMulti() {

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Insert multiple {$this->table->getNameLabel()} records, created from an array of arrays of \$info\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param array \$infos array of associative arrays, keys matching columns in database for this Entity\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return Collection\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function insertMulti(array \$infos) {\n\n";
            $this->code .= "\t\t\t// Insert Record\n";
            $this->code .= "\t\t\treturn parent::_insertMulti(\$infos);\n";
            $this->code .= "\t\t}\n\n";
        }

        protected function update() {

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Updates a {$this->table->getNameLabel()} record with new data\n";
            $this->code .= "\t\t *   only fields that are specified in the \$info array will be written\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param Model \${$this->table->getNameCamelCase()} record to be updated\n";
            $this->code .= "\t\t * @param array \$info data to write to the Record\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return Model updated model\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function update(Model \${$this->table->getNameCamelCase()}, array \$info) {\n\n";
            $this->code .= "\t\t\t// Update Record\n";
            $this->code .= "\t\t\treturn parent::_update(\${$this->table->getNameCamelCase()}, \$info);\n";
            $this->code .= "\t\t}\n\n";
        }

        protected function delete() {

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Delete a {$this->table->getNameLabel()} Record\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param Model \${$this->table->getNameCamelCase()} record to be deleted\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return bool\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function delete(Model \${$this->table->getNameCamelCase()}) {\n\n";
            $this->code .= "\t\t\t// Delete Record\n";
            $this->code .= "\t\t\treturn parent::_delete(\${$this->table->getNameCamelCase()});\n";
            $this->code .= "\t\t}\n\n";
        }

        protected function deleteMulti() {

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Delete multiple {$this->table->getNameLabel()} records\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param Collection \${$this->table->getNameCamelCase()}Collection records to be deleted\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return bool\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function deleteMulti(Collection \${$this->table->getNameCamelCase()}Collection) {\n\n";
            $this->code .= "\t\t\t// Delete records\n";
            $this->code .= "\t\t\treturn parent::_deleteMulti(\${$this->table->getNameCamelCase()}Collection);\n";
            $this->code .= "\t\t}\n";
        }
    }