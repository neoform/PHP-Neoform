<?php

    namespace Neoform\Entity\Generate\Record;

    use Neoform\Entity\Generate;

    class Api extends Generate\Api {

        public function code() {

            $this->code .= '<?php'."\n\n";
            $this->code .= "\tnamespace Neoform\\" . str_replace('_', '\\', $this->table->name)  . ";\n\n";
            $this->code .= "\tuse Neoform\\input;\n";
            $this->code .= "\tuse Neoform\\Entity;\n\n";
            $this->code .= "\tclass Api {\n\n";

            $this->create();
            $this->update();
            $this->delete();
            //$this->validate_lookup();
            $this->validate_insert();
            $this->validate_update();

            $this->code = substr($this->code, 0, -1);
            $this->code .= "\t}\n";
        }

        public function delete() {

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Delete a " . ucwords(str_replace('_', ' ', $this->table->name)) . "\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param model \${$this->table->name}\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return bool\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic static function delete(Model \${$this->table->name}) {\n";
            $this->code .= "\t\t\treturn Entity::dao('" . str_replace('_', '\\', $this->table->name) . "')->delete(\${$this->table->name});\n";
            $this->code .= "\t\t}\n\n";
        }
    }