<?php

    namespace neoform;

    class generate_record_model extends generate_model {

        public function code() {

            $this->code .= '<?php'."\n\n";
            $this->class_comments();
            $this->code .= "\tclass " . $this->table->name . "_model extends entity_record_model implements " . $this->table->name . "_definition {\n\n";

            //$this->constants();
            $this->get();
            $this->references();

            $this->code = \substr($this->code, 0, -1);
            $this->code .= "\t}\n";
        }

        public function constants() {
            $this->code .= "\t\tconst NAME      = '" . \str_replace('_', ' ', $this->table->name) . "';\n";
            $this->code .= "\t\tconst DAO       = '" . $this->table->name . "_dao';\n";
            $this->code .= "\t\tconst EXCEPTION = '" . $this->table->name . "_exception';\n\n";
        }
    }