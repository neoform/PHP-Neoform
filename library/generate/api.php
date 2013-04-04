<?php

    class generate_api extends generate {

        public function validate_insert() {

            $pk = $this->table->primary_key;

            $this->code .= "\t\tpublic static function _validate_insert(input_collection \$input) {\n\n";
            foreach ($this->table->fields as $field) {
                if ($field->is_auto_increment()) {
                    // if its auto increment, there's no reason to be setting the field.
                    continue;
                }

                $this->code .= "\t\t\t// " . $field->name . "\n";
                $this->code .= "\t\t\t\$input->" . $field->name . "->cast('" . $field->casting . "')";

                if ($field->allows_null() || $field->is_autogenerated_on_insert()) {
                    $this->code .= "->optional()";
                }

                $this->code .= sql_parser::driver_specific_api_validation($field);

                //unique
                if ($field->is_unique()) {
                    $this->code .= "->callback(function($" . $field->name . ") {\n";
                    $this->code .= "\t\t\t\t$" . $pk->name . "_arr = " . $this->table->name . "_dao::by_" . $field->name_idless . "($" . $field->name . "->val());\n";
                    $this->code .= "\t\t\t\tif (is_array($" . $pk->name . "_arr) && count($" . $pk->name . "_arr)) {\n";
                    $this->code .= "\t\t\t\t\t$" . $field->name . "->errors('already in use');\n";
                    $this->code .= "\t\t\t\t}\n";
                    $this->code .= "\t\t\t})";
                }

                // references = check if object exists
                if ($field->referenced_field) {
                    $this->fk($field);
                }

                $this->code .= ";\n\n";
            }
            $this->code = substr($this->code, 0, -1);
            $this->code .= "\t\t}\n\n";
        }

        public function validate_update() {

            $pk = $this->table->primary_key;

            $this->code .= "\t\tpublic static function _validate_update(" . $this->table->name . "_model $" . $this->table->name . ", input_collection \$input) {\n\n";
            foreach ($this->table->fields as $field) {
                if ($field->is_auto_increment()) {
                    // if its auto increment, there's no reason to be setting the field.
                    continue;
                }

                $this->code .= "\t\t\t// " . $field->name . "\n";
                $this->code .= "\t\t\t\$input->" . $field->name . "->cast('" . $field->casting . "')";

                // Make everything optional since its an update.
                $this->code .= "->optional()";

                $this->code .= sql_parser::driver_specific_api_validation($field);

                //unique
                if ($field->is_unique()) {
                    $this->code .= "->callback(function($" . $field->name . ") use ($" . $this->table->name . ") {\n";
                    $this->code .= "\t\t\t\t$" . $pk->name . "_arr = " . $this->table->name . "_dao::by_" . $field->name_idless . "($" . $field->name . "->val());\n";
                    $this->code .= "\t\t\t\tif (is_array($" . $pk->name . "_arr) && count($" . $pk->name . "_arr) && (" . $pk->casting . ") current($" . $pk->name . "_arr) !== $" . $this->table->name . "->" . $pk->name . ") {\n";
                    $this->code .= "\t\t\t\t\t$" . $field->name . "->errors('already in use');\n";
                    $this->code .= "\t\t\t\t}\n";
                    $this->code .= "\t\t\t})";
                }

                // references = check if object exists
                if ($field->referenced_field) {
                    $this->fk($field);
                }


                $this->code .= ";\n\n";
            }
            $this->code = substr($this->code, 0, -1);
            $this->code .= "\t\t}\n\n";
        }

        public function create() {

            $longest = $this->table->longest_field_length();

            $this->code .= "\t\tpublic static function insert(array \$info) {\n\n";
            $this->code .= "\t\t\t\$input = new input_collection(\$info);\n\n";
            $this->code .= "\t\t\tself::_validate_insert(\$input);\n\n";
            $this->code .= "\t\t\tif (\$input->is_valid()) {\n";
            $this->code .= "\t\t\t\treturn " . $this->table->name . "_dao::insert([\n";

            foreach ($this->table->fields as $field) {
                if ($field->is_auto_increment()) {
                    // if its auto increment, there's no reason to be setting the field.
                    continue;
                }

                $this->code .= "\t\t\t\t\t'" . str_pad($field->name . "'", $longest +1) . " => \$input->" . $field->name . "->val(),\n";
            }

            $this->code .= "\t\t\t\t]);\n";
            $this->code .= "\t\t\t}\n";
            $this->code .= "\t\t\tthrow \$input->exception();\n";
            $this->code .= "\t\t}\n\n";
        }

        public function update() {

            $this->code .= "\t\tpublic static function update(" . $this->table->name . "_model $" . $this->table->name . ", array \$info, \$crush=false) {\n\n";
            $this->code .= "\t\t\t\$input = new input_collection(\$info);\n\n";

            $this->code .= "\t\t\tself::_validate_update($" . $this->table->name . ", \$input);\n\n";

            $this->code .= "\t\t\tif (\$input->is_valid()) {\n";
            $this->code .= "\t\t\t\treturn " . $this->table->name . "_dao::update(\n";
            $this->code .= "\t\t\t\t\t$" . $this->table->name . ",\n";
            $this->code .= "\t\t\t\t\t\$input->vals(\n";
            $this->code .= "\t\t\t\t\t\t[\n";

            foreach ($this->table->fields as $field) {
                if ($field->is_auto_increment()) {
                    // if its auto increment, there's no reason to be setting the field.
                    continue;
                }

                $this->code .= "\t\t\t\t\t\t\t'" . $field->name . "',\n";
            }

            $this->code .= "\t\t\t\t\t\t],\n";
            $this->code .= "\t\t\t\t\t\t\$crush\n";
            $this->code .= "\t\t\t\t\t)\n";
            $this->code .= "\t\t\t\t);\n";
            $this->code .= "\t\t\t}\n";

            $this->code .= "\t\t\tthrow \$input->exception();\n";
            $this->code .= "\t\t}\n\n";
        }

        protected function fk(sql_parser_field $field) {
            $this->code .= "->callback(function($" . $field->name . "){\n";
            if ($field->allows_null()) {
                $this->code .= "\t\t\t\tif ($" . $field->name . "->val()) {\n";
                $this->code .= "\t\t\t\t\ttry {\n";
                $this->code .= "\t\t\t\t\t\t$" . $field->name . "->data('model', new " . $field->referenced_field->table->name . "_model($" . $field->name . "->val()));\n";
                $this->code .= "\t\t\t\t\t} catch (" . $field->referenced_field->table->name . "_exception \$e) {\n";
                $this->code .= "\t\t\t\t\t\t$" . $field->name . "->errors(\$e->getMessage());\n";
                $this->code .= "\t\t\t\t\t}\n";
            } else {
                $this->code .= "\t\t\t\ttry {\n";
                $this->code .= "\t\t\t\t\t$" . $field->name . "->data('model', new " . $field->referenced_field->table->name . "_model($" . $field->name . "->val()));\n";
                $this->code .= "\t\t\t\t} catch (" . $field->referenced_field->table->name . "_exception \$e) {\n";
                $this->code .= "\t\t\t\t\t$" . $field->name . "->errors(\$e->getMessage());\n";
            }
            $this->code .= "\t\t\t\t}\n";
            $this->code .= "\t\t\t})";
        }
    }
