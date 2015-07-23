<?php

    namespace Neoform\Entity\Generate;

    use Neoform\Entity\Generate;

    class Definition extends Generate {

        public function code() {

            $this->code .= '<?php'."\n\n";
            $this->code .= "\tnamespace Neoform\\" . str_replace('_', '\\', $this->table->name) . ";\n\n";

            $this->code .= "\t/**\n";
            $this->code .= "\t * Entity definition interface\n";
            $this->code .= "\t */\n";

            $this->code .= "\tinterface Definition {\n\n";

            $this->constants();

            $this->code .= "\t}\n";
        }
    }
