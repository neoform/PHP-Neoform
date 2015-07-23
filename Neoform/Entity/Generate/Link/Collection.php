<?php

    namespace Neoform\Entity\Generate\Link;

    use Neoform\Entity\Generate;

    class Collection extends Generate\Collection {

        public function code() {

            $this->code .= '<?php'."\n\n";
            $this->code .= "\tnamespace Neoform\\" . str_replace('_', '\\', $this->table->name) . ";\n\n";
            $this->code .= "\tclass Collection extends \\Neoform\\Entity\\Link\\Collection implements Definition {\n\n";

            $this->code .= "\t}\n";
        }

    }