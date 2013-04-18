<?php

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
         * Bind values to a query based on the castings.
         * This is needed because of binary data fields, which need to be specially bound as PDO::PARAM_LOB in Postgres
         *
         * @param PDOStatement $query
         * @param array        $castings
         * @param array        $vals
         * @param bool         $bind_as_param
         */
        public static function bind_by_casting(PDOStatement $query, $castings, $vals, $bind_as_param=false) {
            if ($bind_as_param) {
                foreach ($vals as $k => $v) {
                    switch ($castings[$k]) {
                        case 'int':
                            $query->bindParam($k, $v, PDO::PARAM_INT);
                            break;

                        case 'string':
                            $query->bindParam($k, $v, PDO::PARAM_STR);
                            break;

                        case 'binary':
                            $query->bindParam(utf8_encode($k), $v, PDO::PARAM_LOB);
                            break;

                        case 'bool':
                            $query->bindParam($k, $v, PDO::PARAM_BOOL);
                            break;

                        case 'null':
                            $query->bindParam($k, $v, PDO::PARAM_NULL);
                            break;

                        default:
                            $query->bindParam($k, $v, PDO::PARAM_STR);
                            break;
                    }
                }
            } else {
                $i = 1;
                foreach ($vals as $k => $v) {
                    switch ($castings[$k]) {
                        case 'int':
                            $query->bindValue($i++, $v, PDO::PARAM_INT);
                            break;

                        case 'string':
                            $query->bindValue($i++, $v, PDO::PARAM_STR);
                            break;

                        case 'binary':
                            $query->bindValue($i++, $v, PDO::PARAM_LOB);
                            break;

                        case 'bool':
                            $query->bindValue($i++, $v, PDO::PARAM_BOOL);
                            break;

                        case 'null':
                            $query->bindValue($i++, $v, PDO::PARAM_NULL);
                            break;

                        default:
                            $query->bindParam($i++, $v, PDO::PARAM_STR);
                            break;
                    }
                }
            }
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
    }