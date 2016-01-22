<?php

    namespace Neoform\Entity\Generate\Record\Validator;

    use Neoform\Entity\Generate;

    class Insert extends Generate\Validator\Insert {

        public function code() {

            $this->code .= '<?php'."\n\n";
            $this->code .= "\tnamespace {$this->namespace}\\{$this->table->getNameAsClass()}\\Validator;\n\n";
            $this->code .= "\tuse Neoform;\n";
            if ($this->namespace !== 'Neoform') {
                $this->code .= "\tuse {$this->namespace};\n";
            }
            $this->code .= "\n";
            $this->code .= "\tclass Insert implements Neoform\\Input\\Validator {\n\n";

            $this->validateInsert();

            $this->code = substr($this->code, 0, -1);
            $this->code .= "\t}\n";
        }
    }