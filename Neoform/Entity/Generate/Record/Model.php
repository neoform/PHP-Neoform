<?php

    namespace Neoform\Entity\Generate\Record;

    use Neoform\Entity\Generate;

    class Model extends Generate\Model {

        public function code() {

            $this->code .= '<?php'."\n\n";
            $this->code .= "\tnamespace Neoform\\" . str_replace('_', '\\', $this->table->name) . ";\n\n";
            $this->code .= "\tuse Neoform\\Entity;\n\n";
            $this->class_comments();
            $this->code .= "\tclass Model extends Entity\\Record\\Model implements Definition {\n\n";

            $this->get();
            $this->references();

            $this->code = substr($this->code, 0, -1);
            $this->code .= "\t}\n";
        }
    }