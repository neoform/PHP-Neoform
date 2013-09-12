<?php

    namespace neoform\entity\generate;

    use neoform\entity\generate;

    class lib extends generate {

        public function code() {

            $this->code .= '<?php'."\n\n";
            $this->code .= "\tnamespace neoform\\" . str_replace('_', '\\', $this->table->name) . ";\n\n";
            $this->code .= "\tclass lib {\n\n";
            $this->code .= "\t}\n";
        }
    }
