<?php

    namespace neoform;

    use PDO;

    class sql_pdo extends pdo {

        protected $driver_name;

        /**
         * Get the name of the current driver being used by PDO (eg, mysql, pgsql)
         *
         * @return mixed
         */
        public function driver() {
            if (! $this->driver_name) {
                $this->driver_name = $this->getAttribute(PDO::ATTR_DRIVER_NAME);
            }
            return $this->driver_name;
        }

        /**
         * Converts binary string resource into a string
         *
         * @param $mixed
         */
        public static function unbinary(& $mixed) {
            if (is_array($mixed)) {
                foreach ($mixed as & $val) {
                    self::unbinary($val);
                }
            } else if (is_resource($mixed)) {
                $mixed = stream_get_contents($mixed);
            }
        }

        /**
         * Dumps a raw sql query
         *
         * @param \PDOStatement $query
         *
         * @return string
         * @throws \exception
         */
        public static function dump_query(\PDOStatement $query) {
            ob_start();

            $query->debugDumpParams();

            try {
                $buffer = '';
                while (ob_get_length()) {
                    $buffer .= ob_get_clean();
                }
                return $buffer;
            } catch (\exception $e) {
                throw new \exception('Output buffer error occurred');
            }
        }
    }