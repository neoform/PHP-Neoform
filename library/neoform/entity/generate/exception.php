<?php

    namespace neoform\entity\generate;

    use neoform\entity\generate;

    class exception extends generate {

        public function code() {

            $this->code .= '<?php'."\n\n";
            $this->code .= "\tnamespace neoform\\" . str_replace('_', '\\', $this->table->name) . ";\n\n";
            $this->code .= "\tclass exception extends \\neoform\\entity\\exception {\n\n";
            $this->code .= "\t}\n";
        }
    }