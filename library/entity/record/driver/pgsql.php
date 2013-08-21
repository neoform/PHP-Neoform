<?php

    /**
     * Postgres entity_record_dao driver
     */
    class entity_record_driver_pgsql implements entity_record_driver {

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
         * @param entity_record_dao      $self
         * @param string          $pool which source engine pool to use
         * @param int|string|null $pk
         *
         * @return mixed
         */
        public static function by_pk(entity_record_dao $self, $pool, $pk) {

            $info = core::sql($pool)->prepare("
                SELECT *
                FROM \"" . self::table($self::TABLE) . "\"
                WHERE \"" . $self::PRIMARY_KEY . "\" = ?
            ");

            $info->bindValue(1, $pk, $self->pdo_binding($self::PRIMARY_KEY));
            $info->execute();

            if ($info = $info->fetch()) {
                sql_pdo::unbinary($info);
                return $info;
            }
        }

        /**
         * Get full records by primary key
         *
         * @param entity_record_dao $self the name of the DAO
         * @param string     $pool which source engine pool to use
         * @param array      $pks
         *
         * @return array
         */
        public static function by_pks(entity_record_dao $self, $pool, array $pks) {

            $infos_rs = core::sql($pool)->prepare("
                SELECT *
                FROM \"" . self::table($self::TABLE) . "\"
                WHERE \"" . $self::PRIMARY_KEY . "\" IN (" . join(',', array_fill(0, count($pks), '?')) . ")
            ");

            $pdo_binding = $self->pdo_binding($self::PRIMARY_KEY);
            foreach (array_values($pks) as $i => $pk) {
                $infos_rs->bindValue($i + 1, $pk, $pdo_binding);
            }
            $infos_rs->execute();

            $infos = [];
            foreach ($infos_rs->fetchAll() as $info) {
                $k = array_search($info[$self::PRIMARY_KEY], $pks);
                if ($k !== false) {
                    $infos[$k] = $info;
                }
            }

            sql_pdo::unbinary($infos);

            return $infos;
        }

        /**
         * Get a count
         *
         * @param entity_record_dao $self
         * @param string            $pool
         * @param array             $keys
         *
         * @return integer
         */
        public static function count(entity_record_dao $self, $pool, array $keys=null) {
            $where = [];
            $vals  = [];

            if ($keys) {
                foreach ($keys as $k => $v) {
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
                FROM \"" . self::table($self::TABLE) . "\"
                " . ($where ? " WHERE " . join(" AND ", $where) : '') . "
            ");
            $rs->execute($vals);
            return (int) $rs->fetch()['num'];
        }

        /**
         * Get all records in the table
         *
         * @param entity_record_dao $self the name of the DAO
         * @param string     $pool which source engine pool to use
         * @param int|string $pk
         * @param array      $keys
         *
         * @return array
         */
        public static function all(entity_record_dao $self, $pool, $pk, array $keys=null) {
            $where = [];
            $vals  = [];

            if ($keys) {
                foreach ($keys as $k => $v) {
                    if (is_array($v) && $v) {
                        foreach ($v as $arr_v) {
                            $vals[] = $arr_v;
                        }
                        $where[] = "\"{$k}\" IN(" . join(',', array_fill(0, count($v), '?')) . ")";
                    } else {
                        if ($v === null) {
                            $where[] = "\"{$k}\" IS NULL";
                        } else {
                            $vals[$k] = $v;
                            $where[]  = "\"{$k}\" = ?";
                        }
                    }
                }
            }

            $info = core::sql($pool)->prepare("
                SELECT *
                FROM \"" . self::table($self::TABLE) . "\"
                " . ($where ? " WHERE " . join(" AND ", $where) : "") . "
                ORDER BY \"{$pk}\" ASC
            ");

            $bindings = $self->pdo_bindings();

            // do NOT remove this reference, it will break the bindParam() function
            foreach ($vals as $k => &$v) {
                $info->bindParam($k, $v, $bindings[$k]);
            }

            $info->execute();

            $infos = array_column($info->fetchAll(), null, $pk);
            sql_pdo::unbinary($infos);

            return $infos;
        }

        /**
         * Get record primary key by fields
         *
         * @param entity_record_dao $self the name of the DAO
         * @param string     $pool which source engine pool to use
         * @param array      $keys
         * @param int|string $pk
         *
         * @return array
         */
        public static function by_fields(entity_record_dao $self, $pool, array $keys, $pk) {
            $where = [];
            $vals  = [];

            if ($keys) {
                foreach ($keys as $k => $v) {
                    if ($v === null) {
                        $where[] = "\"{$k}\" IS NULL";
                    } else {
                        $vals[$k] = $v;
                        $where[]  = "\"{$k}\" = ?";
                    }
                }
            }

            $rs = core::sql($pool)->prepare("
                SELECT \"{$pk}\"
                FROM \"" . self::table($self::TABLE) . "\"
                " . ($where ? " WHERE " . join(" AND ", $where) : "") . "
            ");

            $bindings = $self->pdo_bindings();

            // do NOT remove this reference, it will break the bindParam() function
            foreach ($vals as $k => &$v) {
                $rs->bindParam($k, $v, $bindings[$k]);
            }

            $rs->execute();

            $rs = $rs->fetchAll();

            $pks = array_column($rs->fetchAll(), $pk);
            sql_pdo::unbinary($pks);

            return $pks;
        }

        /**
         * Get multiple record primary keys by fields
         *
         * @param entity_record_dao $self the name of the DAO
         * @param string     $pool which source engine pool to use
         * @param array      $keys_arr
         * @param int|string $pk
         *
         * @return array
         */
        public static function by_fields_multi(entity_record_dao $self, $pool, array $keys_arr, $pk) {
            $key_fields     = [];
            $reverse_lookup = [];
            $return         = [];
            $vals           = [];
            $where          = [];

            foreach (array_keys(reset($keys_arr)) as $k) {
                $key_fields[] = "`{$k}`";
            }

            foreach ($keys_arr as $k => $keys) {
                $w = [];
                // @todo this is potentially buggy if the field contains a colon
                $reverse_lookup[join(':', $keys)] = $k;
                $return[$k] = [];
                foreach ($keys as $k => $v) {
                    if ($v === null) {
                        $w[] = "\"{$k}\" IS NULL";
                    } else {
                        $vals[$k] = $v;
                        $w[]      = "\"{$k}\" = ?";
                    }
                }
                $where[] = '(' . join(" AND ", $w) . ')';
            }

            $rs = core::sql($pool)->prepare("
                SELECT
                    \"{$pk}\",
                    CONCAT(" . join(", ':', ", $key_fields) . ") \"__cache_key__\"
                FROM \"" . self::table($self::TABLE) . "\"
                WHERE " . join(' OR ', $where) . "
            ");

            $bindings = $self->pdo_bindings();

            // do NOT remove this reference, it will break the bindParam() function
            foreach ($vals as $k => &$v) {
                $rs->bindParam($k, $v, $bindings[$k]);
            }

            $rs->execute();

            $rows = $rs->fetchAll();
            foreach ($rows as $row) {
                $return[$reverse_lookup[$row['__cache_key__']]][] = $row[$pk];
            }

            sql_pdo::unbinary($return);

            return $return;
        }

        /**
         * Get a set of PKs based on params, in a given order and offset/limit
         *
         * @param entity_record_dao $self
         * @param string            $pool
         * @param array             $keys
         * @param mixed             $pk
         * @param array             $order_by
         * @param integer|null      $offset
         * @param integer           $limit
         *
         * @return mixed
         */
        public static function by_fields_offset(entity_record_dao $self, $pool, array $keys, $pk, array $order_by, $offset, $limit) {
            $where = [];
            $vals  = [];

            if ($keys) {
                foreach ($keys as $k => $v) {
                    if ($v === null) {
                        $where[] = "\"{$k}\" IS NULL";
                    } else {
                        $vals[]  = $v;
                        $where[] = "\"{$k}\" = ?";
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
                $order[] = "\"{$field}\" " . (entity_record_dao::SORT_DESC === $sort_direction ? 'DESC' : 'ASC');
            }
            $order_by = join(', ', $order);

            $rs = core::sql($pool)->prepare("
                SELECT \"{$pk}\"
                FROM \"" . self::table($self::TABLE) . "\"
                " . ($where ? " WHERE " . join(" AND ", $where) : '') . "
                ORDER BY {$order_by}
                {$limit}
            ");
            $rs->execute($vals);

            return array_column($rs->fetchAll(), $pk);
        }

        /**
         * Get multiple sets of PKs based on params, in a given order and offset/limit
         *
         * @param entity_record_dao $self
         * @param string            $pool
         * @param array             $keys_arr
         * @param mixed             $pk
         * @param array             $order_by
         * @param integer|null      $offset
         * @param integer           $limit
         *
         * @return array
         */
        public static function by_fields_offset_multi(entity_record_dao $self, $pool, array $keys_arr, $pk, array $order_by, $offset, $limit) {
            $select_fields  = [ "\"{$pk}\"" ];
            $reverse_lookup = [];
            $return         = [];
            $vals           = [];
            $queries        = [];
            $fields         = array_keys(reset($keys_arr));

            foreach ($fields as $k) {
                $select_fields[] = "`{$k}`";
            }

            if ($limit) {
                $limit = "LIMIT {$limit}" . ($offset !== null ? " OFFSET {$offset}" : '');
            } else {
                $limit = '';
            }

            $order = [];
            foreach ($order_by as $field => $sort_direction) {
                $order[] = "\"{$field}\" " . (entity_record_dao::SORT_DESC === $sort_direction ? 'DESC' : 'ASC');
            }
            $order_by = join(', ', $order);

            foreach ($keys_arr as $k => $keys) {
                $where         = [];
                $return[$k]    = [];
                $hashed_valued = [];
                foreach ($keys as $key => $val) {
                    if ($val === null) {
                        $where[]         = "\"{$key}\" IS NULL";
                        $hashed_valued[] = '';
                    } else {
                        $vals[]  = $val;
                        $where[] = "\"{$key}\" = ?";
                        $hashed_valued[] = md5($val);
                    }
                }
                $reverse_lookup[join(':', $hashed_valued)] = $k;

                $queries[] = "(
                    SELECT " . join(", ", $select_fields) . "
                    FROM \"" . self::table($self::TABLE) . "\"
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
         * @param entity_record_dao $self the name of the DAO
         * @param string     $pool which source engine pool to use
         * @param array      $info
         * @param bool       $autoincrement
         * @param bool       $replace
         *
         * @return array
         */
        public static function insert(entity_record_dao $self, $pool, array $info, $autoincrement, $replace) {
            $insert_fields = [];
            foreach (array_keys($info) as $key) {
                $insert_fields[] = "\"$key\"";
            }

            $insert = core::sql($pool)->prepare("
                INSERT INTO
                    \"" . self::table($self::TABLE) . "\"
                    ( " . join(', ', $insert_fields) . " )
                    VALUES
                    ( " . join(',', array_fill(0, count($insert_fields), '?')) . " )
                    " . ($autoincrement ? "RETURNING \"". $self::PRIMARY_KEY . "\"" : '') . "
            ");

            $bindings = $self->pdo_bindings();

            // do NOT remove this reference, it will break the bindParam() function
            foreach ($info as $k => &$v) {
                $insert->bindParam($k, $v, $bindings[$k]);
            }

            $insert->execute();

            if ($autoincrement) {
                $info[$self::PRIMARY_KEY] = $insert->fetch()[$self::PRIMARY_KEY];
            }

            return $info;
        }

        /**
         * Insert multiple records
         *
         * @param entity_record_dao $self the name of the DAO
         * @param string     $pool which source engine pool to use
         * @param array      $infos
         * @param bool       $keys_match
         * @param bool       $autoincrement
         * @param bool       $replace
         *
         * @return array
         */
        public static function inserts(entity_record_dao $self, $pool, array $infos, $keys_match, $autoincrement, $replace) {

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
                    $pk = $self::PRIMARY_KEY;

                    $insert = $sql->prepare("
                        INSERT INTO
                            \"" . self::table($self::TABLE) . "\"
                            ( " . join(', ', $insert_fields) . " )
                            VALUES
                            ( " . join(',', array_fill(0, count($insert_fields), '?')) . " )
                            RETURNING \"{$pk}\"
                    ");

                    $bindings = $self->pdo_bindings();

                    foreach ($infos as $info) {

                        // do NOT remove this reference, it will break the bindParam() function
                        foreach ($info as $k => &$v) {
                            $insert->bindParam($k, $v, $bindings[$k]);
                        }

                        $insert->execute();

                        if ($autoincrement) {
                            $info[$self::PRIMARY_KEY] = $insert->fetch()[$pk];
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
                            \"" . self::table($self::TABLE) . "\"
                            ( " . implode(', ', $insert_fields) . " )
                            VALUES
                            " . join(', ', array_fill(0, count($infos), '( ' . join(',', array_fill(0, count($insert_fields), '?')) . ')')) . "
                    ");

                    $bindings = $self->pdo_bindings();

                    // do NOT remove this reference, it will break the bindParam() function
                    foreach ($insert_vals as $k => &$v) {
                        $inserts->bindParam($k, $v, $bindings[$k]);
                    }

                    $inserts->execute();
                }
            } else {
                $sql   = core::sql($pool);
                $table = self::table($self::TABLE);

                $sql->beginTransaction();

                $bindings = $self->pdo_bindings();

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
                            " . ($autoincrement ? "RETURNING \"". $self::PRIMARY_KEY . "\"" : '') . "
                    ");

                    // do NOT remove this reference, it will break the bindParam() function
                    foreach ($info as $k => &$v) {
                        $insert->bindParam($k, $v, $bindings[$k]);
                    }

                    $insert->execute();

                    if ($autoincrement) {
                        $info[$self::PRIMARY_KEY] = $insert->fetch()[$self::PRIMARY_KEY];
                    }
                }

                $sql->commit();
            }

            return $infos;
        }

        /**
         * Update a record
         *
         * @param entity_record_dao   $self the name of the DAO
         * @param string       $pool which source engine pool to use
         * @param int|string   $pk
         * @param entity_record_model $model
         * @param array        $info
         */
        public static function update(entity_record_dao $self, $pool, $pk, entity_record_model $model, array $info) {
            $sql = core::sql($pool);

            $update_fields = [];
            foreach (array_keys($info) as $key) {
                $update_fields[] = "\"{$key}\" = :{$key}";
            }
            $update = $sql->prepare("
                UPDATE \"" . self::table($self::TABLE) . "\"
                SET " . implode(", \n", $update_fields) . "
                WHERE \"{$pk}\" = :{$pk}
            ");

            $info[$pk] = $model->$pk;

            $bindings = $self->pdo_bindings();

            $i = 1;
            // do NOT remove this reference, it will break the bindParam() function
            foreach ($info as $k => &$v) {
                $update->bindParam($i++, $v, $bindings[$k]);
            }

            $update->execute();
        }

        /**
         * Delete a record
         *
         * @param entity_record_dao   $self the name of the DAO
         * @param string       $pool which source engine pool to use
         * @param int|string   $pk
         * @param entity_record_model $model
         */
        public static function delete(entity_record_dao $self, $pool, $pk, entity_record_model $model) {
            $delete = core::sql($pool)->prepare("
                DELETE FROM \"" . self::table($self::TABLE) . "\"
                WHERE \"{$pk}\" = ?
            ");
            $delete->bindValue(1, $model->$pk, $self->pdo_binding($self::PRIMARY_KEY));
            $delete->execute();
        }

        /**
         * Delete multiple records
         *
         * @param entity_record_dao        $self the name of the DAO
         * @param string            $pool which source engine pool to use
         * @param int|string        $pk
         * @param entity_record_collection $collection
         */
        public static function deletes(entity_record_dao $self, $pool, $pk, entity_record_collection $collection) {
            $pks = $collection->field($pk);
            $delete = core::sql($pool)->prepare("
                DELETE FROM \"" . self::table($self::TABLE) . "\"
                WHERE \"{$pk}\" IN (" . join(',', array_fill(0, count($collection), '?')) . ")
            ");

            $pdo_binding = $self->pdo_binding($self::PRIMARY_KEY);
            $i = 0;
            foreach ($pks as $pk) {
                $delete->bindValue($i++, $pk, $pdo_binding);
            }
            $delete->execute();
        }
    }