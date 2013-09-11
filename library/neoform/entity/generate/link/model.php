<?php

    namespace neoform\entity\generate\link;

    use neoform\entity\generate;

    class model extends generate\model {

        public function code() {

            $this->code .= '<?php'."\n\n";
            $this->class_comments();
            $this->code .= "\tclass {$this->table->name}_model extends entity_link_model implements {$this->table->name}_definition {\n\n";

            $this->get();
            $this->references();

            $this->code = substr($this->code, 0, -1);
            $this->code .= "\t}\n";
        }
    }