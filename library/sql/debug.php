<?php

    /**
     * A wrapper for PDO, useful for debuging queries that are running and failing
     */
    class sql_debug extends pdo {

        protected $queries = [];

        /**
         * Wrapper for prepare() that logs all queries being created
         *
         * @param string $statement
         * @param array  $driver_options
         *
         * @return PDOStatement
         */
        public function prepare($statement, array $driver_options=[]) {
            $this->queries[] = $statement;
            if (count($driver_options)) {
                return parent::prepare($statement, $driver_options);
            } else {
                return parent::prepare($statement);
            }
        }

        /**
         * Retrieves all executed queries
         *
         * @return array
         */
        public function queries() {
            return $this->queries;
        }
    }
