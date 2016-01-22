<?php

    namespace Neoform\Entity\Generate\Record;

    use Neoform\Entity\Generate;
    use Neoform\Sql\Parser\Field;

    class Collection extends Generate\Collection {

        protected $usedFunctionNames = [];
        protected $usedVarKeyNames = [];

        protected function used(array &$arr, $name) {
            $suffix     = '';
            $final_name = $name;
            $i          = 1;
            while (in_array($final_name, $arr)) {
                $final_name = $name . $suffix;
                $suffix     = $i++;
            }
            $arr[] = $final_name;
            return $final_name;
        }

        public function code() {

            $this->code .= '<?php'."\n\n";
            $this->code .= "\tnamespace {$this->namespace}\\{$this->table->getNameAsClass()};\n\n";

            $this->code .= "\tuse Neoform;\n";
            if ($this->namespace !== "Neoform") {
                $this->code .= "\tuse {$this->namespace};\n\n";
            }

            $this->code .= "\t/**\n";
            $this->code .= "\t * {$this->table->getNameLabel()} collection\n";
            $this->code .= "\t */\n";

            $this->code .= "\tclass Collection extends Neoform\\Entity\\Record\\Collection {\n\n";
            $this->code .= "\t\t// Load entity details into the class\n";
            $this->code .= "\t\tuse Details;\n\n";

            $this->preloaders();

            $this->code = substr($this->code, 0, -1);
            $this->code .= "\t}\n";
        }

        public function preloaders() {

            // many to one relationship (other tables referencing this one as a constraint)
            foreach ($this->table->getReferencingFields() as $referencing_field) {

                /**
                *   User (*id*, name, email) --> User_info (*user_id*, address, birthday)
                */
                if ($referencing_field->isUnique()) {
                    // one to one relationship on inbound references
                    $this->one_to_one($referencing_field->getReferencedField(), $referencing_field);
                } else {

                     /**
                     *   User (*id*, blah, blah) --> User_comments (id, *user_id*, body, posted_on)
                     */
                    if ($referencing_field->getTable()->isRecord()) {

                        // one to many relationship (linking table implicating this one, tying it to another)
                        $this->one_to_many($referencing_field);
                    }

                    // if the referencing field is part of a 2-key unique key, it's a many-to-many
                    if ($referencing_field->isLinkIndex()) {
                        // many to many relationship (linking table implicating this one, tying it to another)
                        $this->many_to_many($referencing_field, $referencing_field->getOtherLinkIndexField());
                    }
                }
            }

            /**
            *   User (*id*, name, email) <-- User_info (*user_id*, address, birthday)
            */
            // one to one relationships on outbound references
            foreach ($this->table->getForeignKeys() as $foreignKey) {
                if ($foreignKey->getTable()->isRecord()) {

                    $this->one_to_one($foreignKey, $foreignKey->getReferencedField());
                }
            }
        }

        // these are all labelled as _collections because that's what they return as a value. :P

        protected function one_to_one(field $field, field $referencedField) {

            $selfReference = $referencedField->getTable() === $this->table;

            $name    = $this->used($this->usedFunctionNames, ($selfReference ? "parent{$referencedField->getTable()->getNameTitleCase()}" : $referencedField->getTable()->getNameCamelCase()) . 'Collection');
            $_varKey = $this->used($this->usedVarKeyNames, $selfReference ? "parent{$referencedField->getTable()->getNameTitleCase()}" : $referencedField->getTable()->getNameCamelCase());

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Preload the {$referencedField->getTable()->getNameLabel()} models in this collection\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return {$this->namespace}\\{$referencedField->getTable()->getNameAsClass()}\\Collection\n";
            $this->code .= "\t\t * @deprecated\n";
            $this->code .= "\t\t */\n";
            $this->code .= "\t\tpublic function {$name}() {\n";
            $this->code .= "\t\t\treturn \$this->_preloadOneToOne(\n";
            $this->code .= "\t\t\t\t'{$_varKey}',\n";
            $this->code .= "\t\t\t\t'{$referencedField->getTable()->getNameAsClass()}',\n";
            $this->code .= "\t\t\t\t'get{$field->getNameTitleCase()}'\n";
            $this->code .= "\t\t\t);\n";
            $this->code .= "\t\t}\n\n";

            $name = $this->used($this->usedFunctionNames, ($selfReference ? "Parent" : '') . "{$referencedField->getTable()->getNameTitleCase()}Collection");

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Preload the {$referencedField->getTable()->getNameLabel()} models in this collection\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return {$this->namespace}\\{$referencedField->getTable()->getNameAsClass()}\\Collection\n";
            $this->code .= "\t\t */\n";
            $this->code .= "\t\tpublic function get{$name}() {\n";
            $this->code .= "\t\t\treturn \$this->_preloadOneToOne(\n";
            $this->code .= "\t\t\t\t'{$_varKey}',\n";
            $this->code .= "\t\t\t\t'{$this->namespace}\\{$referencedField->getTable()->getNameAsClass()}',\n";
            $this->code .= "\t\t\t\t'get{$field->getNameTitleCase()}'\n";
            $this->code .= "\t\t\t);\n";
            $this->code .= "\t\t}\n\n";
        }

        protected function one_to_many(field $field) {

            $selfReference = $field->getTable() === $this->table;

            // Collection
            $name    = $this->used($this->usedFunctionNames, ($selfReference ? "child{$field->getTable()->getNameTitleCase()}" : $field->getTable()->getNameCamelCase()) . 'Collection');
            $_varKey = $this->used($this->usedVarKeyNames, ($selfReference ? "child{$field->getTable()->getNameTitleCase()}" : $field->getTable()->getNameCamelCase()) . 'Collection');

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Preload the " . ($selfReference ? 'child ' : '') . "{$field->getTable()->getNameLabel()} models in this collection\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param array|null \$orderBy array of field names (as the key) and sort direction (Entity\\Record_dao::SORT_ASC, Entity\\Record_dao::SORT_DESC)\n";
            $this->code .= "\t\t * @param int|null   \$offset get PKs starting at this offset\n";
            $this->code .= "\t\t * @param int|null   \$limit max number of PKs to return\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return {$this->namespace}\\{$field->getTable()->getNameAsClass()}\\Collection\n";
            $this->code .= "\t\t * @deprecated\n";
            $this->code .= "\t\t */\n";
            $this->code .= "\t\tpublic function {$name}(array \$orderBy=null, \$offset=null, \$limit=null) {\n";
            $this->code .= "\t\t\treturn \$this->_preloadOneToMany(\n";
            $this->code .= "\t\t\t\t'{$_varKey}',\n";
            $this->code .= "\t\t\t\t'{$this->namespace}\\{$field->getTable()->getNameAsClass()}',\n";
            $this->code .= "\t\t\t\t'by{$field->getNameTitleCaseWithoutId()}',\n";
            $this->code .= "\t\t\t\t\$orderBy,\n";
            $this->code .= "\t\t\t\t\$offset,\n";
            $this->code .= "\t\t\t\t\$limit\n";
            $this->code .= "\t\t\t);\n";
            $this->code .= "\t\t}\n\n";

            $name = $this->used($this->usedFunctionNames, ($selfReference ? "Child" : '') . "{$field->getTable()->getNameTitleCase()}Collection");

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Preload the " . ($selfReference ? 'child ' : '') . "{$field->getTable()->getNameLabel()} models in this collection\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param array|null \$orderBy array of field names (as the key) and sort direction (Entity\\Record_dao::SORT_ASC, Entity\\Record_dao::SORT_DESC)\n";
            $this->code .= "\t\t * @param int|null   \$offset get PKs starting at this offset\n";
            $this->code .= "\t\t * @param int|null   \$limit max number of PKs to return\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return {$this->namespace}\\{$field->getTable()->getNameAsClass()}\\Collection\n";
            $this->code .= "\t\t */\n";
            $this->code .= "\t\tpublic function get{$name}(array \$orderBy=null, \$offset=null, \$limit=null) {\n";
            $this->code .= "\t\t\treturn \$this->_preloadOneToMany(\n";
            $this->code .= "\t\t\t\t'{$_varKey}',\n";
            $this->code .= "\t\t\t\t'{$this->namespace}\\{$field->getTable()->getNameAsClass()}',\n";
            $this->code .= "\t\t\t\t'by{$field->getNameTitleCaseWithoutId()}',\n";
            $this->code .= "\t\t\t\t\$orderBy,\n";
            $this->code .= "\t\t\t\t\$offset,\n";
            $this->code .= "\t\t\t\t\$limit\n";
            $this->code .= "\t\t\t);\n";
            $this->code .= "\t\t}\n\n";

            // Count
            $name     = $this->used($this->usedFunctionNames, ($selfReference ? "child{$field->getTable()->getNameTitleCase()}" : $field->getTable()->getNameCamelCase()) . "Count");
            $_varKey = $this->used($this->usedVarKeyNames, ($selfReference ? "child{$field->getTable()->getNameTitleCase()}" : $field->getTable()->getNameCamelCase()) . "Count");

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Preload the " . ($selfReference ? 'child ' : '') . "{$field->getTable()->getNameLabel()} counts\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return array counts\n";
            $this->code .= "\t\t * @deprecated\n";
            $this->code .= "\t\t */\n";
            $this->code .= "\t\tpublic function {$name}() {\n";
            $this->code .= "\t\t\treturn \$this->_preloadCounts(\n";
            $this->code .= "\t\t\t\t'{$_varKey}',\n";
            $this->code .= "\t\t\t\t'{$this->namespace}\\{$field->getTable()->getNameAsClass()}',\n";
            $this->code .= "\t\t\t\t'{$field->getName()}'\n";
            $this->code .= "\t\t\t);\n";
            $this->code .= "\t\t}\n\n";

            $name = $this->used($this->usedFunctionNames, ($selfReference ? "child" : '') . "{$field->getTable()->getNameTitleCase()}Count");

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Preload the " . ($selfReference ? 'child ' : '') . "{$field->getTable()->getNameLabel()} counts\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return array counts\n";
            $this->code .= "\t\t */\n";
            $this->code .= "\t\tpublic function get{$name}() {\n";
            $this->code .= "\t\t\treturn \$this->_preloadCounts(\n";
            $this->code .= "\t\t\t\t'{$_varKey}',\n";
            $this->code .= "\t\t\t\t'{$this->namespace}\\{$field->getTable()->getNameAsClass()}',\n";
            $this->code .= "\t\t\t\t'{$field->getName()}'\n";
            $this->code .= "\t\t\t);\n";
            $this->code .= "\t\t}\n\n";
        }

        protected function many_to_many(field $field, field $referencedField) {

            // Collection
            $name    = $this->used($this->usedFunctionNames, "{$referencedField->getReferencedField()->getTable()->getNameCamelCase()}Collection");
            $_varKey = $this->used($this->usedVarKeyNames, "{$referencedField->getReferencedField()->getTable()->getNameCamelCase()}Collection");

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Preload the {$referencedField->getReferencedField()->getTable()->getNameLabel()} models in this collection\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param array    \$orderBy array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)\n";
            $this->code .= "\t\t * @param int|null \$offset  get PKs starting at this offset\n";
            $this->code .= "\t\t * @param int|null \$limit   max number of PKs to return\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return {$this->namespace}\\{$referencedField->getReferencedField()->getTable()->getNameAsClass()}\\Collection\n";
            $this->code .= "\t\t * @deprecated\n";
            $this->code .= "\t\t */\n";
            $this->code .= "\t\tpublic function {$name}(array \$orderBy=null, \$offset=null, \$limit=null) {\n";
            $this->code .= "\t\t\treturn \$this->_preloadManyToMany(\n";
            $this->code .= "\t\t\t\t'{$_varKey}',\n";
            $this->code .= "\t\t\t\t'{$this->namespace}\\{$field->getTable()->getNameAsClass()}',\n";
            $this->code .= "\t\t\t\t'by{$field->getNameTitleCaseWithoutId()}',\n";
            $this->code .= "\t\t\t\t'{$this->namespace}\\{$referencedField->getReferencedField()->getTable()->getNameAsClass()}',\n";
            $this->code .= "\t\t\t\t\$orderBy,\n";
            $this->code .= "\t\t\t\t\$offset,\n";
            $this->code .= "\t\t\t\t\$limit\n";
            $this->code .= "\t\t\t);\n";
            $this->code .= "\t\t}\n\n";

            $name = $this->used($this->usedFunctionNames, "{$referencedField->getReferencedField()->getTable()->getNameTitleCase()}Collection");

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Preload the {$referencedField->getReferencedField()->getTable()->getNameLabel()} models in this collection\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param array    \$orderBy array of field names (as the key) and sort direction (parent::SORT_ASC, parent::SORT_DESC)\n";
            $this->code .= "\t\t * @param int|null \$offset  get PKs starting at this offset\n";
            $this->code .= "\t\t * @param int|null \$limit   max number of PKs to return\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return {$this->namespace}\\{$referencedField->getReferencedField()->getTable()->getNameAsClass()}\\Collection\n";
            $this->code .= "\t\t */\n";
            $this->code .= "\t\tpublic function get{$name}(array \$orderBy=null, \$offset=null, \$limit=null) {\n";
            $this->code .= "\t\t\treturn \$this->_preloadManyToMany(\n";
            $this->code .= "\t\t\t\t'{$_varKey}',\n";
            $this->code .= "\t\t\t\t'{$this->namespace}\\{$field->getTable()->getNameAsClass()}',\n";
            $this->code .= "\t\t\t\t'by{$field->getNameTitleCaseWithoutId()}',\n";
            $this->code .= "\t\t\t\t'{$this->namespace}\\{$referencedField->getReferencedField()->getTable()->getNameAsClass()}',\n";
            $this->code .= "\t\t\t\t\$orderBy,\n";
            $this->code .= "\t\t\t\t\$offset,\n";
            $this->code .= "\t\t\t\t\$limit\n";
            $this->code .= "\t\t\t);\n";
            $this->code .= "\t\t}\n\n";

            // Count
            $name    = $this->used($this->usedFunctionNames, "{$referencedField->getReferencedField()->getTable()->getNameCamelCase()}Count");
            $_varKey = $this->used($this->usedVarKeyNames, "{$referencedField->getReferencedField()->getTable()->getNameCamelCase()}Count");

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Preload the {$referencedField->getReferencedField()->getTable()->getNameLabel()} counts\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return array counts\n";
            $this->code .= "\t\t */\n";
            $this->code .= "\t\tpublic function {$name}() {\n";
            $this->code .= "\t\t\treturn \$this->_preloadCounts(\n";
            $this->code .= "\t\t * @deprecated\n";
            $this->code .= "\t\t\t\t'{$_varKey}',\n";
            $this->code .= "\t\t\t\t'{$this->namespace}\\{$field->getTable()->getNameAsClass()}',\n";
            $this->code .= "\t\t\t\t'{$field->getName()}'\n";
            $this->code .= "\t\t\t);\n";
            $this->code .= "\t\t}\n\n";

            $name = $this->used($this->usedFunctionNames, "{$referencedField->getReferencedField()->getTable()->getNameTitleCase()}Count");

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Preload the {$referencedField->getReferencedField()->getTable()->getNameLabel()} counts\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return array counts\n";
            $this->code .= "\t\t */\n";
            $this->code .= "\t\tpublic function get{$name}() {\n";
            $this->code .= "\t\t\treturn \$this->_preloadCounts(\n";
            $this->code .= "\t\t\t\t'{$_varKey}',\n";
            $this->code .= "\t\t\t\t'{$this->namespace}\\{$field->getTable()->getNameAsClass()}',\n";
            $this->code .= "\t\t\t\t'{$field->getName()}'\n";
            $this->code .= "\t\t\t);\n";
            $this->code .= "\t\t}\n\n";
        }
    }