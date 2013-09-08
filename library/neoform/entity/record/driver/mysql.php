<?php

    namespace neoform;

    use PDO;

    class entity_record_driver_mysql implements entity_record_driver {

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
                return "{$table[0]}`.`{$table[1]}";
            } else {
                return $table;
            }
        }

        /**
         * Get full record by primary key
         *
         * @param entity_record_dao $self the name of the DAO
         * @param string            $pool which source engine pool to use
         * @param int|string|null   $pk
         *
         * @return mixed
         */
        public static function record(entity_record_dao $self, $pool, $pk) {
            $info = core::sql($pool)->prepare("
                SELECT *
                FROM `" . self::table($self::TABLE) . "`
                WHERE `" . $self::PRIMARY_KEY . "` = ?
            ");

            $info->execute([
                $pk,
            ]);

            if ($info = $info->fetch()) {
                return $info;
            }
        }

        /**
         * Get full records by primary key
         *
         * @param entity_record_dao $self the name of the DAO
         * @param string            $pool which source engine pool to use
         * @param array             $pks
         *
         * @return array
         */
        public static function records(entity_record_dao $self, $pool, array $pks) {
            $infos_rs = core::sql($pool)->prepare("
                SELECT *
                FROM `" . self::table($self::TABLE) . "`
                WHERE `" . $self::PRIMARY_KEY . "` IN (" . \join(',', \array_fill(0, \count($pks), '?')) . ")
            ");
            $infos_rs->execute(\array_values($pks));

            $infos = [];
            foreach ($infos_rs->fetchAll() as $info) {
                $k = \array_search($info[$self::PRIMARY_KEY], $pks);
                if ($k !== false) {
                    $infos[$k] = $info;
                }
            }

            return $infos;
        }

        /**
         * Get a count
         *
         * @param entity_record_dao $self
         * @param string            $pool
         * @param array             $fieldvals
         *
         * @return integer
         */
        public static function count(entity_record_dao $self, $pool, array $fieldvals=null) {
            $where = [];
            $vals  = [];

            if ($fieldvals) {
                foreach ($fieldvals as $field => $val) {
                    if ($val === null) {
                        $where[] = "`{$field}` IS NULL";
                    } else {
                        $vals[]  = $val;
                        $where[] = "`{$field}` = ?";
                    }
                }
            }

            $rs = core::sql($pool)->prepare("
                SELECT COUNT(0) `num`
                FROM `" . self::table($self::TABLE) . "`
                " . ($where ? " WHERE " . \join(" AND ", $where) : '') . "
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
            $counts  = [];

            foreach ($fieldvals_arr as $k => $fieldvals) {
                $where      = [];
                $counts[$k] = [];
                $vals[]     = $k;

                foreach ($fieldvals as $field => $val) {
                    if ($val === null) {
                        $where[] = "`{$field}` IS NULL";
                    } else {
                        $vals[]  = $val;
                        $where[] = "`{$field}` = ?";
                    }
                }

                $queries[] = "(
                    SELECT COUNT(0) `num`, ? k
                    FROM `" . self::table($self::TABLE) . "`
                    " . ($where ? " WHERE " . \join(" AND ", $where) : '') . "
                )";
            }

            $rs = core::sql($pool)->prepare(\join(' UNION ALL ', $queries));
            $rs->execute($vals);

            foreach ($rs->fetchAll() as $row) {
                $counts[$row['k']] = (int) $row['num'];
            }

            return $counts;
        }

        /**
         * Get all records in the table
         *
         * @param entity_record_dao $self the name of the DAO
         * @param string            $pool which source engine pool to use
         * @param int|string        $pk
         * @param array             $fieldvals
         *
         * @return array
         */
        public static function all(entity_record_dao $self, $pool, $pk, array $fieldvals=null) {
            $where = [];
            $vals  = [];

            if ($fieldvals) {
                foreach ($fieldvals as $field => $val) {
                    if ($val === null) {
                        $where[] = "`{$field}` IS NULL";
                    } else {
                        $vals[]  = $val;
                        $where[] = "`{$field}` = ?";
                    }
                }
            }

            $info = core::sql($pool)->prepare("
                SELECT *
                FROM `" . self::table($self::TABLE) . "`
                " . ($where ? " WHERE " . \join(" AND ", $where) : '') . "
                ORDER BY `{$pk}` ASC
            ");

            $info->execute($vals);

            return \array_column($info->fetchAll(), null, $pk);
        }

        /**
         * Get record primary key by fields
         *
         * @param entity_record_dao $self the name of the DAO
         * @param string            $pool which source engine pool to use
         * @param array             $fieldvals
         * @param int|string        $pk
         *
         * @return array
         */
        public static function by_fields(entity_record_dao $self, $pool, array $fieldvals, $pk) {
            $where = [];
            $vals  = [];

            if ($fieldvals) {
                foreach ($fieldvals as $field => $val) {
                    if ($val === null) {
                        $where[] = "`{$field}` IS NULL";
                    } else {
                        $vals[]  = $val;
                        $where[] = "`{$field}` = ?";
                    }
                }
            }

            $rs = core::sql($pool)->prepare("
                SELECT `{$pk}`
                FROM `" . self::table($self::TABLE) . "`
                " . ($where ? " WHERE " . \join(" AND ", $where) : '') . "
            ");
            $rs->execute($vals);

            return $rs->fetchAll(PDO::FETCH_COLUMN, 0);
        }

        /**
         * Get multiple record primary keys by fields
         *
         * @param entity_record_dao $self the name of the DAO
         * @param string            $pool which source engine pool to use
         * @param array             $fieldvals_arr
         * @param int|string        $pk
         *
         * @return array
         */
        public static function by_fields_multi(entity_record_dao $self, $pool, array $fieldvals_arr, $pk) {
            $return  = [];
            $vals    = [];
            $queries = [];

            $query = "
                SELECT `{$pk}`, ? `__k__`
                FROM `" . self::table($self::TABLE) . "`
            ";
            foreach ($fieldvals_arr as $k => $fieldvals) {
                $where      = [];
                $return[$k] = [];
                $vals[]     = $k;

                foreach ($fieldvals as $field => $val) {
                    if ($val === null) {
                        $where[] = "`{$field}` IS NULL";
                    } else {
                        $vals[]  = $val;
                        $where[] = "`{$field}` = ?";
                    }
                }

                $queries[] = "(
                    {$query}
                    WHERE " . \join(' AND ', $where) . "
                )";
            }

            $rs = core::sql($pool)->prepare(
                \join(' UNION ALL ', $queries)
            );

            $rs->execute($vals);

            foreach ($rs->fetchAll() as $row) {
                $return[$row['__k__']][] = $row[$pk];
            }

            return $return;
        }

        /**
         * Get a set of PKs based on params, in a given order and offset/limit
         *
         * @param entity_record_dao $self
         * @param string            $pool
         * @param array             $fieldvals
         * @param mixed             $pk
         * @param array             $order_by
         * @param integer|null      $offset
         * @param integer           $limit
         *
         * @return mixed
         */
        public static function by_fields_offset(entity_record_dao $self, $pool, array $fieldvals, $pk, array $order_by, $offset, $limit) {
            $where = [];
            $vals  = [];

            if ($fieldvals) {
                foreach ($fieldvals as $field => $val) {
                    if ($val === null) {
                        $where[] = "`{$field}` IS NULL";
                    } else {
                        $vals[]  = $val;
                        $where[] = "`{$field}` = ?";
                    }
                }
            }

            // LIMIT
            if ($limit) {
                $limit = "LIMIT {$limit}";
            } else if ($offset !== null) {
                $limit = 'LIMIT 18446744073709551610'; // Official mysql docs say to do this... :P
            } else {
                $limit = '';
            }

            // OFFSET
            $offset = $offset !== null ? "OFFSET {$offset}" : '';

            $order = [];
            foreach ($order_by as $field => $sort_direction) {
                $order[] = "`{$field}` " . (entity_dao::SORT_DESC === $sort_direction ? 'DESC' : 'ASC');
            }
            $order_by = \join(', ', $order);

            $rs = core::sql($pool)->prepare("
                SELECT `{$pk}`
                FROM `" . self::table($self::TABLE) . "`
                " . ($where ? " WHERE " . \join(" AND ", $where) : '') . "
                ORDER BY {$order_by}
                {$limit} {$offset}
            ");
            $rs->execute($vals);

            return $rs->fetchAll(PDO::FETCH_COLUMN, 0);
        }

        /**
         * Get multiple sets of PKs based on params, in a given order and offset/limit
         *
         * @param entity_record_dao $self
         * @param string            $pool
         * @param array             $fieldvals_arr
         * @param mixed             $pk
         * @param array             $order_by
         * @param integer|null      $offset
         * @param integer           $limit
         *
         * @return array
         */
        public static function by_fields_offset_multi(entity_record_dao $self, $pool, array $fieldvals_arr, $pk,
                                                      array $order_by, $offset, $limit) {
            $return  = [];
            $vals    = [];
            $queries = [];

            // LIMIT
            if ($limit) {
                $limit = "LIMIT {$limit}";
            } else if ($offset !== null) {
                $limit = 'LIMIT 18446744073709551610'; // Official mysql docs say to do this... :P
            } else {
                $limit = '';
            }

            // OFFSET
            $offset = $offset !== null ? "OFFSET {$offset}" : '';

            // ORDER BY
            $order = [];
            foreach ($order_by as $field => $sort_direction) {
                $order[] = "`{$field}` " . (entity_dao::SORT_DESC === $sort_direction ? 'DESC' : 'ASC');
            }
            $order_by = \join(', ', $order);

            // QUERY
            $query = "
                SELECT `{$pk}`, ? `__k__`
                FROM `" . self::table($self::TABLE) . "`
            ";

            foreach ($fieldvals_arr as $k => $fieldvals) {
                $where      = [];
                $return[$k] = [];
                $vals[]     = $k;
                foreach ($fieldvals as $field => $val) {
                    if ($val === null) {
                        $where[] = "`{$field}` IS NULL";
                    } else {
                        $vals[]  = $val;
                        $where[] = "`{$field}` = ?";
                    }
                }

                $queries[] = "(
                    {$query}
                    WHERE " . \join(" AND ", $where) . "
                    ORDER BY {$order_by}
                    {$limit} {$offset}
                )";
            }

            $rs = core::sql($pool)->prepare(
                \join(' UNION ALL ', $queries)
            );

            $rs->execute($vals);

            foreach ($rs->fetchAll() as $row) {
                $return[$row['__k__']][] = $row[$pk];
            }

            return $return;
        }

        /**
         * Insert record
         *
         * @param entity_record_dao $self the name of the DAO
         * @param string            $pool which source engine pool to use
         * @param array             $info
         * @param bool              $autoincrement
         * @param bool              $replace
         *
         * @return array
         * @throws entity_exception
         */
        public static function insert(entity_record_dao $self, $pool, array $info, $autoincrement, $replace) {
            $insert_fields = [];
            foreach (\array_keys($info) as $key) {
                $insert_fields[] = "`{$key}`";
            }

            $sql = core::sql($pool);
            $insert = $sql->prepare("
                " . ($replace ? 'REPLACE' : 'INSERT') . " INTO `" . self::table($self::TABLE) . "`
                ( " . \join(', ', $insert_fields) . " )
                VALUES
                ( " . \join(',', \array_fill(0, \count($insert_fields), '?')) . " )
            ");

            if (! $insert->execute(\array_values($info))) {
                $error = $sql->errorInfo();
                throw new entity_exception("Insert failed - {$error[0]}: {$error[2]}");
            }

            if ($autoincrement) {
                $info[$self::PRIMARY_KEY] = $sql->lastInsertId();
            }

            return $info;
        }

        /**
         * Insert multiple records
         *
         * @param entity_record_dao $self the name of the DAO
         * @param string            $pool which source engine pool to use
         * @param array             $infos
         * @param bool              $keys_match
         * @param bool              $autoincrement
         * @param bool              $replace
         *
         * @return array
         * @throws entity_exception
         */
        public static function insert_multi(entity_record_dao $self, $pool, array $infos, $keys_match, $autoincrement, $replace) {
            $sql = core::sql($pool);

            if ($keys_match) {
                $insert_fields = [];
                foreach (\array_keys(\reset($infos)) as $k) {
                    $insert_fields[] = "`{$k}`";
                }

                /**
                 * If the table is auto increment, we cannot lump all inserts into one query
                 * since we need the returned IDs for cache-busting and to return a model
                 */
                if ($autoincrement) {

                    $insert = $sql->prepare("
                        " . ($replace ? 'REPLACE' : 'INSERT') . " INTO
                        `" . self::table($self::TABLE) . "`
                        ( " . \join(', ', $insert_fields) . " )
                        VALUES
                        ( " . \join(',', \array_fill(0, \count($insert_fields), '?')) . " )
                    ");
                    foreach ($infos as &$info) {
                        if (! $insert->execute(\array_values($info))) {
                            $error = $sql->errorInfo();
                            $sql->rollBack();
                            throw new entity_exception("Insert multi failed - {$error[0]}: {$error[2]}");
                        }

                        if ($autoincrement) {
                            $info[$self::PRIMARY_KEY] = $sql->lastInsertId();
                        }
                    }

                    if (! $sql->commit()) {
                        $error = $sql->errorInfo();
                        throw new entity_exception("Insert multi failed - {$error[0]}: {$error[2]}");
                    }
                } else {
                    // this might explode if $keys_match was a lie
                    $insert_vals = new \splFixedArray(count($insert_fields) * \count($infos));
                    foreach ($infos as $info) {
                        foreach ($info as $v) {
                            $insert_vals[] = $v;
                        }
                    }

                    if (! $sql->prepare("
                        INSERT INTO
                        `" . self::table($self::TABLE) . "`
                        ( " . \join(', ', $insert_fields) . " )
                        VALUES
                        " . \join(', ', \array_fill(0, \count($infos), '( ' . \join(',', \array_fill(0, \count($insert_fields), '?')) . ')')) . "
                    ")->execute($insert_vals)) {
                        $error = $sql->errorInfo();
                        throw new entity_exception("Insert multi failed - {$error[0]}: {$error[2]}");
                    }
                }
            } else {
                $sql->beginTransaction();

                foreach ($infos as &$info) {
                    $insert_fields = [];
                    foreach (\array_keys($info) as $key) {
                        $insert_fields[] = "`{$key}`";
                    }

                    if (! $sql->prepare("
                        INSERT INTO
                        `" . self::table($self::TABLE) . "`
                        ( " . \join(', ', $insert_fields) . " )
                        VALUES
                        ( " . \join(',', \array_fill(0, \count($info), '?')) . " )
                    ")->execute(array_values($info))) {
                        $error = $sql->errorInfo();
                        $sql->rollBack();
                        throw new entity_exception("Insert multi failed - {$error[0]}: {$error[2]}");
                    }

                    if ($autoincrement) {
                        $info[$self::PRIMARY_KEY] = $sql->lastInsertId();
                    }
                }

                if (! $sql->commit()) {
                    $error = $sql->errorInfo();
                    throw new entity_exception("Insert multi failed - {$error[0]}: {$error[2]}");
                }
            }

            return $infos;
        }

        /**
         * Update a record
         *
         * @param entity_record_dao   $self the name of the DAO
         * @param string              $pool which source engine pool to use
         * @param int|string          $pk
         * @param entity_record_model $model
         * @param array               $info
         *
         * @throws entity_exception
         */
        public static function update(entity_record_dao $self, $pool, $pk, entity_record_model $model, array $info) {
            $update_fields = [];
            foreach (\array_keys($info) as $field) {
                $update_fields[] = "`{$field}` = :{$field}";
            }

            $info[$pk] = $model->$pk;

            if (! core::sql($pool)->prepare("
                UPDATE `" . self::table($self::TABLE) . "`
                SET " . \join(", \n", $update_fields) . "
                WHERE `{$pk}` = :{$pk}
            ")->execute($info)) {
                $error = core::sql($pool)->errorInfo();
                throw new entity_exception("Update failed - {$error[0]}: {$error[2]}");
            }
        }

        /**
         * Delete a record
         *
         * @param entity_record_dao   $self the name of the DAO
         * @param string              $pool which source engine pool to use
         * @param int|string          $pk
         * @param entity_record_model $model
         *
         * @throws entity_exception
         */
        public static function delete(entity_record_dao $self, $pool, $pk, entity_record_model $model) {
            $delete = core::sql($pool)->prepare("
                DELETE FROM `" . self::table($self::TABLE) . "`
                WHERE `{$pk}` = ?
            ");
            if (! $delete->execute([
                $model->$pk,
            ])) {
                $error = core::sql($pool)->errorInfo();
                throw new entity_exception("Delete failed - {$error[0]}: {$error[2]}");
            }
        }

        /**
         * Delete multiple records
         *
         * @param entity_record_dao        $self the name of the DAO
         * @param string                   $pool which source engine pool to use
         * @param int|string               $pk
         * @param entity_record_collection $collection
         *
         * @throws entity_exception
         */
        public static function delete_multi(entity_record_dao $self, $pool, $pk, entity_record_collection $collection) {
            $delete = core::sql($pool)->prepare("
                DELETE FROM `" . self::table($self::TABLE) . "`
                WHERE `{$pk}` IN (" . \join(',', \array_fill(0, \count($collection), '?')) . ")
            ");
            if (! $delete->execute(
                \array_values($collection->field($pk))
            )) {
                $error = core::sql($pool)->errorInfo();
                throw new entity_exception("Delete multi failed - {$error[0]}: {$error[2]}");
            }
        }
    }