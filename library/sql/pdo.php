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
         * Fetch the PDO binding
         *
         * @param array        $castings
         * @param string       $name
         *
         * @return int PDO param val
         */
        public static function pdo_binding(array $castings, $name) {
           switch ($castings[$name]) {
                case 'int':
                    return PDO::PARAM_INT;

                case 'string':
                    return PDO::PARAM_STR;

                case 'binary':
                    return PDO::PARAM_LOB;

                case 'bool':
                    return PDO::PARAM_BOOL;

                case 'null':
                    return PDO::PARAM_NULL;
            }
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
        public static function bind_by_casting(PDOStatement $query, array $castings, array $vals, $bind_as_param=false) {
            if ($bind_as_param) {
                foreach ($vals as $k => &$v) { // do NOT remove this reference, it will break the bindParam() function
                    switch ($castings[$k]) {
                        case 'int':
                            $query->bindParam($k, $v, PDO::PARAM_INT);
                            break;

                        case 'string':
                            $query->bindParam($k, $v, PDO::PARAM_STR);
                            break;

                        case 'binary':
                            $query->bindParam($k, $v, PDO::PARAM_LOB);
                            break;

                        case 'bool':
                            $query->bindParam($k, $v, PDO::PARAM_BOOL);
                            break;

                        case 'null':
                            $query->bindParam($k, $v, PDO::PARAM_NULL);
                            break;

                        default:
                            $query->bindParam($k, $v);
                            break;
                    }
                }
            } else {
                $i = 1;
                foreach ($vals as $k => &$v) { // do NOT remove this reference, it will break the bindParam() function
                    switch ($castings[$k]) {
                        case 'int':
                            $query->bindParam($i++, $v, PDO::PARAM_INT);
                            break;

                        case 'string':
                            $query->bindParam($i++, $v, PDO::PARAM_STR);
                            break;

                        case 'binary':
                            $query->bindParam($i++, $v, PDO::PARAM_LOB);
                            break;

                        case 'bool':
                            $query->bindParam($i++, $v, PDO::PARAM_BOOL);
                            break;

                        case 'null':
                            $query->bindParam($i++, $v, PDO::PARAM_NULL);
                            break;

                        default:
                            $query->bindParam($i++, $v);
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

        /**
         * Dumps a raw sql query
         *
         * @param PDOStatement $query
         *
         * @return string
         * @throws Exception
         */
        public static function dump_query(PDOStatement$query) {
            ob_start();

            $query->debugDumpParams();

            try {
                $buffer = '';
                while (ob_get_length()) {
                    $buffer .= ob_get_clean();
                }
                return $buffer;
            } catch (Exception $e) {
                throw new Exception('Output buffer error occurred');
            }
        }
    }