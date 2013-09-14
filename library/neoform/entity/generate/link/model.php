<?php

    namespace neoform\entity\generate\link;

    use neoform\entity\generate;

    class model extends generate\model {

        public function code() {

            $this->code .= '<?php'."\n\n";
            $this->code .= "\tnamespace neoform\\" . str_replace('_', '\\', $this->table->name) . ";\n\n";
            $this->code .= "\tuse neoform\\entity;\n\n";
            $this->class_comments();
            $this->code .= "\tclass model extends entity\\link\\model implements definition {\n\n";

            $this->get();
            $this->references();

            $this->code = substr($this->code, 0, -1);
            $this->code .= "\t}\n";
        }
    }