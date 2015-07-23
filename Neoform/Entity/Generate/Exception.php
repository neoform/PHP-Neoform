<?php

    namespace Neoform\Entity\Generate;

    use Neoform\Entity\Generate;

    class Exception extends Generate {

        public function code() {

            $this->code .= '<?php'."\n\n";
            $this->code .= "\tnamespace Neoform\\" . str_replace('_', '\\', $this->table->name) . ";\n\n";
            $this->code .= "\tclass Exception extends \\Neoform\\Entity\\Exception {\n\n";
            $this->code .= "\t}\n";
        }
    }