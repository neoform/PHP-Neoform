<?php

    class link_driver_pgsql implements link_driver {

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
         * @param link_dao $self the name of the DAO
         * @param string   $pool which source engine pool to use
         * @param array    $select_fields
         * @param array    $keys
         *
         * @return array
         */
        public static function by_fields(link_dao $self, $pool, array $select_fields, array $keys) {
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
                SELECT " . join(',', $select_fields) . "
                FROM \"" . self::table($self::TABLE) . "\"
                " . (count($where) ? "WHERE " . join(" AND ", $where) : "") . "
            ");

            $rs->execute($vals);

            if (count($select_fields) === 1) {
                return array_column($rs->fetchAll(), reset($select_fields));
            } else {
                return $rs->fetchAll();
            }
        }

        /**
         * Get specific fields from multiple records, by keys
         *
         * @param link_dao $self the name of the DAO
         * @param string   $pool which source engine pool to use
         * @param array    $select_fields
         * @param array    $keys_arr
         *
         * @return array
         */
        public static function by_fields_multi(link_dao $self, $pool, array $select_fields, array $keys_arr) {
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
                        $w[] = "\"{$k}\" IS NULL";
                    } else {
                        $vals[] = $v;
                        $w[]    = "\"{$k}\" = ?";
                    }
                }
                $where[] = '(' . join(" AND ", $w) . ')';
            }

            $rs = core::sql($pool)->prepare("
                SELECT
                    " . join(',', $select_fields) . ",
                    CONCAT(" . join(", ':', ", $key_fields) . ") \"__cache_key__\"
                FROM \"" . self::table($self::TABLE) . "\"
                WHERE " . join(' OR ', $where) . "
            ");

            $rs->execute($vals);

            $rows = $rs->fetchAll();
            if (count($select_fields) === 1) {
                $field = reset($select_fields);
                foreach ($rows as $row) {
                    $return[$reverse_lookup[$row['__cache_key__']]][] = $row[$field];
                }
            } else {
                foreach ($rows as $row) {
                    $return[$reverse_lookup[$row['__cache_key__']]][] = $row;
                }
            }

            return $return;
        }

        /**
         * Insert a link
         *
         * @param link_dao $self the name of the DAO
         * @param string   $pool which source engine pool to use
         * @param array    $info
         * @param bool     $replace
         *
         * @return mixed
         */
        public static function insert(link_dao $self, $pool, array $info, $replace) {

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
         * @param link_dao $self the name of the DAO
         * @param string   $pool which source engine pool to use
         * @param array    $infos
         * @param bool     $replace
         *
         * @return bool
         */
        public static function inserts(link_dao $self, $pool, array $infos, $replace) {
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
         * @param link_dao $self the name of the DAO
         * @param string   $pool which source engine pool to use
         * @param array    $new_info
         * @param array    $where
         *
         * @return mixed
         */
        public static function update(link_dao $self, $pool, array $new_info, array $where) {
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
         * @param link_dao $self the name of the DAO
         * @param string   $pool which source engine pool to use
         * @param array    $keys
         *
         * @return mixed
         */
        public static function delete(link_dao $self, $pool, array $keys) {
            $where = [];
            $vals  = [];

            foreach ($keys as $k => $v) {
                if ($v === null) {
                    $where[] = "\"{$k}\" IS NULL";
                } else {
                    $vals[]  = $v;
                    $where[] = "\"{$k}\" = ?";
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
         * @param link_dao $self the name of the DAO
         * @param string   $pool which source engine pool to use
         * @param array    $keys_arr
         *
         * @return mixed
         */
        public static function deletes(link_dao $self, $pool, array $keys_arr) {
            $vals  = [];
            $where = [];

            foreach ($keys_arr as $keys) {
                $w = [];
                foreach ($keys as $k => $v) {
                    if ($v === null) {
                        $w[] = "\"{$k}\" IS NULL";
                    } else {
                        $vals[] = $v;
                        $w[]    = "\"{$k}\" = ?";
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