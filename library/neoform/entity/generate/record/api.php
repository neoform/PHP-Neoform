<?php

    namespace neoform\entity\generate\record;

    use neoform\entity\generate;

    class api extends generate\api {

        public function code() {

            $this->code .= '<?php'."\n\n";
            $this->code .= "\tnamespace neoform\\" . str_replace('_', '\\', $this->table->name)  . ";\n\n";
            $this->code .= "\tuse neoform\\input;\n";
            $this->code .= "\tuse neoform\\entity;\n\n";
            $this->code .= "\tclass api {\n\n";

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
            $this->code .= "\t\tpublic static function delete(model \${$this->table->name}) {\n";
            $this->code .= "\t\t\treturn entity::dao('neoform\\" . str_replace('_', '\\', $this->table->name) . "')->delete(\${$this->table->name});\n";
            $this->code .= "\t\t}\n\n";
        }
    }