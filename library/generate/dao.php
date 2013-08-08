<?php

    class generate_dao extends generate {

        protected function bindings() {

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * \$var array \$pdo_bindings list of fields and their corresponding PDO bindings\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return array\n";
            $this->code .= "\t\t */\n";
            $this->code .= "\t\t\tprotected \$pdo_bindings = [\n";
            $longest_part = $this->longest_length($this->table->fields);

            foreach ($this->table->fields as $field) {
                switch ((string) $field->pdo_casting) {
                    case 'int':
                        $binding = 'PDO::PARAM_INT';
                        break;

                    case 'string':
                        $binding = 'PDO::PARAM_STR';
                        break;

                    case 'binary':
                        $binding = 'PDO::PARAM_LOB';
                        break;

                    case 'bool':
                        $binding = 'PDO::PARAM_BOOL';
                        break;

                    case 'null':
                        $binding = 'PDO::PARAM_NULL';
                        break;

                    default:
                        throw new exception("Unknown PDO binding for type \"{$field->pdo_casting}\".");
                }

                $this->code .= "\t\t\t\t'" . str_pad($field->name . "'", $longest_part + 1) . " => {$binding},\n";
            }
            $this->code .= "\t\t\t];\n\n";
        }
    }