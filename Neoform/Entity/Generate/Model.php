<?php

    namespace Neoform\Entity\Generate;

    use Neoform\Sql\Parser\Field;
    use Neoform\Entity\Generate;

    abstract class Model extends Generate {

        /**
         * @var string[]
         */
        protected $usedFunctionNames = [];

        /**
         * @param string $name
         *
         * @return string
         */
        protected function used($name) {
            $suffix     = '';
            $final_name = $name;
            $i          = 1;
            while (in_array($final_name, $this->usedFunctionNames)) {
                $final_name = $name . $suffix;
                $suffix     = $i++;
            }
            $this->usedFunctionNames[] = $final_name;
            return $final_name;
        }

        public function get($functionName='__get') {

//            $enum_values = [
//                "'yes','no'"     => 'yes',
//                "'no','yes'"     => 'yes',
//                "'true','false'" => 'true',
//                "'false','true'" => 'true',
//            ];

            $ints    = [];
            $floats  = [];
            $dates   = [];
            $bools   = [];
            $strings = [];

            foreach ($this->table->getFields() as $field) {
                switch ($field->getCastingExtended()) {
                    case 'int':
                    case 'integer':
                        $ints[] = $field;
                        break;

                    case 'float':
                        $floats[] = $field;
                        break;

                    case 'date':
                    case 'datetime':
                        $dates[] = $field;
                        break;

                    case 'bool':
                    case 'boolean':
                        $bools[] = $field;
                        break;

                    default:
                        $strings[] = $field;
                        break;
                }
            }

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Get field data\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return mixed\n";
            if ($functionName === '__get') {
                $this->code .= "\t\t * @deprecated\n";
            }
            $this->code .= "\t\t */\n";
            $this->code .= "\t\tpublic function {$functionName}(\$k) {\n\n";
            $this->code .= "\t\t\tif (isset(\$this->vars[\$k])) {\n";
            $this->code .= "\t\t\t\tswitch (\$k) {\n";

            // INTS
            if (count($ints)) {
                $this->code .= "\t\t\t\t\t// integers\n";
                foreach ($ints as $int) {
                    $this->code .= "\t\t\t\t\tcase '{$int->getName()}':\n";
                }
                $this->code .= "\t\t\t\t\t\treturn (int) \$this->vars[\$k];\n\n";
            }

            // FLOATS
            if (count($floats)) {
                $this->code .= "\t\t\t\t\t// floats\n";
                foreach ($floats as $float) {
                    $this->code .= "\t\t\t\t\tcase '{$float->getName()}':\n";
                }
                $this->code .= "\t\t\t\t\t\treturn (float) \$this->vars[\$k];\n\n";
            }

            // BOOLS
            if (count($bools)) {
                $this->code .= "\t\t\t\t\t// booleans\n";
                foreach ($bools as $bool) {
                    $this->code .= "\t\t\t\t\tcase '{$bool->getName()}':\n";
                    $this->code .= "\t\t\t\t\t\treturn \$this->vars[\$k] === '{$bool->getBoolTrueValue()}';\n\n";
                }
            }

            // DATES
            if (count($dates)) {
                $this->code .= "\t\t\t\t\t// dates\n";
                foreach ($dates as $date) {
                    $this->code .= "\t\t\t\t\tcase '{$date->getName()}':\n";
                }
                $this->code .= "\t\t\t\t\t\treturn \$this->_model(\$k, \$this->vars[\$k], 'Neoform\\Type\\Date');\n\n";
            }

            // STRINGS
            if (count($strings)) {
                $this->code .= "\t\t\t\t\t// strings\n";
                foreach ($strings as $string) {
                    $this->code .= "\t\t\t\t\tcase '{$string->getName()}':\n";
                }
                $this->code .= "\t\t\t\t\t\treturn (string) \$this->vars[\$k];\n\n";
            }

            $this->code .= "\t\t\t\t\tdefault:\n";
            $this->code .= "\t\t\t\t\t\treturn \$this->vars[\$k];\n";
            $this->code .= "\t\t\t\t}\n";
            $this->code .= "\t\t\t}\n";
            $this->code .= "\t\t}\n\n";
        }

        public function getV2() {

            foreach ($this->table->getFields() as $field) {
                switch ($field->getCastingExtended()) {
                    case 'int':
                    case 'integer':
                        $this->code .= "\t\t/**\n";
                        $this->code .= "\t\t * Get {$field->getNameLabel()}\n";
                        $this->code .= "\t\t *\n";
                        $this->code .= "\t\t * @return int\n";
                        $this->code .= "\t\t */\n";
                        $this->code .= "\t\tpublic function get{$field->getNameTitleCase()}() {\n";
                        $this->code .= "\t\t\treturn (int) \$this->vars['{$field->getName()}'];\n";
                        $this->code .= "\t\t}\n\n";
                        break;

                    case 'float':
                        $this->code .= "\t\t/**\n";
                        $this->code .= "\t\t * Get {$field->getNameLabel()}\n";
                        $this->code .= "\t\t *\n";
                        $this->code .= "\t\t * @return float\n";
                        $this->code .= "\t\t */\n";
                        $this->code .= "\t\tpublic function get{$field->getNameTitleCase()}() {\n";
                        $this->code .= "\t\t\treturn (float) \$this->vars['{$field->getName()}'];\n";
                        $this->code .= "\t\t}\n\n";
                        break;

                    case 'date':
                    case 'datetime':
                        $this->code .= "\t\t/**\n";
                        $this->code .= "\t\t * Get {$field->getNameLabel()}\n";
                        $this->code .= "\t\t *\n";
                        $this->code .= "\t\t * @return bool\n";
                        $this->code .= "\t\t */\n";
                        $this->code .= "\t\tpublic function get{$field->getNameTitleCase()}() {\n";
                        $this->code .= "\t\t\treturn \$this->_model('{$field->getNameCamelCase()}', \$this->vars['{$field->getName()}'], 'Neoform\\Type\\Date');\n";
                        $this->code .= "\t\t}\n\n";
                        break;

                    case 'bool':
                    case 'boolean':
                        $this->code .= "\t\t/**\n";
                        $this->code .= "\t\t * Is {$field->getNameLabel()}\n";
                        $this->code .= "\t\t *\n";
                        $this->code .= "\t\t * @return bool\n";
                        $this->code .= "\t\t */\n";
                        $this->code .= "\t\tpublic function is{$field->getNameTitleCase()}() {\n";
                        $this->code .= "\t\t\treturn \$this->vars['{$field->getName()}'] === '{$field->getBoolTrueValue()}';\n";
                        $this->code .= "\t\t}\n\n";
                        break;

                    default:
                        $this->code .= "\t\t/**\n";
                        $this->code .= "\t\t * Get {$field->getNameLabel()}\n";
                        $this->code .= "\t\t *\n";
                        $this->code .= "\t\t * @return string\n";
                        $this->code .= "\t\t */\n";
                        $this->code .= "\t\tpublic function get{$field->getNameTitleCase()}() {\n";
                        $this->code .= "\t\t\treturn (string) \$this->vars['{$field->getName()}'];\n";
                        $this->code .= "\t\t}\n\n";
                        break;
                }
            }
        }

        protected function class_comments() {
            /**
            * The short description
            *
            * As many lines of extended description as you want {@link element} links to an element
            * {@link http://www.example.com Example hyperlink inline link} links to a website
            * Below this goes the tags to further describe element you are documenting
            */
            $this->code .= "\t/**\n";
            $this->code .= "\t * {$this->table->getNameLabel()} Model\n";
            $this->code .= "\t *\n";
            foreach ($this->table->getFields() as $field) {
                $this->code .= "\t * @var {$field->getCastingExtended()}" . ($field->allowsNull() ? '|null' : '') . " \${$field->getName()}\n";
            }
            $this->code .= "\t */\n";
        }

        public function references() {

            // many to one relationship (other tables referencing this one as a constraint)
            foreach ($this->table->getReferencingFields() as $referencing_field) {

                // if the reference is to a field that uniquely identifies a single row, the it is a one-to-one
                if ($referencing_field->isUnique()) {
                    // one to one relationship on inbound references
                    $this->one_to_one($referencing_field->getReferencedField(), $referencing_field);
                } else {

                    if ($referencing_field->getTable()->isRecord()) {
                        // one to many relationship (linking table implicating this one, tying it to another)
                        $this->one_to_many(
                            $referencing_field,
                            $referencing_field->getReferencedField()
                        );
                    }

                    // if the referencing field is part of a 2-key unique key, it's a many-to-many
                    if ($referencing_field->isLinkIndex()) {
                        // the many to many becomes one to many since this is a model and not a collection
                        $this->many_to_many($referencing_field);
                    }
                }
            }

            // one to one relationships on outbound references
            foreach ($this->table->getForeignKeys() as $foreignKey) {
                if ($foreignKey->getReferencedField()->getTable()->isRecord()) {
                    $this->one_to_one($foreignKey, $foreignKey->getReferencedField());
                }
            }
        }

        protected function one_to_one(Field $field, Field $referencedField) {

            $selfReference = $referencedField->getTable() === $this->table;

            $name = $this->used($selfReference ? "parent{$referencedField->getTable()->getNameTitleCase()}" : $referencedField->getTable()->getNameCamelCase());

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * " . ($selfReference ? 'Parent ' : '') . "{$referencedField->getTable()->getNameLabel()} Model based on \$this->var['{$field->getName()}']\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return {$this->namespace}\\{$referencedField->getTable()->getNameAsClass()}\\Model\n";
            $this->code .= "\t\t * @deprecated\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function {$name}() {\n";
            $this->code .= "\t\t\treturn \$this->_model('{$name}', \$this->vars['{$field->getName()}'], '{$this->namespace}\\{$referencedField->getTable()->getNameAsClass()}\\Model');\n";
            $this->code .= "\t\t}\n\n";

            $fName    = $this->used($selfReference ? "Parent" : '') . $referencedField->getTable()->getNameTitleCase();

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * " . ($selfReference ? 'Parent ' : '') . "{$referencedField->getTable()->getNameLabel()} Model based on \$this->var['{$field->getName()}']\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return {$this->namespace}\\{$referencedField->getTable()->getNameAsClass()}\\Model\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function get{$fName}() {\n";
            $this->code .= "\t\t\treturn \$this->_model('{$name}', \$this->vars['{$field->getName()}'], '{$this->namespace}\\{$referencedField->getTable()->getNameAsClass()}\\Model');\n";
            $this->code .= "\t\t}\n\n";
        }

        protected function one_to_many(Field $field, Field $referencedField) {

            $selfReference = $field->getTable() === $this->table;

            // Collection
            $name = $this->used(($selfReference ? "child{$field->getTable()->getNameTitleCase()}" : $field->getTable()->getNameCamelCase()) . 'Collection');

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * " . ($selfReference ? 'Child ' : '') . "{$field->getTable()->getNameLabel()} Collection\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param array|null \$orderBy array of field names (as the key) and sort direction (Neoform\\Entity\\Record\\Dao::SORT_ASC, Neoform\\Entity\\Record\\Dao::SORT_DESC)\n";
            $this->code .= "\t\t * @param int|null   \$offset get PKs starting at this offset\n";
            $this->code .= "\t\t * @param int|null   \$limit max number of PKs to return\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return {$this->namespace}\\{$field->getTable()->getNameAsClass()}\\Collection\n";
            $this->code .= "\t\t * @deprecated\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function {$name}(array \$orderBy=null, \$offset=null, \$limit=null) {\n";
            $this->code .= "\t\t\t\$key = self::_limitVarKey('{$name}', \$orderBy, \$offset, \$limit);\n";
            $this->code .= "\t\t\tif (! array_key_exists(\$key, \$this->_vars)) {\n";
            $this->code .= "\t\t\t\t\$this->_vars[\$key] = {$this->namespace}\\{$field->getTable()->getNameAsClass()}\\Collection::fromPks(\n";
            $this->code .= "\t\t\t\t\t{$this->namespace}\\{$field->getTable()->getNameAsClass()}\\Dao::get()->by{$field->getNameTitleCaseWithoutId()}(\$this->vars['{$referencedField->getNameCamelCase()}'], \$orderBy, \$offset, \$limit)\n";
            $this->code .= "\t\t\t\t);\n";
            $this->code .= "\t\t\t}\n";
            $this->code .= "\t\t\treturn \$this->_vars[\$key];\n";
            $this->code .= "\t\t}\n\n";

            $fName    = $this->used(($selfReference ? "child" : '') . "{$field->getTable()->getNameTitleCase()}Collection");

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * " . ($selfReference ? 'Child ' : '') . "{$field->getTable()->getNameLabel()} Collection\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param array|null \$orderBy array of field names (as the key) and sort direction (Neoform\\Entity\\Record\\Dao::SORT_ASC, Neoform\\Entity\\Record\\Dao::SORT_DESC)\n";
            $this->code .= "\t\t * @param int|null   \$offset get PKs starting at this offset\n";
            $this->code .= "\t\t * @param int|null   \$limit max number of PKs to return\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return {$this->namespace}\\{$field->getTable()->getNameAsClass()}\\Collection\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function get{$fName}(array \$orderBy=null, \$offset=null, \$limit=null) {\n";
            $this->code .= "\t\t\t\$key = self::_limitVarKey('{$name}', \$orderBy, \$offset, \$limit);\n";
            $this->code .= "\t\t\tif (! array_key_exists(\$key, \$this->_vars)) {\n";
            $this->code .= "\t\t\t\t\$this->_vars[\$key] = {$this->namespace}\\{$field->getTable()->getNameAsClass()}\\Collection::fromPks(\n";
            $this->code .= "\t\t\t\t\t{$this->namespace}\\{$field->getTable()->getNameAsClass()}\\Dao::get()->by{$field->getNameTitleCaseWithoutId()}(\$this->vars['{$referencedField->getNameCamelCase()}'], \$orderBy, \$offset, \$limit)\n";
            $this->code .= "\t\t\t\t);\n";
            $this->code .= "\t\t\t}\n";
            $this->code .= "\t\t\treturn \$this->_vars[\$key];\n";
            $this->code .= "\t\t}\n\n";

            // Count
            $name = $this->used(($selfReference ? "child{$field->getTable()->getNameTitleCase()}" : $field->getTable()->getNameCamelCase()) . 'Count');

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * " . ($selfReference ? 'Child ' : '') . "{$field->getTable()->getNameLabel()} Count\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return int\n";
            $this->code .= "\t\t * @deprecated\n";
            $this->code .= "\t\t */\n";
            $this->code .= "\t\tpublic function {$name}() {\n";
            $this->code .= "\t\t\t\$fieldVals = [\n";
            $this->code .= "\t\t\t\t'{$field->getName()}' => ({$referencedField->getCasting()}) \$this->vars['{$referencedField->getName()}'],\n";
            $this->code .= "\t\t\t];\n\n";
            $this->code .= "\t\t\t\$key = parent::_countVarKey('{$name}', \$fieldVals);\n";
            $this->code .= "\t\t\tif (! array_key_exists(\$key, \$this->_vars)) {\n";
            $this->code .= "\t\t\t\t\$this->_vars[\$key] = {$this->namespace}\\{$field->getTable()->getNameAsClass()}\\Dao::get()->count(\$fieldVals);\n";
            $this->code .= "\t\t\t}\n";
            $this->code .= "\t\t\treturn \$this->_vars[\$key];\n";
            $this->code .= "\t\t}\n\n";

            $fName    = $this->used(($selfReference ? "child" : '') . "{$field->getTable()->getNameTitleCase()}Count");

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * " . ($selfReference ? 'Child ' : '') . "{$field->getTable()->getNameLabel()} Count\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return int\n";
            $this->code .= "\t\t */\n";
            $this->code .= "\t\tpublic function get{$fName}() {\n";
            $this->code .= "\t\t\t\$fieldVals = [\n";
            $this->code .= "\t\t\t\t'{$field->getName()}' => ({$referencedField->getCasting()}) \$this->vars['{$referencedField->getName()}'],\n";
            $this->code .= "\t\t\t];\n\n";
            $this->code .= "\t\t\t\$key = parent::_countVarKey('{$name}', \$fieldVals);\n";
            $this->code .= "\t\t\tif (! array_key_exists(\$key, \$this->_vars)) {\n";
            $this->code .= "\t\t\t\t\$this->_vars[\$key] = {$this->namespace}\\{$field->getTable()->getNameAsClass()}\\Dao::get()->count(\$fieldVals);\n";
            $this->code .= "\t\t\t}\n";
            $this->code .= "\t\t\treturn \$this->_vars[\$key];\n";
            $this->code .= "\t\t}\n\n";
        }

        protected function many_to_many(Field $field) {

            $referencedField = $field->getOtherLinkIndexField();

            $selfReference = $field->getTable() === $this->table;

            // Collection
            $name = $this->used(($selfReference ? "child{$referencedField->getReferencedField()->getTable()->getNameTitleCase()}" : $referencedField->getReferencedField()->getTable()->getNameCamelCase()) . "Collection");

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * " . ($selfReference ? 'Child ' : '') . "{$referencedField->getReferencedField()->getTable()->getNameLabel()} Collection\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param array|null \$orderBy array of field names (as the key) and sort direction (Neoform\\Entity\\Record\\Dao::SORT_ASC, Neoform\\Entity\\Record\\Dao::SORT_DESC)\n";
            $this->code .= "\t\t * @param int|null   \$offset get PKs starting at this offset\n";
            $this->code .= "\t\t * @param int|null   \$limit max number of PKs to return\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return {$this->namespace}\\{$referencedField->getReferencedField()->getTable()->getNameAsClass()}\\Collection\n";
            $this->code .= "\t\t * @deprecated\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function {$name}(array \$orderBy=null, \$offset=null, \$limit=null) {\n";
            $this->code .= "\t\t\t\$key = self::_limitVarKey('{$name}', \$orderBy, \$offset, \$limit);\n";
            $this->code .= "\t\t\tif (! array_key_exists(\$key, \$this->_vars)) {\n";
            $this->code .= "\t\t\t\t\$this->_vars[\$key] = {$this->namespace}\\{$referencedField->getReferencedField()->getTable()->getNameAsClass()}\\Collection::fromPks(\n";
            $this->code .= "\t\t\t\t\t{$this->namespace}\\{$field->getTable()->getNameAsClass()}\\Dao::get()->by{$field->getNameTitleCaseWithoutId()}(\$this->vars['{$field->getReferencedField()->getName()}'], \$orderBy, \$offset, \$limit)\n";
            $this->code .= "\t\t\t\t);\n";
            $this->code .= "\t\t\t}\n";
            $this->code .= "\t\t\treturn \$this->_vars[\$key];\n";
            $this->code .= "\t\t}\n\n";

            $fName = $this->used(($selfReference ? "child" : '') . "{$referencedField->getReferencedField()->getTable()->getNameTitleCase()}Collection");

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * " . ($selfReference ? 'Child ' : '') . "{$referencedField->getReferencedField()->getTable()->getNameLabel()} Collection\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param array|null \$orderBy array of field names (as the key) and sort direction (Neoform\\Entity\\Record\\Dao::SORT_ASC, Neoform\\Entity\\Record\\Dao::SORT_DESC)\n";
            $this->code .= "\t\t * @param int|null   \$offset get PKs starting at this offset\n";
            $this->code .= "\t\t * @param int|null   \$limit max number of PKs to return\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return {$this->namespace}\\{$referencedField->getReferencedField()->getTable()->getNameAsClass()}\\Collection\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function get{$fName}(array \$orderBy=null, \$offset=null, \$limit=null) {\n";
            $this->code .= "\t\t\t\$key = self::_limitVarKey('{$name}', \$orderBy, \$offset, \$limit);\n";
            $this->code .= "\t\t\tif (! array_key_exists(\$key, \$this->_vars)) {\n";
            $this->code .= "\t\t\t\t\$this->_vars[\$key] = {$this->namespace}\\{$referencedField->getReferencedField()->getTable()->getNameAsClass()}\\Collection::fromPks(\n";
            $this->code .= "\t\t\t\t\t{$this->namespace}\\{$field->getTable()->getNameAsClass()}\\Dao::get()->by{$field->getNameTitleCaseWithoutId()}(\$this->vars['{$field->getReferencedField()->getName()}'], \$orderBy, \$offset, \$limit)\n";
            $this->code .= "\t\t\t\t);\n";
            $this->code .= "\t\t\t}\n";
            $this->code .= "\t\t\treturn \$this->_vars[\$key];\n";
            $this->code .= "\t\t}\n\n";


            // Count
            $name = $this->used(($selfReference ? "child{$referencedField->getReferencedField()->getTable()->getNameTitleCase()}" : $referencedField->getReferencedField()->getTable()->getNameCamelCase()) . "Count");

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * " . ($selfReference ? 'Child ' : '') . "{$referencedField->getReferencedField()->getTable()->getNameLabel()} count\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return int\n";
            $this->code .= "\t\t * @deprecated\n";
            $this->code .= "\t\t */\n";
            $this->code .= "\t\tpublic function {$name}() {\n";
            $this->code .= "\t\t\t\$fieldVals = [\n";
            $this->code .= "\t\t\t\t'{$field->getName()}' => ({$field->getReferencedField()->getCasting()}) \$this->vars['{$field->getReferencedField()->getNameCamelCase()}'],\n";
            $this->code .= "\t\t\t];\n\n";
            $this->code .= "\t\t\t\$key = parent::_countVarKey('{$name}', \$fieldVals);\n";
            $this->code .= "\t\t\tif (! array_key_exists(\$key, \$this->_vars)) {\n";
            $this->code .= "\t\t\t\t\$this->_vars[\$key] = {$this->namespace}\\{$field->getTable()->getNameAsClass()}\\Dao::get()->count(\$fieldVals);\n";
            $this->code .= "\t\t\t}\n";
            $this->code .= "\t\t\treturn \$this->_vars[\$key];\n";
            $this->code .= "\t\t}\n\n";

            $fName = $this->used(($selfReference ? "child" : '') . "{$referencedField->getReferencedField()->getTable()->getNameTitleCase()}Count");

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * " . ($selfReference ? 'Child ' : '') . "{$referencedField->getReferencedField()->getTable()->getNameLabel()} count\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return int\n";
            $this->code .= "\t\t */\n";
            $this->code .= "\t\tpublic function get{$fName}() {\n";
            $this->code .= "\t\t\t\$fieldVals = [\n";
            $this->code .= "\t\t\t\t'{$field->getName()}' => ({$field->getReferencedField()->getCasting()}) \$this->vars['{$field->getReferencedField()->getNameCamelCase()}'],\n";
            $this->code .= "\t\t\t];\n\n";
            $this->code .= "\t\t\t\$key = parent::_countVarKey('{$name}', \$fieldVals);\n";
            $this->code .= "\t\t\tif (! array_key_exists(\$key, \$this->_vars)) {\n";
            $this->code .= "\t\t\t\t\$this->_vars[\$key] = {$this->namespace}\\{$field->getTable()->getNameAsClass()}\\Dao::get()->count(\$fieldVals);\n";
            $this->code .= "\t\t\t}\n";
            $this->code .= "\t\t\treturn \$this->_vars[\$key];\n";
            $this->code .= "\t\t}\n\n";
        }
    }
