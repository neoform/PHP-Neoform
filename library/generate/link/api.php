<?php

    class generate_link_api extends generate_api {

        public function code() {

            $this->code .= '<?php'."\n\n";
            $this->code .= "\tclass " . $this->table->name . "_api {\n\n";

            $this->create();
            //$this->update($longest);
            $this->delete();
            //$this->validate_lookup();
            $this->validate_insert();
            //$this->validate_update();

            $this->code = substr($this->code, 0, -1);
            $this->code .= "\t}\n";
        }


        public function delete() {

            $fks = array_values($this->table->foreign_keys);

            $field1 = $fks[0];
            $field2 = $fks[1];

            //core::debug($field1->table->name . '.' . $field1->name, $field2->table->name . '.' . $field2->name);

            $length = max(strlen($field1->name), strlen($field2->name));

            $this->code .= "\t\tpublic static function delete_by_" . $field1->referenced_field->table->name . "(" . $field1->referenced_field->table->name . "_model $" . $field1->referenced_field->table->name . ", " . $field2->referenced_field->table->name . "_collection $" . $field2->referenced_field->table->name . "_collection) {\n";
            $this->code .= "\t\t\t\$keys = [];\n";
            $this->code .= "\t\t\tforeach ($" . $field2->referenced_field->table->name . "_collection as $" . $field2->referenced_field->table->name . ") {\n";
            $this->code .= "\t\t\t\t\$keys[] = [\n";
            $this->code .= "\t\t\t\t\t'" . str_pad($field1->name . "'", $length +1) . " => (" . $field1->referenced_field->casting . ") $" . $field1->referenced_field->table->name . "->" . $field1->referenced_field->name . ",\n";
            $this->code .= "\t\t\t\t\t'" . str_pad($field2->name . "'", $length +1) . " => (" . $field2->referenced_field->casting . ") $" . $field2->referenced_field->table->name . "->" . $field2->referenced_field->name . ",\n";
            $this->code .= "\t\t\t\t];\n";
            $this->code .= "\t\t\t}\n";
            $this->code .= "\t\t\treturn " . $this->table->name . "_dao::deletes(\$keys);\n";
            $this->code .= "\t\t}\n\n";

            $this->code .= "\t\tpublic static function delete_by_" . $field2->referenced_field->table->name . "(" . $field2->referenced_field->table->name . "_model $" . $field2->referenced_field->table->name . ", " . $field1->referenced_field->table->name . "_collection $" . $field1->referenced_field->table->name . "_collection) {\n";
            $this->code .= "\t\t\t\$keys = [];\n";
            $this->code .= "\t\t\tforeach ($" . $field1->referenced_field->table->name . "_collection as $" . $field1->referenced_field->table->name . ") {\n";
            $this->code .= "\t\t\t\t\$keys[] = [\n";
            $this->code .= "\t\t\t\t\t'" . str_pad($field2->name . "'", $length +1) . " => (" . $field2->referenced_field->casting . ") $" . $field2->referenced_field->table->name . "->" . $field2->referenced_field->name . ",\n";
            $this->code .= "\t\t\t\t\t'" . str_pad($field1->name . "'", $length +1) . " => (" . $field1->referenced_field->casting . ") $" . $field1->referenced_field->table->name . "->" . $field1->referenced_field->name . ",\n";
            $this->code .= "\t\t\t\t];\n";
            $this->code .= "\t\t\t}\n";
            $this->code .= "\t\t\treturn " . $this->table->name . "_dao::deletes(\$keys);\n";
            $this->code .= "\t\t}\n\n";

        }
    }