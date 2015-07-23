<?php

    namespace Neoform\Entity\Generate\Link;

    use Neoform\Entity\Generate;

    class Api extends Generate\Api {

        public function code() {

            $this->code .= '<?php'."\n\n";
            $this->code .= "\tnamespace Neoform\\" . str_replace('_', '\\', $this->table->name)  . ";\n\n";
            $this->code .= "\tuse Neoform\\input;\n";
            $this->code .= "\tuse Neoform\\Entity;\n\n";
            $this->code .= "\tclass Api {\n\n";

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
            $this->code .= "\t\t * @param \\Neoform\\" . str_replace('_', '\\', $field1->referenced_field->table->name) . "\\Model \${$field1->referenced_field->table->name}\n";
            $this->code .= "\t\t * @param \\Neoform\\" . str_replace('_', '\\', $field2->referenced_field->table->name) . "\\Collection \${$field2->referenced_field->table->name}_collection\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return bool\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic static function delete_by_{$field1->referenced_field->table->name}(\\Neoform\\" . str_replace('_', '\\', $field1->referenced_field->table->name) . "\\Model \${$field1->referenced_field->table->name}, \\Neoform\\" . str_replace('_', '\\', $field2->referenced_field->table->name) . "\\Collection \${$field2->referenced_field->table->name}_collection) {\n";
            $this->code .= "\t\t\t\$keys = [];\n";
            $this->code .= "\t\t\tforeach (\${$field2->referenced_field->table->name}_collection as \${$field2->referenced_field->table->name}) {\n";
            $this->code .= "\t\t\t\t\$keys[] = [\n";
            $this->code .= "\t\t\t\t\t'" . str_pad($field1->name . "'", $length +1) . " => ({$field1->referenced_field->casting}) \${$field1->referenced_field->table->name}->{$field1->referenced_field->name},\n";
            $this->code .= "\t\t\t\t\t'" . str_pad($field2->name . "'", $length +1) . " => ({$field2->referenced_field->casting}) \${$field2->referenced_field->table->name}->{$field2->referenced_field->name},\n";
            $this->code .= "\t\t\t\t];\n";
            $this->code .= "\t\t\t}\n";
            $this->code .= "\t\t\treturn Entity::dao('" . str_replace('_', '\\', $this->table->name) . "')->deleteMulti(\$keys);\n";
            $this->code .= "\t\t}\n\n";

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Deletes links\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param \\Neoform\\" . str_replace('_', '\\', $field2->referenced_field->table->name) . "\\Model \${$field2->referenced_field->table->name}\n";
            $this->code .= "\t\t * @param \\Neoform\\" . str_replace('_', '\\', $field1->referenced_field->table->name) . "\\Collection \${$field1->referenced_field->table->name}_collection\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return bool\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic static function delete_by_{$field2->referenced_field->table->name}(\\Neoform\\" . str_replace('_', '\\', $field2->referenced_field->table->name) . "\\Model \${$field2->referenced_field->table->name}, \\Neoform\\" . str_replace('_', '\\', $field1->referenced_field->table->name) . "\\Collection \${$field1->referenced_field->table->name}_collection) {\n";
            $this->code .= "\t\t\t\$keys = [];\n";
            $this->code .= "\t\t\tforeach (\${$field1->referenced_field->table->name}_collection as \${$field1->referenced_field->table->name}) {\n";
            $this->code .= "\t\t\t\t\$keys[] = [\n";
            $this->code .= "\t\t\t\t\t'" . str_pad($field2->name . "'", $length +1) . " => ({$field2->referenced_field->casting}) \${$field2->referenced_field->table->name}->{$field2->referenced_field->name},\n";
            $this->code .= "\t\t\t\t\t'" . str_pad($field1->name . "'", $length +1) . " => ({$field1->referenced_field->casting}) \${$field1->referenced_field->table->name}->{$field1->referenced_field->name},\n";
            $this->code .= "\t\t\t\t];\n";
            $this->code .= "\t\t\t}\n";
            $this->code .= "\t\t\treturn Entity::dao('" . str_replace('_', '\\', $this->table->name) . "')->deleteMulti(\$keys);\n";
            $this->code .= "\t\t}\n\n";
        }
    }