<?php

    class entity_record_limit_driver_mysql implements entity_record_limit_driver {

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

        public static function by_pk(entity_record_dao $self, $pool, $pk) {

        }

        public static function by_pks(entity_record_dao $self, $pool, array $pks) {

        }

        public static function by_fields_offset(entity_record_dao $self, $pool, array $keys, $pk, array $order_by, $offset, $limit) {
            $where = [];
            $vals  = [];

            if ($keys) {
                foreach ($keys as $k => $v) {
                    if ($v === null) {
                        $where[] = "`{$k}` IS NULL";
                    } else {
                        $vals[]  = $v;
                        $where[] = "`{$k}` = ?";
                    }
                }
            }

            $limit = $limit ? "{$limit} OFFSET {$offset}" : $offset;

            $order = [];
            foreach ($order_by as $field => $sort_direction) {
                $order[] = "`{$field}` " . (entity_record_limit_dao::SORT_DESC === $sort_direction ? 'DESC' : 'ASC');
            }
            $order_by = join(', ', $order);

            $rs = core::sql($pool)->prepare("
                SELECT `{$pk}`
                FROM `" . self::table($self::TABLE) . "`
                " . ($where ? " WHERE " . join(" AND ", $where) : '') . "
                ORDER BY {$order_by}
                LIMIT {$limit}
            ");
            $rs->execute($vals);

            return array_column($rs->fetchAll(), $pk);
        }

        public static function by_fields_offset_multi(entity_record_dao $self, $pool, array $keys_arr, $pk, array $order_by, $offset, $limit) {
            return [];
        }

        public static function by_fields_after(entity_record_dao $self, $pool, array $keys, $pk, array $order_by, $after_pk, $limit) {
            $where = [];
            $vals  = [];

            if ($keys) {
                foreach ($keys as $k => $v) {
                    if ($v === null) {
                        $where[] = "`{$k}` IS NULL";
                    } else {
                        $vals[]  = $v;
                        $where[] = "`{$k}` = ?";
                    }
                }
            }

            $limit = $limit ? "{$limit} OFFSET {$offset}" : $offset;

            $order = [];
            foreach ($order_by as $field => $order) {
                $order[] = "`{$field}` " . (strtoupper($order) === 'DESC' ? 'DESC' : 'ASC');
            }
            $order_by = join(', ', $order);

            $rs = core::sql($pool)->prepare("
                SELECT `{$pk}`
                FROM `" . self::table($self::TABLE) . "`
                " . ($where ? " WHERE " . join(" AND ", $where) : '') . "
                ORDER BY {$order_by}
                LIMIT {$limit}
            ");
            $rs->execute($vals);

            return array_column($rs->fetchAll(), $pk);
        }

        public static function by_fields_after_multi(entity_record_dao $self, $pool, array $keys_arr, $pk, array $order_by, $after_pk, $limit) {

        }

        public static function by_fields_multi(entity_record_dao $self, $pool, array $keys_arr, $pk, array $order_by, $offset, $limit) {

        }

        public static function by_fields_select(entity_record_dao $self, $pool, array $select_fields, array $keys) {

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
         */
        public static function insert(entity_record_dao $self, $pool, array $info, $autoincrement, $replace) {
            $insert_fields = [];
            foreach (array_keys($info) as $key) {
                $insert_fields[] = "`$key`";
            }

            $sql = core::sql($pool);
            $insert = $sql->prepare("
                " . ($replace ? 'REPLACE' : 'INSERT IGNORE') . " INTO `" . self::table($self::TABLE) . "`
                ( " . join(', ', $insert_fields) . " )
                VALUES
                ( " . join(',', array_fill(0, count($insert_fields), '?')) . " )
            ");

            $insert->execute(array_values($info));

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
         */
        public static function inserts(entity_record_dao $self, $pool, array $infos, $keys_match, $autoincrement, $replace) {
            $sql = core::sql($pool);

            if ($keys_match) {
                $insert_fields = [];
                foreach (array_keys(reset($infos)) as $k) {
                    $insert_fields[] = "`{$k}`";
                }

                /**
                 * If the table is auto increment, we cannot lump all inserts into one query
                 * since we need the returned IDs for cache-busting and to return a model
                 */
                if ($autoincrement) {
                    $sql->beginTransaction();

                    $insert = $sql->prepare("
                        " . ($replace ? 'REPLACE' : 'INSERT IGNORE') . " INTO
                        `" . self::table($self::TABLE) . "`
                        ( " . join(', ', $insert_fields) . " )
                        VALUES
                        ( " . join(',', array_fill(0, count($insert_fields), '?')) . " )
                    ");
                    foreach ($infos as &$info) {
                        $insert->execute(array_values($info));
                        if ($autoincrement) {
                            $info[$self::PRIMARY_KEY] = $sql->lastInsertId();
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

                    $sql->prepare("
                        INSERT INTO
                        `" . self::table($self::TABLE) . "`
                        ( " . join(', ', $insert_fields) . " )
                        VALUES
                        " . join(', ', array_fill(0, count($infos), '( ' . join(',', array_fill(0, count($insert_fields), '?')) . ')')) . "
                    ")->execute($insert_vals);
                }
            } else {
                $sql->beginTransaction();

                foreach ($infos as &$info) {
                    $insert_fields = [];
                    foreach (array_keys($info) as $key) {
                        $insert_fields[] = "`{$key}`";
                    }

                    $sql->prepare("
                        INSERT INTO
                        `" . self::table($self::TABLE) . "`
                        ( " . join(', ', $insert_fields) . " )
                        VALUES
                        ( " . join(',', array_fill(0, count($info), '?')) . " )
                    ")->execute(array_values($info));

                    if ($autoincrement) {
                        $info[$self::PRIMARY_KEY] = $sql->lastInsertId();
                    }
                }

                $sql->commit();
            }

            return $infos;
        }

        public static function update(entity_record_dao $self, $pool, $pk, entity_record_model $model, array $info) {

        }

        public static function delete(entity_record_dao $self, $pool, $pk, entity_record_model $model) {

        }

        public static function deletes(entity_record_dao $self, $pool, $pk, entity_record_collection $collection) {

        }
    }