<?php

    namespace Neoform\Entity\Generate\Record;

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
            $this->update();
            $this->delete();

            $this->code = substr($this->code, 0, -1);
            $this->code .= "\t}\n";
        }

        public function delete() {

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Delete a {$this->table->getNameLabel()}\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param Model \${$this->table->getNameCamelCase()}\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return bool\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function delete(Model \${$this->table->getNameCamelCase()}) {\n";
            $this->code .= "\t\t\treturn Dao::get()->delete(\${$this->table->getNameCamelCase()});\n";
            $this->code .= "\t\t}\n\n";
        }
    }