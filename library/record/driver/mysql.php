<?php

    class record_driver_mysql implements record_driver {

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
                return "$table[0]`.`$table[1]";
            } else {
                return $table;
            }
        }

        /**
         * Get full record by primary key
         *
         * @param string     $self the name of the DAO
         * @param int|string $pk
         *
         * @return mixed
         * @throws record_exception
         */
        public static function by_pk($self, $pk) {
            $info = core::sql('slave')->prepare("
                SELECT *
                FROM `" . self::table($self::TABLE) . "`
                WHERE `" . $self::PRIMARY_KEY . "` = ?
            ");

            $info->execute([
                $pk,
            ]);

            if (! ($info = $info->fetch())) {
                $exception = $self::ENTITY_NAME . '_exception';
                throw new $exception('That ' . $self::NAME . ' doesn\'t exist');
            }

            return $info;
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
            $infos_rs = core::sql('slave')->prepare("
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
                        $where[] = "`$k` IN(" . join(',', array_fill(0, count($v), '?')) . ")";
                    } else {
                        if ($v === null) {
                            $where[] = "`$k` IS NULL";
                        } else {
                            $vals[]  = $v;
                            $where[] = "`$k` = ?";
                        }
                    }
                }
            }

            $info = core::sql('slave')->prepare("
                SELECT *
                FROM `" . self::table($self::TABLE) . "`
                " . (count($where) ? " WHERE " . join(" AND ", $where) : "") . "
                ORDER BY `$pk` ASC
            ");

            $info->execute($vals);

            $infos = [];
            foreach ($info->fetchAll() as $info) {
                $infos[$info[$pk]] = $info;
            }

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
                        $where[] = "`$k` IS NULL";
                    } else {
                        $vals[]  = $v;
                        $where[] = "`$k` = ?";
                    }
                }
            }

            $rs = core::sql('slave')->prepare("
                SELECT `$pk`
                FROM `" . self::table($self::TABLE) . "`
                " . (count($where) ? " WHERE " . join(" AND ", $where) : "") . "
            ");
            $rs->execute($vals);

            $rs = $rs->fetchAll();
            $pks = [];
            foreach ($rs as $row) {
                $pks[] = $row[$pk];
            }

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
            $sql            = core::sql('slave');
            $key_fields     = array_keys(current($keys_arr));
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
                        $w[] = "`$k` IS NULL";
                    } else {
                        $vals[] = $v;
                        $w[]    = "`$k` = ?";
                    }
                }
                $where[] = '(' . join(" AND ", $w) . ')';
            }

            $rs = $sql->prepare("
                SELECT
                    `$pk`,
                    CONCAT(" . join(", ':', ", $key_fields) . ") `__cache_key__`
                FROM `" . self::table($self::TABLE) . "`
                WHERE " . join(' OR ', $where) . "
            ");

            $rs->execute($vals);

            $rows = $rs->fetchAll();
            foreach ($rows as $row) {
                $return[
                $reverse_lookup[$row['__cache_key__']]
                ][] = $row[$pk];
            }

            return $return;
        }

        /**
         * Get specific fields from a record, by keys
         *
         * @param string $self
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
                        $where[] = "`$k` IS NULL";
                    } else {
                        $vals[]  = $v;
                        $where[] = "`$k` = ?";
                    }
                }
            }

            $rs = core::sql('slave')->prepare("
                SELECT " . join(',', $select_fields) . "
                FROM `" . self::table($self::TABLE) . "`
                " . (count($where) ? "WHERE " . join(" AND ", $where) : "") . "
            ");

            $rs->execute($vals);

            $rs = $rs->fetchAll();
            $return = [];
            if (count($select_fields) === 1) {
                $field = current($select_fields);
                foreach ($rs as $row) {
                    $return[] = $row[$field];
                }
            } else {
                $return = $rs;
            }

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
                $insert_fields[] = "`$key`";
            }

            $sql = core::sql('slave');
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
         * @param string $self the name of the DAO
         * @param array $infos
         * @param bool  $keys_match
         * @param bool  $autoincrement
         * @param bool  $replace
         *
         * @return array
         */
        public static function inserts($self, array $infos, $keys_match, $autoincrement, $replace) {
            $sql = core::sql('master');

            if ($keys_match) {
                $insert_fields = [];

                foreach (array_keys(current($infos)) as $k) {
                    $insert_fields[] = "`$k`";
                }

                // If the table is auto increment, we cannot lump all inserts into one query
                // since we need the returned IDs for cache-busting and to return a model
                if ($autoincrement) {
                    $sql->beginTransaction();

                    $insert = $sql->prepare("
                        " . ($replace ? 'REPLACE' : 'INSERT IGNORE') . " INTO
                            `" . self::table($self::TABLE) . "`
                            ( " . join(', ', $insert_fields) . " )
                            VALUES
                            ( " . join(',', array_fill(0, count($insert_fields), '?')) . " )
                    ");
                    foreach ($infos as $info) {
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

                    $inserts = $sql->prepare("
                        INSERT INTO
                            `" . self::table($self::TABLE) . "`
                            ( " . implode(', ', $insert_fields) . " )
                            VALUES
                            " . join(', ', array_fill(0, count($infos), '( ' . join(',', array_fill(0, count($insert_fields), '?')) . ')')) . "
                    ");

                    $inserts->execute($insert_vals);
                }
            } else {
                $sql->beginTransaction();

                foreach ($infos as $info) {
                    $insert_fields = [];

                    foreach (array_keys($info) as $key) {
                        $insert_fields[] = "`$key`";
                    }

                    $insert = $sql->prepare("
                        INSERT INTO
                            `" . self::table($self::TABLE) . "`
                            ( " . join(', ', $insert_fields) . " )
                            VALUES
                            ( " . join(',', array_fill(0, count($info), '?')) . " )
                    ");
                    $insert->execute(array_values($info));

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
         * @param string       $self the name of the DAO
         * @param int|string   $pk
         * @param record_model $model
         * @param array        $info
         */
        public static function update($self, $pk, record_model $model, array $info) {
            $update_fields = [];
            foreach (array_keys($info) as $key) {
                $update_fields[] = "`$key` = :$key";
            }
            $update = core::sql('master')->prepare("
                UPDATE `" . self::table($self::TABLE) . "`
                SET " . implode(", \n", $update_fields) . "
                WHERE `$pk` = :$pk
            ");

            $info[$pk] = $model->$pk;
            $update->execute($info);
        }

        /**
         * Delete a record
         *
         * @param string       $self the name of the DAO
         * @param int|string   $pk
         * @param record_model $model
         */
        public static function delete($self, $pk, record_model $model) {
            $delete = core::sql('master')->prepare("
                DELETE FROM `" . self::table($self::TABLE) . "`
                WHERE `$pk` = ?
            ");
            $delete->execute([
                $model->$pk,
            ]);
        }

        /**
         * Delete multiple records
         *
         * @param string            $self the name of the DAO
         * @param int|string        $pk
         * @param record_collection $collection
         */
        public static function deletes($self, $pk, record_collection $collection) {
            $delete = core::sql('master')->prepare("
                DELETE FROM `" . self::table($self::TABLE) . "`
                WHERE `$pk` IN (" . join(',', array_fill(0, count($collection), '?')) . ")
            ");
            $delete->execute(
                array_values($collection->field($pk))
            );
        }
    }