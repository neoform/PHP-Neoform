<?php

    namespace neoform\entity\generate;

    use neoform\entity\generate;

    class lib extends generate {

        public function code() {

            $this->code .= '<?php'."\n\n";
            $this->code .= "\tclass {$this->table->name}_lib {\n\n";

            $this->code .= "\t}\n";
        }
    }
