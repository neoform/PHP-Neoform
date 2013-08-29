<?php

    class entity_link_driver_mysql implements entity_link_driver {

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
         * Get specific fields from a record, by keys
         *
         * @param entity_link_dao $self the name of the DAO
         * @param string          $pool which source engine pool to use
         * @param array           $select_fields
         * @param array           $keys
         *
         * @return array
         */
        public static function by_fields(entity_link_dao $self, $pool, array $select_fields, array $keys) {

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
                SELECT " . join(',', $select_fields) . "
                FROM `" . self::table($self::TABLE) . "`
                " . (count($where) ? "WHERE " . join(" AND ", $where) : "") . "
            ");

            $rs->execute($vals);

            if (count($select_fields) === 1) {
                return $rs->fetchAll(PDO::FETCH_COLUMN, 0);
            } else {
                return $rs->fetchAll();
            }
        }

        /**
         * Get specific fields from multiple records, by keys
         *
         * @param entity_link_dao $self the name of the DAO
         * @param string          $pool which source engine pool to use
         * @param array           $select_fields
         * @param array           $keys_arr
         *
         * @return array
         */
        public static function by_fields_multi(entity_link_dao $self, $pool, array $select_fields, array $keys_arr) {
            $key_fields     = array_keys(reset($keys_arr));
            $fields         = [];
            $reverse_lookup = [];
            $return         = [];
            $vals           = [];
            $where          = [];

            foreach (array_unique(array_merge($select_fields, $key_fields)) as $k) {
                $fields[] = "`{$k}`";
            }

            foreach ($keys_arr as $k => $keys) {
                $w = [];
                $return[$k] = [];
                $hashed_valued = [];
                foreach ($keys as $key => $v) {
                    if ($v === null) {
                        $w[]             = "`{$key}` IS NULL";
                        $hashed_valued[] = '';
                    } else {
                        $vals[]          = $v;
                        $w[]             = "`{$key}` = ?";
                        $hashed_valued[] = md5($v);
                    }
                }
                $reverse_lookup[join(':', $hashed_valued)] = $k;
                $where[] = '(' . join(" AND ", $w) . ')';
            }

            $rs = core::sql($pool)->prepare("
                SELECT " . join(',', $fields) . "
                FROM `" . self::table($self::TABLE) . "`
                WHERE " . join(' OR ', $where) . "
            ");
            $rs->execute($vals);

            if (count($select_fields) === 1) {
                $field = reset($select_fields);
                foreach ($rs->fetchAll() as $row) {
                    $hashed = [];
                    foreach ($key_fields as $k) {
                        $hashed[$row[$k]] = md5($row[$k]);
                    }
                    $return[$reverse_lookup[join(':', $hashed)]][] = $row[$field];
                }
            } else {
                // If the selected field count is different than the requested fields, only return the requested fields
                if (count($select_fields) !== count($fields)) {
                    $select_fields = array_keys($select_fields);
                    foreach ($rs->fetchAll() as $row) {
                        $hashed = [];
                        foreach ($key_fields as $k) {
                            $hashed[$row[$k]] = md5($row[$k]);
                        }
                        $return[$reverse_lookup[join(':', $hashed)]][] = array_intersect_key($row, $select_fields);
                    }
                } else {
                    foreach ($rs->fetchAll() as $row) {
                        $hashed = [];
                        foreach ($key_fields as $k) {
                            $hashed[$row[$k]] = md5($row[$k]);
                        }
                        $return[$reverse_lookup[join(':', $hashed)]][] = $row;
                    }
                }
            }

            return $return;
        }

        /**
         * Get specific fields from a record, by keys - joined to its related foreign table - and limited
         *
         * @param entity_link_dao   $self the name of the DAO
         * @param string            $pool which source engine pool to use
         * @param string            $local_field
         * @param entity_record_dao $foreign_dao
         * @param array             $keys
         * @param array             $order_by
         * @param integer           $offset
         * @param integer           $limit
         *
         * @return array
         * @throws entity_exception
         */
        public static function by_fields_limit(entity_link_dao $self, $pool, $local_field, entity_record_dao $foreign_dao,
                                               array $keys, array $order_by, $offset, $limit) {

            $quoted_table = self::table($self::TABLE);

            // FK Relation
            $quoted_foreign_table = self::table($foreign_dao::TABLE);
            $foreign_pk           = $foreign_dao::PRIMARY_KEY;

            // WHERE
            $where = [];
            $vals  = [];

            if ($keys) {
                foreach ($keys as $k => $v) {
                    if ($v === null) {
                        $where[] = "`{$quoted_table}`.`{$k}` IS NULL";
                    } else {
                        $vals[]  = $v;
                        $where[] = "`{$quoted_table}`.`{$k}` = ?";
                    }
                }
            }

            // LIMIT/OFFSET
            if ($limit) {
                $limit = "LIMIT {$limit}" . ($offset !== null ? " OFFSET {$offset}" : '');
            } else {
                $limit = '';
            }

            // ORDER BY
            $order = [];
            foreach ($order_by as $field => $sort_direction) {
                $order[] = "`{$quoted_foreign_table}`.`{$field}` " . (entity_dao::SORT_DESC === $sort_direction ? 'DESC' : 'ASC');
            }
            $order_by = join(', ', $order);

            $rs = core::sql($pool)->prepare("
                SELECT `{$quoted_foreign_table}`.`{$foreign_pk}`
                FROM `{$quoted_table}`
                INNER JOIN `{$quoted_foreign_table}`
                ON `{$quoted_foreign_table}`.`{$foreign_pk}` = `{$quoted_table}`.`{$local_field}`
                " . (count($where) ? "WHERE " . join(" AND ", $where) : "") . "
                ORDER BY {$order_by}
                {$limit}
            ");

            $rs->execute($vals);

            return $rs->fetchAll(PDO::FETCH_COLUMN, 0);
        }

        /**
         * Insert a link
         *
         * @param entity_link_dao $self the name of the DAO
         * @param string          $pool which source engine pool to use
         * @param array           $info
         * @param bool            $replace
         *
         * @return mixed
         */
        public static function insert(entity_link_dao $self, $pool, array $info, $replace) {

            $insert_fields = [];
            foreach ($info as $k => $v) {
                $insert_fields[] = "`{$k}`";
            }

            $insert = core::sql($pool)->prepare("
                " . ($replace ? 'REPLACE' : 'INSERT IGNORE') . " INTO
                `" . self::table($self::TABLE) . "`
                ( " . join(', ', $insert_fields) . " )
                VALUES
                ( " . join(',', array_fill(0, count($info), '?')) . " )
            ");

            return $insert->execute(array_values($info));
        }

        /**
         * Insert multiple links
         *
         * @param entity_link_dao $self the name of the DAO
         * @param string          $pool which source engine pool to use
         * @param array           $infos
         * @param bool            $replace
         *
         * @return bool
         */
        public static function inserts(entity_link_dao $self, $pool, array $infos, $replace) {
            $insert_fields = [];
            $info          = current($infos);
            $sql           = core::sql($pool);

            if (count($infos) > 1) {
                $sql->beginTransaction();
            }

            foreach ($info as $k => $v) {
                $insert_fields[] = "`{$k}`";
            }

            $insert = $sql->prepare("
                " . ($replace ? 'REPLACE' : 'INSERT IGNORE') . " INTO `" . self::table($self::TABLE) . "`
                ( " . join(', ', $insert_fields) . " )
                VALUES
                ( " . join(',', array_fill(0, count($info), '?')) . " )
            ");

            foreach ($infos as $info) {
                $insert->execute(array_values($info));
            }

            if (count($infos) > 1) {
                return $sql->commit();
            } else {
                return true;
            }
        }

        /**
         * Update a set of links
         *
         * @param entity_link_dao $self the name of the DAO
         * @param string          $pool which source engine pool to use
         * @param array           $new_info
         * @param array           $where
         *
         * @return mixed
         */
        public static function update(entity_link_dao $self, $pool, array $new_info, array $where) {
            $vals          = [];
            $update_fields = [];

            foreach ($new_info as $k => $v) {
                $update_fields[] = "`{$k}` = ?";
                $vals[]          = $v;
            }

            $where_fields = [];
            foreach ($where as $k => $v) {
                if ($v === null) {
                    $where_fields[] = "`{$k}` IS NULL";
                } else {
                    $vals[] = $v;
                    $where_fields[] = "`{$k}` = ?";
                }
            }

            $update = core::sql($pool)->prepare("
                UPDATE `" . self::table($self::TABLE) . "`
                SET " . join(", \n", $update_fields) . "
                WHERE " . join(" AND \n", $where_fields) . "
            ");

            return $update->execute($vals);
        }

        /**
         * Delete one or more links
         *
         * @param entity_link_dao $self the name of the DAO
         * @param string          $pool which source engine pool to use
         * @param array           $keys
         *
         * @return mixed
         */
        public static function delete(entity_link_dao $self, $pool, array $keys) {
            $where = [];
            $vals  = [];

            foreach ($keys as $k => $v) {
                if ($v === null) {
                    $where[] = "`{$k}` IS NULL";
                } else {
                    $vals[]  = $v;
                    $where[] = "`{$k}` = ?";
                }
            }

            $delete = core::sql($pool)->prepare("
                DELETE FROM `" . self::table($self::TABLE) . "`
                WHERE " . join(" AND ", $where) . "
            ");

            return $delete->execute($vals);
        }

        /**
         * Delete sets of links
         *
         * @param entity_link_dao $self the name of the DAO
         * @param string          $pool which source engine pool to use
         * @param array           $keys_arr
         *
         * @return mixed
         */
        public static function deletes(entity_link_dao $self, $pool, array $keys_arr) {
            $vals  = [];
            $where = [];

            foreach ($keys_arr as $keys) {
                $w = [];
                foreach ($keys as $k => $v) {
                    if ($v === null) {
                        $w[] = "`{$k}` IS NULL";
                    } else {
                        $vals[] = $v;
                        $w[]    = "`{$k}` = ?";
                    }
                }
                $where[] = "(" . join(" AND ", $w) . ")";
            }

            $delete = core::sql($pool)->prepare("
                DELETE FROM `" . self::table($self::TABLE) . "`
                WHERE " . join(" OR ", $where) . "
            ");

            return $delete->execute($vals);
        }
    }