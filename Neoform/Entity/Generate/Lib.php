<?php

    namespace Neoform\Entity\Generate;

    use Neoform\Entity\Generate;

    class Lib extends Generate {

        public function code() {

            $this->code .= '<?php'."\n\n";
            $this->code .= "\tnamespace {$this->namespace}\\{$this->table->getNameAsClass()};\n\n";
            $this->code .= "\tclass Lib {\n\n";
            $this->code .= "\t}\n";
        }
    }
