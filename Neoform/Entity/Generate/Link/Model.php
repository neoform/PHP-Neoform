<?php

    namespace Neoform\Entity\Generate\Link;

    use Neoform\Entity\Generate;

    class Model extends Generate\Model {

        public function code() {

            $this->code .= '<?php'."\n\n";
            $this->code .= "\tnamespace {$this->namespace}\\{$this->table->getNameAsClass()};\n\n";
            $this->code .= "\tuse Neoform\\Entity;\n";
            if ($this->namespace !== 'Neoform') {
                $this->code .= "\tuse {$this->namespace};\n";
            }
            $this->code .= "\n";
            $this->class_comments();
            $this->code .= "\tclass Model extends Entity\\Link\\Model {\n\n";
            $this->code .= "\t\t// Load entity details into the class\n";
            $this->code .= "\t\tuse Details;\n\n";

            $this->get('__get');
            $this->get('get');
            $this->references();

            $this->code = substr($this->code, 0, -1);
            $this->code .= "\t}\n";
        }
    }