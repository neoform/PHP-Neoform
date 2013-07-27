<?php

    class generate_record_definition extends generate_definition {

        protected function constants() {

            $primary_key = $this->table->primary_keys;
            $primary_key = current($primary_key);

            $this->code .= "\t\tconst NAME                = '" . str_replace('_', ' ', $this->table->name) . "';\n";
            $this->code .= "\t\tconst TABLE               = '" . $this->table->name . "';\n";
            $this->code .= "\t\tconst AUTOINCREMENT       = " . ($primary_key->is_auto_increment() ? 'true' : 'false') . ";\n";
            $this->code .= "\t\tconst PRIMARY_KEY         = '" . $primary_key->name . "';\n";
            $this->code .= "\t\tconst BINARY_PK           = " . ($primary_key->is_binary() ? 'true' : 'false') . ";\n";
            $this->code .= "\t\tconst ENTITY_NAME         = '" . $this->table->name . "';\n";
            $this->code .= "\t\tconst CACHE_ENGINE        = null;\n";
            $this->code .= "\t\tconst CACHE_ENGINE_READ   = null;\n";
            $this->code .= "\t\tconst CACHE_ENGINE_WRITE  = null;\n";
            $this->code .= "\t\tconst SOURCE_ENGINE       = null;\n";
            $this->code .= "\t\tconst SOURCE_ENGINE_READ  = null;\n";
            $this->code .= "\t\tconst SOURCE_ENGINE_WRITE = null;\n";
            $this->code .= "\t\tconst USING_LIMIT         = false;\n";
            $this->code .= "\t\tconst USING_COUNT         = false;\n\n";
        }
    }