<?php

    class generate_dao extends generate {

        protected function bindings() {

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Get the generic bindings of the table columns\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return array\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic static function bindings() {\n";
            $this->code .= "\t\t\treturn [\n";
            $longest_part = $this->longest_length($this->table->fields);
            foreach ($this->table->fields as $field) {
                $this->code .= "\t\t\t\t'" . str_pad($field->name . "'", $longest_part + 1) . " => '" . $field->pdo_casting . "',\n";
            }
            $this->code .= "\t\t\t];\n";
            $this->code .= "\t\t}\n";
            $this->code .= "\n\n";
        }
    }