<?php
    namespace Neoform\Entity\Generate;

    use Neoform\Entity\Generate;

    abstract class Dao extends Generate {

        protected function bindings() {

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * @var array \$fieldBindings list of fields and their corresponding bindings\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return array\n";
            $this->code .= "\t\t */\n";
            $this->code .= "\t\tprotected \$fieldBindings = [\n";
            $longest_part = $this->longestLength($this->table->getFields());

            foreach ($this->table->getFields() as $field) {
                switch ((string) $field->getPdoCasting()) {
                    case 'int':
                        $binding = 'self::TYPE_INTEGER';
                        break;

                    case 'string':
                        $binding = 'self::TYPE_STRING';
                        break;

                    case 'binary':
                        $binding = 'self::TYPE_BINARY';
                        break;

                    case 'bool':
                        $binding = 'self::TYPE_BOOL';
                        break;

                    case 'float':
                        $binding = 'self::TYPE_FLOAT';
                        break;

                    case 'decimal':
                        $binding = 'self::TYPE_DECIMAL';
                        break;

                    default:
                        throw new \Exception("Unknown PDO binding for type \"{$field->getPdoCasting()}\".");
                }

                $this->code .= "\t\t\t'" . str_pad("{$field->getName()}'", $longest_part + 1) . " => {$binding},\n";
            }
            $this->code .= "\t\t];\n\n";

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * @var array \$referencedEntities list of fields (in this entity) and their related foreign Entity\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return array\n";
            $this->code .= "\t\t */\n";

            $relations        = [];
            $referenced_field = [];
//            if ($this->table->getTable()_type() === 'link') {
                foreach ($this->table->getForeignKeys() as $fk) {
                    $referenced_field[]   = $fk;
                    $relations[$fk->getName()] = "{$this->namespace}\\{$fk->getReferencedField()->getTable()->getNameAsClass()}";
                }
//            }

            if ($relations) {
                $this->code .= "\t\tprotected \$referencedEntities = [\n";
                $longest_part = $this->longestLength($referenced_field);
                foreach ($relations as $field => $table) {
                    $this->code .= "\t\t\t'" . str_pad($field . "'", $longest_part + 1) . " => '{$table}',\n";
                }
                $this->code .= "\t\t];\n\n";
            } else {
                $this->code .= "\t\tprotected \$referencedEntities = [];\n\n";
            }

//            $this->code .= "\t\t/**\n";
//            $this->code .= "\t\t * \$var array \$referencing_entities list of fields (in this entity) and their referencing foreign entities\n";
//            $this->code .= "\t\t *\n";
//            $this->code .= "\t\t * @return array\n";
//            $this->code .= "\t\t */\n";
//
//            $relations        = [];
//            $referenced_field = [];
//            foreach ($this->table->getReferencedFields() as $referencing_field) {
//                $referenced_field[] = $referencing_field->getReferencedField();
//                $relations[$referencing_field->getReferencedField()->getName()][] = "'" . str_replace('_', '\\', $referencing_field->getTable()->getName()) . "'";
//            }
//
//            if ($relations) {
//                $this->code .= "\t\tprotected \$referencing_entities = [\n";
//                $longest_part = $this->longestLength($referenced_field);
//                foreach ($relations as $field => $tables) {
//                    $this->code .= "\t\t\t'" . str_pad($field . "'", $longest_part + 1) . " => [ " . join(', ', $tables) . " ],\n";
//                }
//                $this->code .= "\t\t];\n\n";
//            } else {
//                $this->code .= "\t\tprotected \$referencedEntities = [];\n\n";
//            }
        }
    }