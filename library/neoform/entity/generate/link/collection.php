<?php

    namespace neoform\entity\generate\link;

    use neoform\entity\generate;

    class collection extends generate\collection {

        public function code() {

            $this->code .= '<?php'."\n\n";
            $this->code .= "\tnamespace neoform\\" . str_replace('_', '\\', $this->table->name) . ";\n\n";
            $this->code .= "\tclass collection extends \\neoform\\entity\\link\\collection implements definition {\n\n";

            $this->code .= "\t}\n";
        }

    }