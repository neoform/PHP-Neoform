<?php

    namespace neoform\entity\generate;

    use neoform\sql\parser\field;
    use neoform\entity\generate;

    class model extends generate {

        protected $used_function_names = [];

        protected function used($name) {
            $suffix     = '';
            $final_name = $name;
            $i          = 1;
            while (in_array($final_name, $this->used_function_names)) {
                $final_name = $name . $suffix;
                $suffix     = $i++;
            }
            $this->used_function_names[] = $final_name;
            return $final_name;
        }

        public function get() {

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

            foreach ($this->table->fields as $field) {
                switch ($field->casting_extended) {
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

            $this->code .= "\t\tpublic function __get(\$k) {\n\n";
            $this->code .= "\t\t\tif (isset(\$this->vars[\$k])) {\n";
            $this->code .= "\t\t\t\tswitch (\$k) {\n";

            // INTS
            if (count($ints)) {
                $this->code .= "\t\t\t\t\t// integers\n";
                foreach ($ints as $int) {
                    $this->code .= "\t\t\t\t\tcase '{$int->name}':\n";
                }
                $this->code .= "\t\t\t\t\t\treturn (int) \$this->vars[\$k];\n\n";
            }

            // FLOATS
            if (count($floats)) {
                $this->code .= "\t\t\t\t\t// floats\n";
                foreach ($floats as $float) {
                    $this->code .= "\t\t\t\t\tcase '{$float->name}':\n";
                }
                $this->code .= "\t\t\t\t\t\treturn (float) \$this->vars[\$k];\n\n";
            }

            // BOOLS
            if (count($bools)) {
                $this->code .= "\t\t\t\t\t// booleans\n";
                foreach ($bools as $bool) {
                    $this->code .= "\t\t\t\t\tcase '{$bool->name}':\n";
                    $this->code .= "\t\t\t\t\t\treturn \$this->vars[\$k] === '" . $bool->bool_true_value . "';\n\n";
                }
            }

            // DATES
            if (count($dates)) {
                $this->code .= "\t\t\t\t\t// dates\n";
                foreach ($dates as $date) {
                    $this->code .= "\t\t\t\t\tcase '{$date->name}':\n";
                }
                $this->code .= "\t\t\t\t\t\treturn \$this->_model(\$k, \$this->vars[\$k], 'type\\date');\n\n";
            }

            // STRINGS
            if (count($strings)) {
                $this->code .= "\t\t\t\t\t// strings\n";
                foreach ($strings as $string) {
                    $this->code .= "\t\t\t\t\tcase '{$string->name}':\n";
                }
                $this->code .= "\t\t\t\t\t\treturn (string) \$this->vars[\$k];\n\n";
            }

            $this->code .= "\t\t\t\t\tdefault:\n";
            $this->code .= "\t\t\t\t\t\treturn \$this->vars[\$k];\n";
            $this->code .= "\t\t\t\t}\n";
            $this->code .= "\t\t\t}\n";
            $this->code .= "\t\t}\n\n";
        }



        protected function class_comments() {
            /**
            * The short description
            *
            * As many lines of extendend description as you want {@link element} links to an element
            * {@link http://www.example.com Example hyperlink inline link} links to a website
            * Below this goes the tags to further describe element you are documenting
            */
            $this->code .= "\t/**\n";
            $this->code .= "\t * " . ucwords(str_replace('_', ' ', $this->table->name)) . " Model\n";
            $this->code .= "\t *\n";
            foreach ($this->table->fields as $field) {
                $this->code .= "\t * @var {$field->casting_extended}" . ($field->allows_null() ? '|null' : '') . " \${$field->name}\n";
            }
            $this->code .= "\t */\n";
        }

        public function references() {

            // many to one relationship (other tables referencing this one as a constraint)
            foreach ($this->table->referencing_fields as $referencing_field) {

                // if the reference is to a field that uniquely identifies a single row, the it is a one-to-one
                if ($referencing_field->is_unique()) {
                    // one to one relationship on inbound references
                    $this->one_to_one($referencing_field->referenced_field, $referencing_field);
                } else {

                    if ($referencing_field->table->is_record()) {
                        // one to many relationship (linking table implicating this one, tying it to another)
                        $this->one_to_many(
                            $referencing_field,
                            $referencing_field->referenced_field
                        );
                    }

                    // if the referencing field is part of a 2-key unique key, it's a many-to-many
                    if ($referencing_field->is_link_index()) {
                        // the many to many becomes one to many since this is a model and not a collection
                        $this->many_to_many($referencing_field);
                    }
                }
            }


            // one to one relationships on outbound references
            foreach ($this->table->foreign_keys as $foreign_key) {
                if ($foreign_key->referenced_field->table->is_record()) {
                    $this->one_to_one($foreign_key, $foreign_key->referenced_field);
                }
            }
        }

        protected function one_to_one(field $field, field $referenced_field) {

            $self_reference = $referenced_field->table === $this->table;

            $name = $this->used(($self_reference ? 'parent_' : '') . $referenced_field->table->name);

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * " . ($self_reference ? 'Parent ' : '') . ucwords(str_replace('_', ' ', $referenced_field->table->name)) . " Model based on '{$field->name}'\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return \\neoform\\" . str_replace('_', '\\', $referenced_field->table->name) . "\\model\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function {$name}() {\n";
            $this->code .= "\t\t\treturn \$this->_model('{$name}', \$this->vars['{$field->name}'], '" . str_replace('_', '\\', $referenced_field->table->name) . "\\model');\n";
            $this->code .= "\t\t}\n\n";
        }

        protected function one_to_many(field $field, field $referenced_field) {

            $self_reference = $field->table === $this->table;

            // Collection
            $name = $this->used(($self_reference ? 'child_' : '') . $field->table->name . '_collection');

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * " . ($self_reference ? 'Child ' : '') . ucwords(str_replace('_', ' ', $field->table->name . ' Collection')) . "\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param array|null   \$order_by array of field names (as the key) and sort direction (entity_record_dao::SORT_ASC, entity_record_dao::SORT_DESC)\n";
            $this->code .= "\t\t * @param integer|null \$offset get PKs starting at this offset\n";
            $this->code .= "\t\t * @param integer|null \$limit max number of PKs to return\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return \\neoform\\" . str_replace('_', '\\', $field->table->name) . "\\collection\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function {$name}(array \$order_by=null, \$offset=null, \$limit=null) {\n";
            $this->code .= "\t\t\t\$key = self::_limit_var_key('{$name}', \$order_by, \$offset, \$limit);\n";
            $this->code .= "\t\t\tif (! array_key_exists(\$key, \$this->_vars)) {\n";
            $this->code .= "\t\t\t\t\$this->_vars[\$key] = new \\neoform\\" . str_replace('_', '\\', $field->table->name) . "\\collection(\n";
            $this->code .= "\t\t\t\t\tentity::dao('" . str_replace('_', '\\', $field->table->name) . "')->by_{$field->name_idless}(\$this->vars['{$referenced_field->name}'], \$order_by, \$offset, \$limit)\n";
            $this->code .= "\t\t\t\t);\n";
            $this->code .= "\t\t\t}\n";
            $this->code .= "\t\t\treturn \$this->_vars[\$key];\n";
            $this->code .= "\t\t}\n\n";

            // Count
            $name = $this->used(($self_reference ? 'child_' : '') . $field->table->name . '_count');

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * " . ($self_reference ? 'Child ' : '') . ucwords(str_replace('_', ' ', $field->table->name . ' Count')) . "\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return integer\n";
            $this->code .= "\t\t */\n";
            $this->code .= "\t\tpublic function {$name}() {\n";
            $this->code .= "\t\t\t\$fieldvals = [\n";
            $this->code .= "\t\t\t\t'{$field->name}' => ({$referenced_field->casting}) \$this->vars['{$referenced_field->name}'],\n";
            $this->code .= "\t\t\t];\n\n";
            $this->code .= "\t\t\t\$key = parent::_count_var_key('{$name}', \$fieldvals);\n";
            $this->code .= "\t\t\tif (! array_key_exists(\$key, \$this->_vars)) {\n";
            $this->code .= "\t\t\t\t\$this->_vars[\$key] = entity::dao('" . str_replace('_', '\\', $field->table->name) . "')->count(\$fieldvals);\n";
            $this->code .= "\t\t\t}\n";
            $this->code .= "\t\t\treturn \$this->_vars[\$key];\n";
            $this->code .= "\t\t}\n\n";
        }

        protected function many_to_many(field $field) {

            $referenced_field = $field->get_other_link_index_field();

            $self_reference = $field->table === $this->table;

            // Collection
            $name = $this->used(($self_reference ? 'child_' : '') . $referenced_field->referenced_field->table->name . "_collection");

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * " . ($self_reference ? 'Child ' : '') . ucwords(str_replace('_', ' ', $referenced_field->referenced_field->table->name)) . " Collection\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param array|null   \$order_by array of field names (as the key) and sort direction (entity_record_dao::SORT_ASC, entity_record_dao::SORT_DESC)\n";
            $this->code .= "\t\t * @param integer|null \$offset get PKs starting at this offset\n";
            $this->code .= "\t\t * @param integer|null \$limit max number of PKs to return\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return \\neoform\\" . str_replace('_', '\\', $referenced_field->referenced_field->table->name) . "\\collection\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function {$name}(array \$order_by=null, \$offset=null, \$limit=null) {\n";
            $this->code .= "\t\t\t\$key = self::_limit_var_key('{$name}', \$order_by, \$offset, \$limit);\n";
            $this->code .= "\t\t\tif (! array_key_exists(\$key, \$this->_vars)) {\n";
            $this->code .= "\t\t\t\t\$this->_vars[\$key] = new \\neoform\\" . str_replace('_', '\\', $referenced_field->referenced_field->table->name) . "\\collection(\n";
            $this->code .= "\t\t\t\t\tentity::dao('" . str_replace('_', '\\', $field->table->name) . "')->by_{$field->name_idless}(\$this->vars['{$field->referenced_field->name}'], \$order_by, \$offset, \$limit)\n";
            $this->code .= "\t\t\t\t);\n";
            $this->code .= "\t\t\t}\n";
            $this->code .= "\t\t\treturn \$this->_vars[\$key];\n";
            $this->code .= "\t\t}\n\n";


            // Count
            $name = $this->used(($self_reference ? 'child_' : '') . $referenced_field->referenced_field->table->name . "_count");

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * " . ($self_reference ? 'Child ' : '') . ucwords(str_replace('_', ' ', $referenced_field->referenced_field->table->name)) . " count\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return integer\n";
            $this->code .= "\t\t */\n";
            $this->code .= "\t\tpublic function {$name}() {\n";
            $this->code .= "\t\t\t\$fieldvals = [\n";
            $this->code .= "\t\t\t\t'{$field->name}' => ({$field->referenced_field->casting}) \$this->vars['{$field->referenced_field->name}'],\n";
            $this->code .= "\t\t\t];\n\n";
            $this->code .= "\t\t\t\$key = parent::_count_var_key('{$name}', \$fieldvals);\n";
            $this->code .= "\t\t\tif (! array_key_exists(\$key, \$this->_vars)) {\n";
            $this->code .= "\t\t\t\t\$this->_vars[\$key] = entity::dao('" . str_replace('_', '\\', $field->table->name) . "')->count(\$fieldvals);\n";
            $this->code .= "\t\t\t}\n";
            $this->code .= "\t\t\treturn \$this->_vars[\$key];\n";
            $this->code .= "\t\t}\n\n";
        }
    }
