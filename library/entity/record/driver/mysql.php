<?php

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
        public static function by_pk(entity_record_dao $self, $pool, $pk) {
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
        public static function by_pks(entity_record_dao $self, $pool, array $pks) {
            $infos_rs = core::sql($pool)->prepare("
                SELECT *
                FROM `" . self::table($self::TABLE) . "`
                WHERE `" . $self::PRIMARY_KEY . "` IN (" . join(',', array_fill(0, count($pks), '?')) . ")
            ");
            $infos_rs->execute(array_values($pks));

            $infos = [];
            foreach ($infos_rs->fetchAll() as $info) {
                $k = array_search($info[$self::PRIMARY_KEY], $pks);
                if ($k !== false) {
                    $infos[$k] = $info;
                }
            }

            return $infos;
        }

        /**
         * Get full count of rows in a table
         *
         * @param entity_record_dao $self
         * @param string            $pool which source engine pool to use
         *
         * @return int
         */
        public static function count(entity_record_dao $self, $pool) {
            $rs = core::sql($pool)->prepare("
                SELECT COUNT(0) `num`
                FROM `" . self::table($self::TABLE) . "`
            ");
            $rs->execute();
            return (int) $rs->fetch()['num'];
        }

        /**
         * Get all records in the table
         *
         * @param entity_record_dao $self the name of the DAO
         * @param string            $pool which source engine pool to use
         * @param int|string        $pk
         * @param array             $keys
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
                        $where[] = "`{$k}` IN(" . join(',', array_fill(0, count($v), '?')) . ")";
                    } else {
                        if ($v === null) {
                            $where[] = "`{$k}` IS NULL";
                        } else {
                            $vals[]  = $v;
                            $where[] = "`{$k}` = ?";
                        }
                    }
                }
            }

            $info = core::sql($pool)->prepare("
                SELECT *
                FROM `" . self::table($self::TABLE) . "`
                " . ($where ? " WHERE " . join(" AND ", $where) : '') . "
                ORDER BY `{$pk}` ASC
            ");

            $info->execute($vals);

            return array_column($info->fetchAll(), null, $pk);
        }

        /**
         * Get record primary key by fields
         *
         * @param entity_record_dao $self the name of the DAO
         * @param string            $pool which source engine pool to use
         * @param array             $keys
         * @param int|string        $pk
         *
         * @return array
         */
        public static function by_fields(entity_record_dao $self, $pool, array $keys, $pk) {
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

            $rs = core::sql($pool)->prepare("
                SELECT `{$pk}`
                FROM `" . self::table($self::TABLE) . "`
                " . ($where ? " WHERE " . join(" AND ", $where) : '') . "
            ");
            $rs->execute($vals);

            return array_column($rs->fetchAll(), $pk);
        }

        /**
         * Get multiple record primary keys by fields
         *
         * @param entity_record_dao $self the name of the DAO
         * @param string            $pool which source engine pool to use
         * @param array             $keys_arr
         * @param int|string        $pk
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
                        $w[] = "`{$k}` IS NULL";
                    } else {
                        $vals[] = $v;
                        $w[]    = "`{$k}` = ?";
                    }
                }
                $where[] = '(' . join(" AND ", $w) . ')';
            }

            $rs = core::sql($pool)->prepare("
                SELECT
                    `{$pk}`,
                    CONCAT(" . join(", ':', ", $key_fields) . ") `__cache_key__`
                FROM `" . self::table($self::TABLE) . "`
                WHERE " . join(' OR ', $where) . "
            ");

            $rs->execute($vals);

            foreach ($rs->fetchAll() as $row) {
                $return[$reverse_lookup[$row['__cache_key__']]][] = $row[$pk];
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

        /**
         * Update a record
         *
         * @param entity_record_dao   $self the name of the DAO
         * @param string              $pool which source engine pool to use
         * @param int|string          $pk
         * @param entity_record_model $model
         * @param array               $info
         */
        public static function update(entity_record_dao $self, $pool, $pk, entity_record_model $model, array $info) {
            $update_fields = [];
            foreach (array_keys($info) as $key) {
                $update_fields[] = "`{$key}` = :{$key}";
            }

            $info[$pk] = $model->$pk;

            core::sql($pool)->prepare("
                UPDATE `" . self::table($self::TABLE) . "`
                SET " . join(", \n", $update_fields) . "
                WHERE `{$pk}` = :{$pk}
            ")->execute($info);
        }

        /**
         * Delete a record
         *
         * @param entity_record_dao   $self the name of the DAO
         * @param string              $pool which source engine pool to use
         * @param int|string          $pk
         * @param entity_record_model $model
         */
        public static function delete(entity_record_dao $self, $pool, $pk, entity_record_model $model) {
            $delete = core::sql($pool)->prepare("
                DELETE FROM `" . self::table($self::TABLE) . "`
                WHERE `{$pk}` = ?
            ");
            $delete->execute([
                $model->$pk,
            ]);
        }

        /**
         * Delete multiple records
         *
         * @param entity_record_dao        $self the name of the DAO
         * @param string                   $pool which source engine pool to use
         * @param int|string               $pk
         * @param entity_record_collection $collection
         */
        public static function deletes(entity_record_dao $self, $pool, $pk, entity_record_collection $collection) {
            $delete = core::sql($pool)->prepare("
                DELETE FROM `" . self::table($self::TABLE) . "`
                WHERE `{$pk}` IN (" . join(',', array_fill(0, count($collection), '?')) . ")
            ");
            $delete->execute(
                array_values($collection->field($pk))
            );
        }
    }