<?php

    namespace Neoform\Entity\Generate;

    use Neoform\Entity\Generate;

    class Exception extends Generate {

        public function code() {

            $this->code .= '<?php'."\n\n";
            $this->code .= "\tnamespace {$this->namespace}\\{$this->table->getNameAsClass()};\n\n";
            $this->code .= "\tuse Neoform;\n\n";
            $this->code .= "\tclass Exception extends Neoform\\Entity\\Exception {\n\n";
            $this->code .= "\t}\n";
        }
    }