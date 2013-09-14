<?php

    namespace neoform\entity\generate\link;

    use neoform\entity\generate;

    class api extends generate\api {

        public function code() {

            $this->code .= '<?php'."\n\n";
            $this->code .= "\tnamespace neoform\\" . str_replace('_', '\\', $this->table->name)  . ";\n\n";
            $this->code .= "\tuse neoform\\input;\n";
            $this->code .= "\tuse neoform\\entity;\n\n";
            $this->code .= "\tclass api {\n\n";

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

            $length = max(strlen($field1->name), strlen($field2->name));

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Deletes links\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param \\neoform\\" . str_replace('_', '\\', $field1->referenced_field->table->name) . "\\model \${$field1->referenced_field->table->name}\n";
            $this->code .= "\t\t * @param \\neoform\\" . str_replace('_', '\\', $field2->referenced_field->table->name) . "\\collection \${$field2->referenced_field->table->name}_collection\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return bool\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic static function delete_by_{$field1->referenced_field->table->name}(\\neoform\\" . str_replace('_', '\\', $field1->referenced_field->table->name) . "\\model \${$field1->referenced_field->table->name}, \\neoform\\" . str_replace('_', '\\', $field2->referenced_field->table->name) . "\\collection \${$field2->referenced_field->table->name}_collection) {\n";
            $this->code .= "\t\t\t\$keys = [];\n";
            $this->code .= "\t\t\tforeach (\${$field2->referenced_field->table->name}_collection as \${$field2->referenced_field->table->name}) {\n";
            $this->code .= "\t\t\t\t\$keys[] = [\n";
            $this->code .= "\t\t\t\t\t'" . str_pad($field1->name . "'", $length +1) . " => ({$field1->referenced_field->casting}) \${$field1->referenced_field->table->name}->{$field1->referenced_field->name},\n";
            $this->code .= "\t\t\t\t\t'" . str_pad($field2->name . "'", $length +1) . " => ({$field2->referenced_field->casting}) \${$field2->referenced_field->table->name}->{$field2->referenced_field->name},\n";
            $this->code .= "\t\t\t\t];\n";
            $this->code .= "\t\t\t}\n";
            $this->code .= "\t\t\treturn entity::dao('" . str_replace('_', '\\', $this->table->name) . "')->delete_multi(\$keys);\n";
            $this->code .= "\t\t}\n\n";

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Deletes links\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param \\neoform\\" . str_replace('_', '\\', $field2->referenced_field->table->name) . "\\model \${$field2->referenced_field->table->name}\n";
            $this->code .= "\t\t * @param \\neoform\\" . str_replace('_', '\\', $field1->referenced_field->table->name) . "\\collection \${$field1->referenced_field->table->name}_collection\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return bool\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic static function delete_by_{$field2->referenced_field->table->name}(\\neoform\\" . str_replace('_', '\\', $field2->referenced_field->table->name) . "\\model \${$field2->referenced_field->table->name}, \\neoform\\" . str_replace('_', '\\', $field1->referenced_field->table->name) . "\\collection \${$field1->referenced_field->table->name}_collection) {\n";
            $this->code .= "\t\t\t\$keys = [];\n";
            $this->code .= "\t\t\tforeach (\${$field1->referenced_field->table->name}_collection as \${$field1->referenced_field->table->name}) {\n";
            $this->code .= "\t\t\t\t\$keys[] = [\n";
            $this->code .= "\t\t\t\t\t'" . str_pad($field2->name . "'", $length +1) . " => ({$field2->referenced_field->casting}) \${$field2->referenced_field->table->name}->{$field2->referenced_field->name},\n";
            $this->code .= "\t\t\t\t\t'" . str_pad($field1->name . "'", $length +1) . " => ({$field1->referenced_field->casting}) \${$field1->referenced_field->table->name}->{$field1->referenced_field->name},\n";
            $this->code .= "\t\t\t\t];\n";
            $this->code .= "\t\t\t}\n";
            $this->code .= "\t\t\treturn entity::dao('" . str_replace('_', '\\', $this->table->name) . "')->delete_multi(\$keys);\n";
            $this->code .= "\t\t}\n\n";
        }
    }