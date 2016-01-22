<?php

    namespace Neoform\Entity\Generate\Record;

    use Neoform\Entity\Generate;

    class Model extends Generate\Model {

        public function code() {

            $this->code .= '<?php'."\n\n";
            $this->code .= "\tnamespace {$this->namespace}\\{$this->table->getNameAsClass()};\n\n";
            $this->code .= "\tuse Neoform;\n";
            if ($this->namespace !== 'Neoform') {
                $this->code .= "\tuse {$this->namespace};\n";
            }
            $this->code .= "\n";
            $this->class_comments();
            $this->code .= "\tclass Model extends Neoform\\Entity\\Record\\Model {\n\n";
            $this->code .= "\t\t// Load entity details into the class\n";
            $this->code .= "\t\tuse Details;\n\n";

            $this->get('__get');
            $this->get('get');
            $this->getV2();
            $this->references();

            $this->code = substr($this->code, 0, -1);
            $this->code .= "\t}\n";
        }
    }