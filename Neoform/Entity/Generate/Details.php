<?php

    namespace Neoform\Entity\Generate;

    use Neoform\Entity\Generate;

    abstract class Details extends Generate {

        abstract protected function extendedGetters();

        public function code() {

            $this->code .= '<?php'."\n\n";
            $this->code .= "\tnamespace {$this->namespace}\\{$this->table->getNameAsClass()};\n\n";

            $this->code .= "\t/**\n";
            $this->code .= "\t * Entity definition trait\n";
            $this->code .= "\t */\n";

            $this->code .= "\ttrait Details {\n\n";

            $this->getters();
            $this->extendedGetters();

            $this->code .= "\t}\n";
        }

        public function getters() {

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Label to identify this entity\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return string\n";
            $this->code .= "\t\t */\n";
            $this->code .= "\t\tpublic static function getLabel() {\n";
            $this->code .= "\t\t\treturn '{$this->table->getNameLabel()}';\n";
            $this->code .= "\t\t}\n\n";

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Name of source identifier (eg, SQL table)\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return string\n";
            $this->code .= "\t\t */\n";
            $this->code .= "\t\tpublic static function getSourceIdentifier() {\n";
            $this->code .= "\t\t\treturn '{$this->table->getName()}';\n";
            $this->code .= "\t\t}\n\n";

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Namespace for this entity\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return string\n";
            $this->code .= "\t\t */\n";
            $this->code .= "\t\tpublic static function getNamespace() {\n";
            $this->code .= "\t\t\treturn '{$this->namespace}\\{$this->table->getNameAsClass()}';\n";
            $this->code .= "\t\t}\n\n";

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Cache key prefix\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return string\n";
            $this->code .= "\t\t */\n";
            $this->code .= "\t\tpublic static function getCacheKeyPrefix() {\n";
            $this->code .= "\t\t\treturn '{$this->namespace}:{$this->table->getNameTitleCase()}';\n";
            $this->code .= "\t\t}\n";
        }
    }
