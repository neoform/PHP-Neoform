<?php

    namespace neoform\entity\generate;

    use neoform\entity\generate;

    class definition extends generate {

        public function code() {

            $this->code .= '<?php'."\n\n";
            $this->code .= "\tnamespace neoform\\" . str_replace('_', '\\', $this->table->name) . ";\n\n";

            $this->code .= "\t/**\n";
            $this->code .= "\t * Entity definition interface\n";
            $this->code .= "\t */\n";

            $this->code .= "\tinterface definition {\n\n";

            $this->constants();

            $this->code .= "\t}\n";
        }
    }
