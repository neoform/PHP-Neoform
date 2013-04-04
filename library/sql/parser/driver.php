<?php

    abstract class sql_parser_driver {

        /**
         * When these ENUM values are encountered, the model will treat these values as boolean and return true/false instead
         * @var array
         */
        protected static $enum_values = [
            "'yes','no'"     => 'yes',
            "'no','yes'"      => 'yes',
            "'true','false'" => 'true',
            "'false','true'" => 'true',
            "'1','0'"        => '1',
            "'0','1'"        => '1',
            "'on','off'"     => 'on',
            "'off','on'"     => 'on',
            "'y','n'"        => 'y',
            "'t','f'"        => 't',
        ];

        protected $tables = [];

        public function tables() {
            return $this->tables;
        }

        public function dump() {
            $return = [];
            foreach ($this->tables as $table) {
                foreach ($table->fields as $field) {
                    $referencing_fields = [];
                    foreach ($field->referencing_fields as $referencing_field) {
                        $referencing_fields[] = $referencing_field->table->name . '.' . $referencing_field->name;
                    }

                    $return[$field->table->name][$field->name] = [
                        'info'               => $field->info,
                        'referenced_field'   => $field->referenced_field ? $field->referenced_field->table->name . '.' . $field->referenced_field->name : null,
                        'referencing_fields' => $referencing_fields,
                    ];
                }
            }

            return $return;
        }
    }