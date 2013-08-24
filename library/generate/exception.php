<?php

    class generate_exception extends generate {

        public function code() {

            $this->code .= '<?php'."\n\n";
            $this->code .= "\tclass " . $this->table->name . "_exception extends entity_exception {\n\n";
            $this->code .= "\t}\n";
        }
    }