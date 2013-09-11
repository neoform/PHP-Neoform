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

                $this->code .= "\t\t\t'" . str_pad($field->name . "'", $longest_part + 1) . " => {$binding},\n";
            }
            $this->code .= "\t\t];\n\n";
        }
    }