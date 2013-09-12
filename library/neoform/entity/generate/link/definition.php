<?php

    namespace neoform\entity\generate\link;

    use neoform\entity\generate;

    class definition extends generate\definition {

        protected function constants() {

            $this->code .= "\t\tconst NAME        = '" . str_replace('_', ' ', $this->table->name) . " link';\n";
            $this->code .= "\t\tconst TABLE       = '{$this->table->name}';\n";
            $this->code .= "\t\tconst ENTITY_NAME = '" . str_replace('_', '\\', $this->table->name) . "';\n";
        }
    }