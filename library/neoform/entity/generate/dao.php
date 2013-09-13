<?php
    namespace neoform\entity\generate;

    use neoform\entity\generate;

    class dao extends generate {

        protected function bindings() {

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * \$var array \$field_bindings list of fields and their corresponding bindings\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return array\n";
            $this->code .= "\t\t */\n";
            $this->code .= "\t\tprotected \$field_bindings = [\n";
            $longest_part = $this->longest_length($this->table->fields);

            foreach ($this->table->fields as $field) {
                switch ((string) $field->pdo_casting) {
                    case 'int':
                        $binding = 'self::TYPE_INTEGER';
                        break;

                    case 'string':
                        $binding = 'self::TYPE_STRING';
                        break;

                    case 'binary':
                        $binding = 'self::TYPE_BINARY';
                        break;

                    case 'bool':
                        $binding = 'self::TYPE_BOOL';
                        break;

                    case 'float':
                        $binding = 'self::TYPE_FLOAT';
                        break;

                    case 'decimal':
                        $binding = 'self::TYPE_DECIMAL';
                        break;

                    default:
                        throw new \exception("Unknown PDO binding for type \"{$field->pdo_casting}\".");
                }

                $this->code .= "\t\t\t'" . str_pad("{$field->name}'", $longest_part + 1) . " => {$binding},\n";
            }
            $this->code .= "\t\t];\n\n";

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * \$var array \$referenced_entities list of fields (in this entity) and their related foreign entity\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return array\n";
            $this->code .= "\t\t */\n";

            $relations        = [];
            $referenced_field = [];
//            if ($this->table->table_type() === 'link') {
                foreach ($this->table->foreign_keys as $fk) {
                    $referenced_field[]   = $fk;
                    $relations[$fk->name] = str_replace('_', '\\', $fk->referenced_field->table->name);
                }
//            }

            if ($relations) {
                $this->code .= "\t\tprotected \$referenced_entities = [\n";
                $longest_part = $this->longest_length($referenced_field);
                foreach ($relations as $field => $table) {
                    $this->code .= "\t\t\t'" . str_pad($field . "'", $longest_part + 1) . " => '" . $table . "',\n";
                }
                $this->code .= "\t\t];\n\n";
            } else {
                $this->code .= "\t\tprotected \$referenced_entities = [];\n\n";
            }

//            $this->code .= "\t\t/**\n";
//            $this->code .= "\t\t * \$var array \$referencing_entities list of fields (in this entity) and their referencing foreign entities\n";
//            $this->code .= "\t\t *\n";
//            $this->code .= "\t\t * @return array\n";
//            $this->code .= "\t\t */\n";
//
//            $relations        = [];
//            $referenced_field = [];
//            foreach ($this->table->referencing_fields as $referencing_field) {
//                $referenced_field[] = $referencing_field->referenced_field;
//                $relations[$referencing_field->referenced_field->name][] = "'" . str_replace('_', '\\', $referencing_field->table->name) . "'";
//            }
//
//            if ($relations) {
//                $this->code .= "\t\tprotected \$referencing_entities = [\n";
//                $longest_part = $this->longest_length($referenced_field);
//                foreach ($relations as $field => $tables) {
//                    $this->code .= "\t\t\t'" . str_pad($field . "'", $longest_part + 1) . " => [ " . join(', ', $tables) . " ],\n";
//                }
//                $this->code .= "\t\t];\n\n";
//            } else {
//                $this->code .= "\t\tprotected \$referenced_entities = [];\n\n";
//            }
        }
    }