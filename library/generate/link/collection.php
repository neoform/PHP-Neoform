<?php

    class generate_entity_link_collection extends generate_collection {

        public function code() {

            $this->code .= '<?php'."\n\n";
            $this->code .= "\tclass " . $this->table->name . "_collection extends entity_link_collection implements " . $this->table->name . "_defintion {\n\n";

            $this->code .= "\t}\n";
        }

    }