<?php

    class sql_debug extends pdo {

        protected $queries = [];


        public function prepare($statement, array $driver_options=[]) {
            core::debug($statement);
            $this->queries[] = $statement;
            if (count($driver_options)) {
                return parent::prepare($statement, $driver_options);
            } else {
                return parent::prepare($statement);
            }
        }


        public function queries() {
            return $this->queries;
        }

    }
