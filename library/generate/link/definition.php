<?php

    class generate_link_definition extends generate_definition {

        protected function constants() {

            $this->code .= "\t\tconst NAME         = '" . str_replace('_', ' ', $this->table->name) . " link';\n";
            $this->code .= "\t\tconst TABLE        = '" . $this->table->name . "';\n";
            $this->code .= "\t\tconst ENTITY_NAME  = '" . $this->table->name . "';\n";
            $this->code .= "\t\tconst ENTITY_POOL  = 'entities';\n";
            $this->code .= "\t\tconst CACHE_ENGINE = 'redis';\n";
        }

    }