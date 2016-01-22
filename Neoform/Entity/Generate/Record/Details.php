<?php

    namespace Neoform\Entity\Generate\Record;

    use Neoform;

    class Details extends Neoform\Entity\Generate\Details {

        protected function extendedGetters() {

            $primaryKey = $this->table->getPrimaryKeys();
            $primaryKey = reset($primaryKey);

            $this->code .= "\n";
            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * The primary key is auto assigned\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return bool\n";
            $this->code .= "\t\t */\n";
            $this->code .= "\t\tpublic static function isPrimaryKeyAutoIncrement() {\n";
            $this->code .= "\t\t\treturn " . ($primaryKey->isAutoIncrement() ? 'true' : 'false') . ";\n";
            $this->code .= "\t\t}\n\n";

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Field name of the primary key\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return string\n";
            $this->code .= "\t\t */\n";
            $this->code .= "\t\tpublic static function getPrimaryKeyName() {\n";
            $this->code .= "\t\t\treturn '{$primaryKey->getName()}';\n";
            $this->code .= "\t\t}\n";
        }
    }
