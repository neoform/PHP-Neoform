<?php

    /**
     * Postgres entity_record_dao driver
     */
    class entity_record_driver_pgsql implements entity_record_driver {

        protected static $binding_conversions = [
            entity_record_dao::TYPE_STRING  => PDO::PARAM_STR,
            entity_record_dao::TYPE_INTEGER => PDO::PARAM_INT,
            entity_record_dao::TYPE_BINARY  => PDO::PARAM_LOB,
            entity_record_dao::TYPE_FLOAT   => PDO::PARAM_STR,
            entity_record_dao::TYPE_DECIMAL => PDO::PARAM_STR,
            entity_record_dao::TYPE_BOOL    => PDO::PARAM_BOOL,
        ];

        /**
         * Parse the table name into a properly escaped table string
         *
         * @param string $table
         *
         * @return string
         */
        protected static function table($table) {
            if (strpos($table, '.') !== false) {
                $table = explode('.', $table);
                return "{$table[0]}\".\"{$table[1]}";
            } else {
                return $table;
            }
        }

        /**
         * Get full record by primary key
         *
         * @param entity_record_dao $dao
         * @param string            $pool which source engine pool to use
         * @param int|string|null   $pk
         *
         * @return mixed
         */
        public static function record(entity_record_dao $dao, $pool, $pk) {

            $info = core::sql($pool)->prepare("
                SELECT *
                FROM \"" . self::table($dao::TABLE) . "\"
                WHERE \"" . $dao::PRIMARY_KEY . "\" = ?
            ");

            $info->bindValue(1, $pk, self::$binding_conversions[$dao->field_binding($dao::PRIMARY_KEY)]);
            $info->execute();

            if ($info = $info->fetch()) {
                sql_pdo::unbinary($info);
                return $info;
            }
        }

        /**
         * Get full records by primary key
         *
         * @param entity_record_dao $dao
         * @param string            $pool which source engine pool to use
         * @param array             $pks
         *
         * @return array
         */
        public static function records(entity_record_dao $dao, $pool, array $pks) {

            $infos_rs = core::sql($pool)->prepare("
                SELECT *
                FROM \"" . self::table($dao::TABLE) . "\"
                WHERE \"" . $dao::PRIMARY_KEY . "\" IN (" . join(',', array_fill(0, count($pks), '?')) . ")
            ");

            $pdo_binding = self::$binding_conversions[$dao->field_binding($dao::PRIMARY_KEY)];
            foreach (array_values($pks) as $i => $pk) {
                $infos_rs->bindValue($i + 1, $pk, $pdo_binding);
            }
            $infos_rs->execute();

            $infos = [];
            foreach ($infos_rs->fetchAll() as $info) {
                $k = array_search($info[$dao::PRIMARY_KEY], $pks);
                if ($k !== false) {
                    $infos[$k] = $info;
                }
            }

            sql_pdo::unbinary($infos);

            return $infos;
        }

        /**
         * Get a count based on key inputs
         *
         * @param entity_record_dao $dao
         * @param string            $pool
         * @param array             $fieldvals
         *
         * @return integer
         */
        public static function count(entity_record_dao $dao, $pool, array $fieldvals=null) {
            $where = [];
            $vals  = [];

            if ($fieldvals) {
                foreach ($fieldvals as $k => $v) {
                    if ($v === null) {
                        $where[] = "\"{$k}\" IS NULL";
                    } else {
                        $vals[]  = $v;
                        $where[] = "\"{$k}\" = ?";
                    }
                }
            }

            $rs = core::sql($pool)->prepare("
                SELECT COUNT(*) \"num\"
                FROM \"" . self::table($dao::TABLE) . "\"
                " . ($where ? " WHERE " . join(" AND ", $where) : '') . "
            ");
            $rs->execute($vals);
            return (int) $rs->fetch()['num'];
        }

        /**
         * Get multiple counts
         *
         * @param entity_record_dao $self
         * @param string            $pool
         * @param array             $fieldvals_arr
         *
         * @return array
         */
        public static function count_multi(entity_record_dao $self, $pool, array $fieldvals_arr) {
            $queries = [];
            $vals    = [];

            foreach ($fieldvals_arr as $fieldvals) {
                $where = [];
                foreach ($fieldvals as $field => $val) {
                    if ($val === null) {
                        $where[] = "\"{$field}\" IS NULL";
                    } else {
                        $vals[]  = $val;
                        $where[] = "\"{$field}\" = ?";
                    }
                }

                $queries[] = "(
                    SELECT COUNT(*) \"num\"
                    FROM \"" . self::table($self::TABLE) . "\"
                    " . ($where ? " WHERE " . join(" AND ", $where) : '') . "
                )";
            }

            $rs = core::sql($pool)->prepare(join(' UNION ', $queries));
            $rs->execute($vals);

            $keys   = array_keys($fieldvals_arr);
            $counts = [];
            foreach ($rs->fetchAll(PDO::FETCH_COLUMN, 0) as $k => $count) {
                $counts[$keys[$k]] = (int) $count;
            }
            return $counts;
        }

        /**
         * Get all records in the table
         *
         * @param entity_record_dao $dao
         * @param string            $pool which source engine pool to use
         * @param int|string        $pk
         * @param array             $fieldvals
         *
         * @return array
         */
        public static function all(entity_record_dao $dao, $pool, $pk, array $fieldvals=null) {
            $where = [];
            $vals  = [];

            if ($fieldvals) {
                foreach ($fieldvals as $field => $val) {
//                    if (is_array($val) && $val) {
//                        foreach ($val as $arr_v) {
//                            $vals[] = $arr_v;
//                        }
//                        $where[] = "\"{$field}\" IN(" . join(',', array_fill(0, count($v), '?')) . ")";
//                    } else {
                        if ($val === null) {
                            $where[] = "\"{$field}\" IS NULL";
                        } else {
                            $vals[$field] = $val;
                            $where[]      = "\"{$field}\" = ?";
                        }
//                    }
                }
            }

            $info = core::sql($pool)->prepare("
                SELECT *
                FROM \"" . self::table($dao::TABLE) . "\"
                " . ($where ? " WHERE " . join(" AND ", $where) : "") . "
                ORDER BY \"{$pk}\" ASC
            ");

            $bindings = $dao->field_bindings();

            // do NOT remove this reference, it will break the bindParam() function
            foreach ($vals as $k => &$v) {
                $info->bindParam($k, $v, self::$binding_conversions[$bindings[$k]]);
            }

            $info->execute();

            $infos = array_column($info->fetchAll(), null, $pk);
            sql_pdo::unbinary($infos);

            return $infos;
        }

        /**
         * Get record primary key by fields
         *
         * @param entity_record_dao $dao
         * @param string            $pool which source engine pool to use
         * @param array             $fieldvals
         * @param int|string        $pk
         *
         * @return array
         */
        public static function by_fields(entity_record_dao $dao, $pool, array $fieldvals, $pk) {
            $where = [];
            $vals  = [];

            if ($fieldvals) {
                foreach ($fieldvals as $field => $val) {
                    if ($val === null) {
                        $where[] = "\"{$field}\" IS NULL";
                    } else {
                        $vals[$field] = $val;
                        $where[]      = "\"{$field}\" = ?";
                    }
                }
            }

            $rs = core::sql($pool)->prepare("
                SELECT \"{$pk}\"
                FROM \"" . self::table($dao::TABLE) . "\"
                " . ($where ? " WHERE " . join(" AND ", $where) : "") . "
            ");

            $bindings = $dao->field_bindings();

            // do NOT remove this reference, it will break the bindParam() function
            foreach ($vals as $k => &$v) {
                $rs->bindParam($k, $v, self::$binding_conversions[$bindings[$k]]);
            }

            $rs->execute();

            $rs = $rs->fetchAll();

            $pks = $rs->fetchAll(PDO::FETCH_COLUMN, 0);
            sql_pdo::unbinary($pks);

            return $pks;
        }

        /**
         * Get multiple record primary keys by fields
         *
         * @param entity_record_dao $dao
         * @param string            $pool which source engine pool to use
         * @param array             $fieldvals_arr
         * @param int|string        $pk
         *
         * @return array
         */
        public static function by_fields_multi(entity_record_dao $dao, $pool, array $fieldvals_arr, $pk) {
            $select_fields  = [ "\"{$pk}\"" ];
            $reverse_lookup = [];
            $return         = [];
            $vals           = [];
            $where          = [];
            $fields         = array_keys(reset($fieldvals_arr));

            foreach ($fields as $k) {
                $select_fields[] = "\"{$k}\"";
            }

            foreach ($fieldvals_arr as $k => $fieldvals) {
                $w             = [];
                $return[$k]    = [];
                $hashed_valued = [];
                foreach ($fieldvals as $field => $val) {
                    if ($val === null) {
                        $w[]             = "\"{$field}\" IS NULL";
                        $hashed_valued[] = '';
                    } else {
                        $vals[]          = $val;
                        $w[]             = "\"{$field}\" = ?";
                        $hashed_valued[] = md5($val);
                    }
                }
                $reverse_lookup[join(':', $hashed_valued)] = $k;
                $where[] = '(' . join(" AND ", $w) . ')';
            }

            $rs = core::sql($pool)->prepare("
                SELECT " . join(", ", $select_fields) . "
                FROM \"" . self::table($dao::TABLE) . "\"
                WHERE " . join(" OR ", $where) . "
            ");

            $rs->execute($vals);

            foreach ($rs->fetchAll() as $row) {
                $hashed = [];
                foreach ($fields as $k) {
                    $hashed[$row[$k]] = md5($row[$k]);
                }
                $return[$reverse_lookup[join(':', $hashed)]][] = $row[$pk];
            }

            sql_pdo::unbinary($return);

            return $return;
        }

        /**
         * Get a set of PKs based on params, in a given order and offset/limit
         *
         * @param entity_record_dao $dao
         * @param string            $pool
         * @param array             $fieldvals
         * @param mixed             $pk
         * @param array             $order_by
         * @param integer|null      $offset
         * @param integer           $limit
         *
         * @return mixed
         */
        public static function by_fields_offset(entity_record_dao $dao, $pool, array $fieldvals, $pk, array $order_by, $offset, $limit) {
            $where = [];
            $vals  = [];

            if ($fieldvals) {
                foreach ($fieldvals as $field => $val) {
                    if ($val === null) {
                        $where[] = "\"{$field}\" IS NULL";
                    } else {
                        $vals[]  = $val;
                        $where[] = "\"{$field}\" = ?";
                    }
                }
            }

            if ($limit) {
                $limit = "LIMIT {$limit}" . ($offset !== null ? " OFFSET {$offset}" : '');
            } else {
                $limit = '';
            }

            $order = [];
            foreach ($order_by as $field => $sort_direction) {
                $order[] = "\"{$field}\" " . (entity_dao::SORT_DESC === $sort_direction ? 'DESC' : 'ASC');
            }
            $order_by = join(', ', $order);

            $rs = core::sql($pool)->prepare("
                SELECT \"{$pk}\"
                FROM \"" . self::table($dao::TABLE) . "\"
                " . ($where ? " WHERE " . join(" AND ", $where) : '') . "
                ORDER BY {$order_by}
                {$limit}
            ");
            $rs->execute($vals);

            return $rs->fetchAll(PDO::FETCH_COLUMN, 0);
        }

        /**
         * Get multiple sets of PKs based on params, in a given order and offset/limit
         *
         * @param entity_record_dao $dao
         * @param string            $pool
         * @param array             $fieldvals_arr
         * @param mixed             $pk
         * @param array             $order_by
         * @param integer|null      $offset
         * @param integer           $limit
         *
         * @return array
         */
        public static function by_fields_offset_multi(entity_record_dao $dao, $pool, array $fieldvals_arr, $pk, array $order_by, $offset, $limit) {
            $select_fields  = [ "\"{$pk}\"" ];
            $reverse_lookup = [];
            $return         = [];
            $vals           = [];
            $queries        = [];
            $fields         = array_keys(reset($fieldvals_arr));

            foreach ($fields as $k) {
                $select_fields[] = "\"{$k}\"";
            }

            if ($limit) {
                $limit = "LIMIT {$limit}" . ($offset !== null ? " OFFSET {$offset}" : '');
            } else {
                $limit = '';
            }

            $order = [];
            foreach ($order_by as $field => $sort_direction) {
                $order[] = "\"{$field}\" " . (entity_dao::SORT_DESC === $sort_direction ? 'DESC' : 'ASC');
            }
            $order_by = join(', ', $order);

            foreach ($fieldvals_arr as $k => $fieldvals) {
                $where         = [];
                $return[$k]    = [];
                $hashed_valued = [];
                foreach ($fieldvals as $field => $val) {
                    if ($val === null) {
                        $where[]         = "\"{$field}\" IS NULL";
                        $hashed_valued[] = '';
                    } else {
                        $vals[]          = $val;
                        $where[]         = "\"{$field}\" = ?";
                        $hashed_valued[] = md5($val);
                    }
                }
                $reverse_lookup[join(':', $hashed_valued)] = $k;

                $queries[] = "(
                    SELECT " . join(", ", $select_fields) . "
                    FROM \"" . self::table($dao::TABLE) . "\"
                    WHERE " . join(" AND ", $where) . "
                    ORDER BY {$order_by}
                    {$limit}
                )";
            }

            $rs = core::sql($pool)->prepare(
                join(" UNION ", $queries)
            );

            $rs->execute($vals);

            foreach ($rs->fetchAll() as $row) {
                $hashed = [];
                foreach ($fields as $k) {
                    $hashed[$row[$k]] = md5($row[$k]);
                }
                $return[$reverse_lookup[join(':', $hashed)]][] = $row[$pk];
            }

            return $return;
        }

        /**
         * Insert record
         *
         * @param entity_record_dao $dao
         * @param string            $pool which source engine pool to use
         * @param array             $info
         * @param bool              $autoincrement
         * @param bool              $replace
         *
         * @return array
         */
        public static function insert(entity_record_dao $dao, $pool, array $info, $autoincrement, $replace) {
            $insert_fields = [];
            foreach (array_keys($info) as $key) {
                $insert_fields[] = "\"$key\"";
            }

            $insert = core::sql($pool)->prepare("
                INSERT INTO
                    \"" . self::table($dao::TABLE) . "\"
                    ( " . join(', ', $insert_fields) . " )
                    VALUES
                    ( " . join(',', array_fill(0, count($insert_fields), '?')) . " )
                    " . ($autoincrement ? "RETURNING \"". $dao::PRIMARY_KEY . "\"" : '') . "
            ");

            $bindings = $dao->field_bindings();

            // do NOT remove this reference, it will break the bindParam() function
            foreach ($info as $k => &$v) {
                $insert->bindParam($k, $v, self::$binding_conversions[$bindings[$k]]);
            }

            $insert->execute();

            if ($autoincrement) {
                $info[$dao::PRIMARY_KEY] = $insert->fetch()[$dao::PRIMARY_KEY];
            }

            return $info;
        }

        /**
         * Insert multiple records
         *
         * @param entity_record_dao $dao
         * @param string            $pool which source engine pool to use
         * @param array             $infos
         * @param bool              $keys_match
         * @param bool              $autoincrement
         * @param bool              $replace
         *
         * @return array
         */
        public static function inserts(entity_record_dao $dao, $pool, array $infos, $keys_match, $autoincrement, $replace) {

            if ($keys_match) {
                $insert_fields = [];

                foreach (array_keys(reset($infos)) as $k) {
                    $insert_fields[] = "\"{$k}\"";
                }

                // If the table is auto increment, we cannot lump all inserts into one query
                // since we need the returned IDs for cache-busting and to return a model
                if ($autoincrement) {
                    $sql = core::sql($pool);
                    $sql->beginTransaction();
                    $pk = $dao::PRIMARY_KEY;

                    $insert = $sql->prepare("
                        INSERT INTO
                            \"" . self::table($dao::TABLE) . "\"
                            ( " . join(', ', $insert_fields) . " )
                            VALUES
                            ( " . join(',', array_fill(0, count($insert_fields), '?')) . " )
                            RETURNING \"{$pk}\"
                    ");

                    $bindings = $dao->field_bindings();

                    foreach ($infos as $info) {

                        // do NOT remove this reference, it will break the bindParam() function
                        foreach ($info as $k => &$v) {
                            $insert->bindParam($k, $v, self::$binding_conversions[$bindings[$k]]);
                        }

                        $insert->execute();

                        if ($autoincrement) {
                            $info[$dao::PRIMARY_KEY] = $insert->fetch()[$pk];
                        }
                    }

                    $sql->commit();
                } else {
                    // this might explode if $keys_match was a lie
                    $insert_vals = new splFixedArray(count($insert_fields) * count($infos));
                    foreach ($infos as $info) {
                        foreach ($info as $v) {
                            $insert_vals[] = $v;
                        }
                    }

                    $inserts = core::sql($pool)->prepare("
                        INSERT INTO
                            \"" . self::table($dao::TABLE) . "\"
                            ( " . implode(', ', $insert_fields) . " )
                            VALUES
                            " . join(', ', array_fill(0, count($infos), '( ' . join(',', array_fill(0, count($insert_fields), '?')) . ')')) . "
                    ");

                    $bindings = $dao->field_bindings();

                    // do NOT remove this reference, it will break the bindParam() function
                    foreach ($insert_vals as $k => &$v) {
                        $inserts->bindParam($k, $v, self::$binding_conversions[$bindings[$k]]);
                    }

                    $inserts->execute();
                }
            } else {
                $sql   = core::sql($pool);
                $table = self::table($dao::TABLE);

                $sql->beginTransaction();

                $bindings = $dao->field_bindings();

                foreach ($infos as $info) {
                    $insert_fields = [];

                    foreach (array_keys($info) as $key) {
                        $insert_fields[] = "\"$key\"";
                    }

                    $insert = $sql->prepare("
                        INSERT INTO
                            \"$table\"
                            ( " . join(', ', $insert_fields) . " )
                            VALUES
                            ( " . join(',', array_fill(0, count($info), '?')) . " )
                            " . ($autoincrement ? "RETURNING \"". $dao::PRIMARY_KEY . "\"" : '') . "
                    ");

                    // do NOT remove this reference, it will break the bindParam() function
                    foreach ($info as $k => &$v) {
                        $insert->bindParam($k, $v, self::$binding_conversions[$bindings[$k]]);
                    }

                    $insert->execute();

                    if ($autoincrement) {
                        $info[$dao::PRIMARY_KEY] = $insert->fetch()[$dao::PRIMARY_KEY];
                    }
                }

                $sql->commit();
            }

            return $infos;
        }

        /**
         * Update a record
         *
         * @param entity_record_dao   $dao
         * @param string              $pool which source engine pool to use
         * @param int|string          $pk
         * @param entity_record_model $model
         * @param array               $info
         */
        public static function update(entity_record_dao $dao, $pool, $pk, entity_record_model $model, array $info) {
            $sql = core::sql($pool);

            $update_fields = [];
            foreach (array_keys($info) as $key) {
                $update_fields[] = "\"{$key}\" = :{$key}";
            }
            $update = $sql->prepare("
                UPDATE \"" . self::table($dao::TABLE) . "\"
                SET " . implode(", \n", $update_fields) . "
                WHERE \"{$pk}\" = :{$pk}
            ");

            $info[$pk] = $model->$pk;

            $bindings = $dao->field_bindings();

            $i = 1;
            // do NOT remove this reference, it will break the bindParam() function
            foreach ($info as $k => &$v) {
                $update->bindParam($i++, $v, self::$binding_conversions[$bindings[$k]]);
            }

            $update->execute();
        }

        /**
         * Delete a record
         *
         * @param entity_record_dao   $dao
         * @param string              $pool which source engine pool to use
         * @param int|string          $pk
         * @param entity_record_model $model
         */
        public static function delete(entity_record_dao $dao, $pool, $pk, entity_record_model $model) {
            $delete = core::sql($pool)->prepare("
                DELETE FROM \"" . self::table($dao::TABLE) . "\"
                WHERE \"{$pk}\" = ?
            ");
            $delete->bindValue(1, $model->$pk, self::$binding_conversions[$dao->field_binding($dao::PRIMARY_KEY)]);
            $delete->execute();
        }

        /**
         * Delete multiple records
         *
         * @param entity_record_dao        $dao
         * @param string                   $pool which source engine pool to use
         * @param int|string               $pk
         * @param entity_record_collection $collection
         */
        public static function deletes(entity_record_dao $dao, $pool, $pk, entity_record_collection $collection) {
            $pks = $collection->field($pk);
            $delete = core::sql($pool)->prepare("
                DELETE FROM \"" . self::table($dao::TABLE) . "\"
                WHERE \"{$pk}\" IN (" . join(',', array_fill(0, count($collection), '?')) . ")
            ");

            $pdo_binding = self::$binding_conversions[$dao->field_binding($dao::PRIMARY_KEY)];
            $i = 0;
            foreach ($pks as $pk) {
                $delete->bindValue($i++, $pk, $pdo_binding);
            }
            $delete->execute();
        }
    }