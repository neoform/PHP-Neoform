<?php

    namespace Neoform\Entity\Generate\Link;

    use Neoform\Entity\Generate;

    class Api extends Generate\Api {

        public function code() {

            $this->code .= '<?php'."\n\n";
            $this->code .= "\tnamespace {$this->namespace}\\{$this->table->getNameAsClass()};\n\n";
            $this->code .= "\tuse Neoform;\n";
            if ($this->namespace !== 'Neoform') {
                $this->code .= "\tuse {$this->namespace};\n";
            }
            $this->code .= "\n";
            $this->code .= "\tclass Api extends Neoform\\Input\\Api {\n\n";

            $this->create();
            $this->delete();

            $this->code = substr($this->code, 0, -1);
            $this->code .= "\t}\n";
        }

        public function delete() {

            $fks = array_values($this->table->getForeignKeys());

            $field1 = $fks[0];
            $field2 = $fks[1];

            $length = max(strlen($field1->getName()), strlen($field2->getName()));

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Deletes links\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param {$this->namespace}\\{$field1->getReferencedField()->getTable()->getNameAsClass()}\\Model \${$field1->getReferencedField()->getTable()->getNameCamelCase()}\n";
            $this->code .= "\t\t * @param {$this->namespace}\\{$field2->getReferencedField()->getTable()->getNameAsClass()}\\Collection \${$field2->getReferencedField()->getTable()->getNameCamelCase()}Collection\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return bool\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function deleteBy{$field1->getReferencedField()->getTable()->getNameTitleCase()}({$this->namespace}\\{$field1->getReferencedField()->getTable()->getNameAsClass()}\\Model \${$field1->getReferencedField()->getTable()->getNameCamelCase()}, {$this->namespace}\\{$field2->getReferencedField()->getTable()->getNameAsClass()}\\Collection \${$field2->getReferencedField()->getTable()->getNameCamelCase()}Collection) {\n";
            $this->code .= "\t\t\t\$keys = [];\n";
            $this->code .= "\t\t\tforeach (\${$field2->getReferencedField()->getTable()->getNameCamelCase()}Collection as \${$field2->getReferencedField()->getTable()->getNameCamelCase()}) {\n";
            $this->code .= "\t\t\t\t\$keys[] = [\n";
            $this->code .= "\t\t\t\t\t'" . str_pad($field1->getName() . "'", $length +1) . " => ({$field1->getReferencedField()->getCasting()}) \${$field1->getReferencedField()->getTable()->getNameCamelCase()}->get{$field1->getReferencedField()->getNameTitleCase()}(),\n";
            $this->code .= "\t\t\t\t\t'" . str_pad($field2->getName() . "'", $length +1) . " => ({$field2->getReferencedField()->getCasting()}) \${$field2->getReferencedField()->getTable()->getNameCamelCase()}->get{$field2->getReferencedField()->getNameTitleCase()}(),\n";
            $this->code .= "\t\t\t\t];\n";
            $this->code .= "\t\t\t}\n";
            $this->code .= "\t\t\treturn Dao::get()->deleteMulti(\$keys);\n";
            $this->code .= "\t\t}\n\n";

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Deletes links\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param {$this->namespace}\\{$field2->getReferencedField()->getTable()->getNameAsClass()}\\Model \${$field2->getReferencedField()->getTable()->getNameCamelCase()}\n";
            $this->code .= "\t\t * @param {$this->namespace}\\{$field1->getReferencedField()->getTable()->getNameAsClass()}\\Collection \${$field1->getReferencedField()->getTable()->getNameCamelCase()}Collection\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return bool\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function deleteBy{$field2->getReferencedField()->getTable()->getNameTitleCase()}({$this->namespace}\\{$field2->getReferencedField()->getTable()->getNameAsClass()}\\Model \${$field2->getReferencedField()->getTable()->getNameCamelCase()}, {$this->namespace}\\{$field1->getReferencedField()->getTable()->getNameAsClass()}\\Collection \${$field1->getReferencedField()->getTable()->getNameCamelCase()}Collection) {\n";
            $this->code .= "\t\t\t\$keys = [];\n";
            $this->code .= "\t\t\tforeach (\${$field1->getReferencedField()->getTable()->getNameCamelCase()}Collection as \${$field1->getReferencedField()->getTable()->getNameCamelCase()}) {\n";
            $this->code .= "\t\t\t\t\$keys[] = [\n";
            $this->code .= "\t\t\t\t\t'" . str_pad($field2->getName() . "'", $length +1) . " => ({$field2->getReferencedField()->getCasting()}) \${$field2->getReferencedField()->getTable()->getNameCamelCase()}->get{$field2->getReferencedField()->getNameTitleCase()}(),\n";
            $this->code .= "\t\t\t\t\t'" . str_pad($field1->getName() . "'", $length +1) . " => ({$field1->getReferencedField()->getCasting()}) \${$field1->getReferencedField()->getTable()->getNameCamelCase()}->get{$field1->getReferencedField()->getNameTitleCase()}(),\n";
            $this->code .= "\t\t\t\t];\n";
            $this->code .= "\t\t\t}\n";
            $this->code .= "\t\t\treturn Dao::get()->deleteMulti(\$keys);\n";
            $this->code .= "\t\t}\n\n";
        }
    }