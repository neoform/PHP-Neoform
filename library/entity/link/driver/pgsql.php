<?php

    class entity_link_driver_pgsql implements entity_link_driver {

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
         * Get specific fields from a record, by keys
         *
         * @param entity_link_dao $self the name of the DAO
         * @param string          $pool which source engine pool to use
         * @param array           $select_fields
         * @param array           $fieldvals
         *
         * @return array
         */
        public static function by_fields(entity_link_dao $self, $pool, array $select_fields, array $fieldvals) {
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

            $rs = core::sql($pool)->prepare("
                SELECT " . join(',', $select_fields) . "
                FROM \"" . self::table($self::TABLE) . "\"
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
         * @param array           $fieldvals_arr
         *
         * @return array
         */
        public static function by_fields_multi(entity_link_dao $self, $pool, array $select_fields, array $fieldvals_arr) {
            $key_fields     = array_keys(reset($fieldvals_arr));
            $fields         = [];
            $reverse_lookup = [];
            $return         = [];
            $vals           = [];
            $where          = [];

            foreach (array_unique(array_merge($select_fields, $key_fields)) as $k) {
                $fields[] = "\"{$k}\"";
            }

            foreach ($fieldvals_arr as $k => $fieldvals) {
                $w = [];
                $return[$k] = [];
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
                SELECT " . join(',', $fields) . "
                FROM \"" . self::table($self::TABLE) . "\"
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
                $insert_fields[] = "\"{$k}\"";
            }

            $insert = core::sql($pool)->prepare("
                INSERT INTO
                \"" . self::table($self::TABLE) . "\"
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
                $insert_fields[] = "\"{$k}\"";
            }

            $insert = $sql->prepare("
                INSERT INTO \"" . self::table($self::TABLE) . "\"
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
                $update_fields[] = "\"{$k}\" = ?";
                $vals[]          = $v;
            }

            $where_fields = [];
            foreach ($where as $k => $v) {
                if ($v === null) {
                    $where_fields[] = "\"{$k}\" IS NULL";
                } else {
                    $vals[] = $v;
                    $where_fields[] = "\"{$k}\" = ?";
                }
            }

            $update = core::sql($pool)->prepare("
                UPDATE \"" . self::table($self::TABLE) . "\"
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
         * @param array           $fieldvals
         *
         * @return mixed
         */
        public static function delete(entity_link_dao $self, $pool, array $fieldvals) {
            $where = [];
            $vals  = [];

            foreach ($fieldvals as $field => $v) {
                if ($v === null) {
                    $where[] = "\"{$field}\" IS NULL";
                } else {
                    $vals[]  = $v;
                    $where[] = "\"{$field}\" = ?";
                }
            }

            $delete = core::sql($pool)->prepare("
                DELETE FROM \"" . self::table($self::TABLE) . "\"
                WHERE " . join(" AND ", $where) . "
            ");

            return $delete->execute($vals);
        }

        /**
         * Delete sets of links
         *
         * @param entity_link_dao $self the name of the DAO
         * @param string          $pool which source engine pool to use
         * @param array           $fieldvals_arr
         *
         * @return mixed
         */
        public static function deletes(entity_link_dao $self, $pool, array $fieldvals_arr) {
            $vals  = [];
            $where = [];

            foreach ($fieldvals_arr as $fieldvals) {
                $w = [];
                foreach ($fieldvals as $field => $v) {
                    if ($v === null) {
                        $w[] = "\"{$field}\" IS NULL";
                    } else {
                        $vals[] = $v;
                        $w[]    = "\"{$field}\" = ?";
                    }
                }
                $where[] = "(" . join(" AND ", $w) . ")";
            }

            $delete = core::sql($pool)->prepare("
                DELETE FROM \"" . self::table($self::TABLE) . "\"
                WHERE " . join(" OR ", $where) . "
            ");

            return $delete->execute($vals);
        }
    }