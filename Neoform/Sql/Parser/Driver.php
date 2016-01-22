<?php

    namespace Neoform\Sql\Parser;

    abstract class Driver {

        /**
         * When these ENUM values are encountered, the model will treat these values as boolean and return true/false instead
         * @var array
         */
        protected static $enum_values = [
            "'yes','no'"     => 'yes',
            "'no','yes'"     => 'yes',
            "'true','false'" => 'true',
            "'false','true'" => 'true',
            "'1','0'"        => '1',
            "'0','1'"        => '1',
            "'on','off'"     => 'on',
            "'off','on'"     => 'on',
            "'y','n'"        => 'y',
            "'t','f'"        => 't',
        ];

        /**
         * @var Table[]
         */
        protected $tables = [];

        /**
         * @return Table[]
         */
        public function tables() {
            return $this->tables;
        }

        public function dump() {
            $return = [];
            foreach ($this->tables as $table) {
                foreach ($table->getFields() as $field) {
                    $referencingFields = [];
                    foreach ($field->getReferencedFields() as $referencingField) {
                        $referencingFields[] = "{$referencingField->getTable()->getName()}.{$referencingField->getName()}";
                    }

                    $return[$field->getTable()->getName()][$field->getName()] = [
                        'info'              => $field->getInfo(),
                        'referencedField'   => $field->getReferencedField() ? "{$field->getReferencedField()->getTable()->getName()}.{$field->getReferencedField()->getName()}" : null,
                        'referencingFields' => $referencingFields,
                    ];
                }
            }

            return $return;
        }
    }