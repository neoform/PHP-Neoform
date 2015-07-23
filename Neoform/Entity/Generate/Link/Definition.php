<?php

    namespace Neoform\Entity\Generate\Link;

    use Neoform\Entity\Generate;

    class Definition extends Generate\Definition {

        protected function constants() {

            $this->code .= "\t\tconst NAME        = '" . str_replace('_', ' ', $this->table->name) . " link';\n";
            $this->code .= "\t\tconst TABLE       = '{$this->table->name}';\n";
            $this->code .= "\t\tconst ENTITY_NAME = '" . str_replace('_', '\\', $this->table->name) . "';\n";
            $this->code .= "\t\tconst CACHE_KEY   = '{$this->table->name}';\n";
        }
    }