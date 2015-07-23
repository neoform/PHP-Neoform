<?php

    namespace Neoform\Entity\Generate\Record;

    use Neoform\Entity\Generate;

    class Definition extends Generate\Definition {

        protected function constants() {

            $primary_key = $this->table->primary_keys;
            $primary_key = reset($primary_key);

            $this->code .= "\t\tconst NAME          = '" . str_replace('_', ' ', $this->table->name) . "';\n";
            $this->code .= "\t\tconst TABLE         = '{$this->table->name}';\n";
            $this->code .= "\t\tconst AUTOINCREMENT = " . ($primary_key->is_auto_increment() ? 'true' : 'false') . ";\n";
            $this->code .= "\t\tconst PRIMARY_KEY   = '{$primary_key->name}';\n";
            $this->code .= "\t\tconst ENTITY_NAME   = '" . str_replace('_', '\\', $this->table->name) . "';\n";
            $this->code .= "\t\tconst CACHE_KEY     = '{$this->table->name}';\n";
        }
    }