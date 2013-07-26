<?php

    /**
     * Postgres record_dao driver
     */
    class record_driver_pgsql implements record_driver {

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
         * @param string          $self
         * @param int|string|null $pk
         *
         * @return mixed
         */
        public static function by_pk($self, $pk) {

            $info = core::sql($self::SOURCE_ENGINE_READ ?: core::config()->sql['default_read'])->prepare("
                SELECT *
                FROM \"" . self::table($self::TABLE) . "\"
                WHERE \"" . $self::PRIMARY_KEY . "\" = ?
            ");

            $info->bindValue(1, $pk, sql_pdo::pdo_binding($self::bindings(), $self::PRIMARY_KEY));
            $info->execute();

            if ($info = $info->fetch()) {
                sql_pdo::unbinary($info);
                return $info;
            }
        }

        /**
         * Get full records by primary key
         *
         * @param string $self the name of the DAO
         * @param array  $pks
         *
         * @return array
         */
        public static function by_pks($self, array $pks) {

            $infos_rs = core::sql($self::SOURCE_ENGINE_READ ?: core::config()->sql['default_read'])->prepare("
                SELECT *
                FROM \"" . self::table($self::TABLE) . "\"
                WHERE \"" . $self::PRIMARY_KEY . "\" IN (" . join(',', array_fill(0, count($pks), '?')) . ")
            ");

            $pdo_binding = sql_pdo::pdo_binding($self::bindings(), $self::PRIMARY_KEY);
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
         * Get a list of PKs, with a limit, offset and order by
         *
         * @param string  $self
         * @param integer $limit     max number of PKs to return
         * @param string  $order_by  field name
         * @param string  $direction ASC|DESC
         * @param string  $after_pk  A PK offset to be used (it's more efficient to use PK offsets than an SQL 'OFFSET')
         *
         * @return array
         */
        public static function limit($self, $limit, $order_by, $direction, $after_pk) {
            $pk = $self::PRIMARY_KEY;

            $rs = core::sql($self::SOURCE_ENGINE_READ ?: core::config()->sql['default_read'])->prepare("
                SELECT \"{$pk}\"
                FROM \"" . self::table($self::TABLE) . "\"
                " . ($after_pk !== null ? "WHERE \"{$pk}\" " . ($direction === 'ASC' ? '>' : '<') . ' ?' : '') . "
                ORDER BY \"{$order_by}\" {$direction}
                LIMIT {$limit}
            ");
            if ($after_pk !== null) {
                $rs->execute($after_pk);
            } else {
                $rs->execute();
            }

            $pks = array_column($rs->fetchAll(), $pk);
            sql_pdo::unbinary($pks);

            return $pks;
        }

        /**
         * Get full count of rows in a table
         *
         * @param string $self
         *
         * @return int
         */
        public static function count($self) {
            $rs = core::sql($self::SOURCE_ENGINE_READ ?: core::config()->sql['default_read'])->prepare("
                SELECT COUNT(0) \"num\"
                FROM \"" . self::table($self::TABLE) . "\"
            ");
            $rs->execute();
            $count = $rs->fetch();

            return (int) $count['num'];
        }

        /**
         * Get all records in the table
         *
         * @param string     $self the name of the DAO
         * @param int|string $pk
         * @param array      $keys
         *
         * @return array
         */
        public static function all($self, $pk, array $keys=null) {
            $where = [];
            $vals  = [];

            if ($keys) {
                foreach ($keys as $k => $v) {
                    if (is_array($v) && count($v)) {
                        foreach ($v as $arr_v) {
                            $vals[] = $arr_v;
                        }
                        $where[] = "\"$k\" IN(" . join(',', array_fill(0, count($v), '?')) . ")";
                    } else {
                        if ($v === null) {
                            $where[] = "\"$k\" IS NULL";
                        } else {
                            $vals[$k] = $v;
                            $where[]  = "\"$k\" = ?";
                        }
                    }
                }
            }

            $info = core::sql($self::SOURCE_ENGINE_READ ?: core::config()->sql['default_read'])->prepare("
                SELECT *
                FROM \"" . self::table($self::TABLE) . "\"
                " . (count($where) ? " WHERE " . join(" AND ", $where) : "") . "
                ORDER BY \"{$pk}\" ASC
            ");

            sql_pdo::bind_by_casting(
                $info,
                $self::bindings(),
                $vals
            );

            $info->execute();

            $infos = array_column($info->fetchAll(), null, $pk);
            sql_pdo::unbinary($infos);

            return $infos;
        }

        /**
         * Get record primary key by fields
         *
         * @param string     $self the name of the DAO
         * @param array      $keys
         * @param int|string $pk
         *
         * @return array
         */
        public static function by_fields($self, array $keys, $pk) {
            $where = [];
            $vals  = [];

            if (count($keys)) {
                foreach ($keys as $k => $v) {
                    if ($v === null) {
                        $where[] = "\"{$k}\" IS NULL";
                    } else {
                        $vals[$k] = $v;
                        $where[]  = "\"{$k}\" = ?";
                    }
                }
            }

            $rs = core::sql($self::SOURCE_ENGINE_READ ?: core::config()->sql['default_read'])->prepare("
                SELECT \"{$pk}\"
                FROM \"" . self::table($self::TABLE) . "\"
                " . (count($where) ? " WHERE " . join(" AND ", $where) : "") . "
            ");

            sql_pdo::bind_by_casting(
                $rs,
                $self::bindings(),
                $vals
            );

            $rs->execute();

            $rs = $rs->fetchAll();

            $pks = array_column($rs->fetchAll(), $pk);
            sql_pdo::unbinary($pks);

            return $pks;
        }

        /**
         * Get multiple record primary keys by fields
         *
         * @param string     $self the name of the DAO
         * @param array      $keys_arr
         * @param int|string $pk
         *
         * @return array
         */
        public static function by_fields_multi($self, array $keys_arr, $pk) {
            $key_fields     = array_keys(reset($keys_arr));
            $reverse_lookup = [];
            $return         = [];
            $vals           = [];
            $where          = [];

            foreach ($keys_arr as $k => $keys) {
                $w = [];
                $reverse_lookup[join(':', $keys)] = $k;
                $return[$k] = [];
                foreach ($keys as $k => $v) {
                    if ($v === null) {
                        $w[] = "\"$k\" IS NULL";
                    } else {
                        $vals[$k] = $v;
                        $w[]      = "\"$k\" = ?";
                    }
                }
                $where[] = '(' . join(" AND ", $w) . ')';
            }

            $rs = core::sql($self::SOURCE_ENGINE_READ ?: core::config()->sql['default_read'])->prepare("
                SELECT
                    \"{$pk}\",
                    CONCAT(" . join(", ':', ", $key_fields) . ") \"__cache_key__\"
                FROM \"" . self::table($self::TABLE) . "\"
                WHERE " . join(' OR ', $where) . "
            ");

            sql_pdo::bind_by_casting(
                $rs,
                $self::bindings(),
                $vals
            );

            $rs->execute();

            $rows = $rs->fetchAll();
            foreach ($rows as $row) {
                $return[$reverse_lookup[$row['__cache_key__']]][] = $row[$pk];
            }

            sql_pdo::unbinary($return);

            return $return;
        }

        /**
         * Get specific fields from a record, by keys
         *
         * @param string $self the name of the DAO
         * @param array  $select_fields
         * @param array  $keys
         *
         * @return array
         */
        public static function by_fields_select($self, array $select_fields, array $keys) {
            $where = [];
            $vals  = [];

            if (count($keys)) {
                foreach ($keys as $k => $v) {
                    if ($v === null) {
                        $where[] = "\"{$k}\" IS NULL";
                    } else {
                        $vals[$k] = $v;
                        $where[]  = "\"{$k}\" = ?";
                    }
                }
            }

            $rs = core::sql($self::SOURCE_ENGINE_READ ?: core::config()->sql['default_read'])->prepare("
                SELECT " . join(',', $select_fields) . "
                FROM \"" . self::table($self::TABLE) . "\"
                " . (count($where) ? "WHERE " . join(" AND ", $where) : "") . "
            ");

            sql_pdo::bind_by_casting(
                $rs,
                $self::bindings(),
                $vals
            );

            $rs->execute();

            if (count($select_fields) === 1) {
                $return = array_column($rs->fetchAll(), reset($select_fields));
            } else {
                $return = $rs->fetchAll();
            }

            sql_pdo::unbinary($return);

            return $return;
        }

        /**
         * Insert record
         *
         * @param string $self the name of the DAO
         * @param array  $info
         * @param bool   $autoincrement
         * @param boo    $replace
         *
         * @return array
         */
        public static function insert($self, array $info, $autoincrement, $replace) {
            $insert_fields = [];
            foreach (array_keys($info) as $key) {
                $insert_fields[] = "\"$key\"";
            }

            $insert = core::sql($self::SOURCE_ENGINE_WRITE ?: core::config()->sql['default_write'])->prepare("
                INSERT INTO
                    \"" . self::table($self::TABLE) . "\"
                    ( " . join(', ', $insert_fields) . " )
                    VALUES
                    ( " . join(',', array_fill(0, count($insert_fields), '?')) . " )
                    " . ($autoincrement ? "RETURNING \"". $self::PRIMARY_KEY . "\"" : '') . "
            ");

            sql_pdo::bind_by_casting(
                $insert,
                $self::bindings(),
                $info
            );

            $insert->execute();

            if ($autoincrement) {
                $info[$self::PRIMARY_KEY] = $insert->fetch()[$self::PRIMARY_KEY];
            }

            return $info;
        }

        /**
         * Insert multiple records
         *
         * @param string $self the name of the DAO
         * @param array $infos
         * @param bool  $keys_match
         * @param bool  $autoincrement
         * @param bool  $replace
         *
         * @return array
         */
        public static function inserts($self, array $infos, $keys_match, $autoincrement, $replace) {

            if ($keys_match) {
                $insert_fields = [];

                foreach (array_keys(reset($infos)) as $k) {
                    $insert_fields[] = "\"{$k}\"";
                }

                // If the table is auto increment, we cannot lump all inserts into one query
                // since we need the returned IDs for cache-busting and to return a model
                if ($autoincrement) {
                    $sql = core::sql($self::SOURCE_ENGINE_WRITE ?: core::config()->sql['default_write']);
                    $sql->beginTransaction();
                    $pk = $self::PRIMARY_KEY;

                    $insert = $sql->prepare("
                        INSERT INTO
                            \"" . self::table($self::TABLE) . "\"
                            ( " . join(', ', $insert_fields) . " )
                            VALUES
                            ( " . join(',', array_fill(0, count($insert_fields), '?')) . " )
                            RETURNING \"$pk\"
                    ");

                    foreach ($infos as $info) {

                        sql_pdo::bind_by_casting(
                            $insert,
                            $self::bindings(),
                            $info
                        );

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

                    $inserts = core::sql($self::SOURCE_ENGINE_WRITE ?: core::config()->sql['default_write'])->prepare("
                        INSERT INTO
                            \"" . self::table($self::TABLE) . "\"
                            ( " . implode(', ', $insert_fields) . " )
                            VALUES
                            " . join(', ', array_fill(0, count($infos), '( ' . join(',', array_fill(0, count($insert_fields), '?')) . ')')) . "
                    ");

                    sql_pdo::bind_by_casting(
                        $inserts,
                        $self::bindings(),
                        $insert_vals
                    );

                    $inserts->execute();
                }
            } else {
                $sql   = core::sql($self::SOURCE_ENGINE_WRITE ?: core::config()->sql['default_write']);
                $table = self::table($self::TABLE);

                $sql->beginTransaction();

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

                    sql_pdo::bind_by_casting(
                        $insert,
                        $self::bindings(),
                        $info
                    );

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
         * @param string       $self the name of the DAO
         * @param int|string   $pk
         * @param record_model $model
         * @param array        $info
         */
        public static function update($self, $pk, record_model $model, array $info) {
            $sql = core::sql($self::SOURCE_ENGINE_WRITE ?: core::config()->sql['default_write']);

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

            sql_pdo::bind_by_casting(
                $update,
                $self::bindings(),
                $info,
                true
            );

            $update->execute();
        }

        /**
         * Delete a record
         *
         * @param string       $self the name of the DAO
         * @param int|string   $pk
         * @param record_model $model
         */
        public static function delete($self, $pk, record_model $model) {
            $delete = core::sql($self::SOURCE_ENGINE_WRITE ?: core::config()->sql['default_write'])->prepare("
                DELETE FROM \"" . self::table($self::TABLE) . "\"
                WHERE \"{$pk}\" = ?
            ");
            $delete->bindValue(1, $model->$pk, sql_pdo::pdo_binding($self::bindings(), $self::PRIMARY_KEY));
            $delete->execute();
        }

        /**
         * Delete multiple records
         *
         * @param string            $self the name of the DAO
         * @param int|string        $pk
         * @param record_collection $collection
         */
        public static function deletes($self, $pk, record_collection $collection) {
            $pks = $collection->field($pk);
            $delete = core::sql($self::SOURCE_ENGINE_WRITE ?: core::config()->sql['default_write'])->prepare("
                DELETE FROM \"" . self::table($self::TABLE) . "\"
                WHERE \"{$pk}\" IN (" . join(',', array_fill(0, count($collection), '?')) . ")
            ");

            $pdo_binding = sql_pdo::pdo_binding($self::bindings(), $self::PRIMARY_KEY);
            $i = 0;
            foreach ($pks as $pk) {
                $delete->bindValue($i++, $pk, $pdo_binding);
            }
            $delete->execute();
        }
    }