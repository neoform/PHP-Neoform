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
                return "$table[0]\".\"$table[1]";
            } else {
                return $table;
            }
        }

        public static function by_fields($self, array $select_fields, array $keys) {
            $where = [];
            $vals  = [];

            if (count($keys)) {
                foreach ($keys as $k => $v) {
                    if ($v === null) {
                        $where[] = "\"$k\" IS NULL";
                    } else {
                        $vals[]  = $v;
                        $where[] = "\"$k\" = ?";
                    }
                }
            }

            $rs = core::sql('slave')->prepare("
                SELECT " . join(',', $select_fields) . "
                FROM \"" . self::table($self::TABLE) . "\"
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

        public static function by_fields_multi($self, array $select_fields, array $keys_arr) {
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
                        $w[] = "\"$k\" IS NULL";
                    } else {
                        $vals[] = $v;
                        $w[]    = "\"$k\" = ?";
                    }
                }
                $where[] = '(' . join(" AND ", $w) . ')';
            }

            $rs = core::sql('slave')->prepare("
                SELECT
                    " . join(',', $select_fields) . ",
                    CONCAT(" . join(", ':', ", $key_fields) . ") \"__cache_key__\"
                FROM \"" . self::table($self::TABLE) . "\"
                WHERE " . join(' OR ', $where) . "
            ");

            $rs->execute($vals);

            $rows = $rs->fetchAll();
            if (count($select_fields) === 1) {
                $field = current($select_fields);
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

        public static function insert($self, array $info, $replace) {

            $insert_fields = [];
            foreach ($info as $k => $v) {
                $insert_fields[] = "\"$k\"";
            }

            $insert = core::sql('master')->prepare("
                INSERT INTO
                \"" . self::table($self::TABLE) . "\"
                ( " . join(', ', $insert_fields) . " )
                VALUES
                ( " . join(',', array_fill(0, count($info), '?')) . " )
            ");

            return $insert->execute(array_values($info));
        }

        public static function inserts($self, array $infos, $replace) {
            $insert_fields = [];
            $info          = current($infos);
            $sql           = core::sql('master');

            if (count($infos) > 1) {
                $sql->beginTransaction();
            }

            foreach ($info as $k => $v) {
                $insert_fields[] = "\"$k\"";
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

        public static function update($self, array $new_info, array $where) {
            $vals          = [];
            $update_fields = [];

            foreach ($new_info as $key => $val) {
                $update_fields[] = "\"$key\" = ?";
                $vals[] = $val;
            }

            $where_fields = [];
            foreach ($where as $k => $v) {
                if ($v === null) {
                    $where_fields[] = "\"$k\" IS NULL";
                } else {
                    $vals[] = $v;
                    $where_fields[] = "\"$k\" = ?";
                }
            }

            $update = core::sql('master')->prepare("
                UPDATE \"" . self::table($self::TABLE) . "\"
                SET " . join(", \n", $update_fields) . "
                WHERE " . join(" AND \n", $where_fields) . "
            ");

            return $update->execute($vals);
        }

        public static function delete($self, array $keys) {
            $where = [];
            $vals  = [];

            foreach ($keys as $k => $v) {
                if ($v === null) {
                    $where[] = "\"$k\" IS NULL";
                } else {
                    $vals[]  = $v;
                    $where[] = "\"$k\" = ?";
                }
            }

            $delete = core::sql('master')->prepare("
                DELETE FROM \"" . self::table($self::TABLE) . "\"
                WHERE " . join(" AND ", $where) . "
            ");

            return $delete->execute($vals);
        }

        public static function deletes($self, array $keys_arr) {
            $vals  = [];
            $where = [];

            foreach ($keys_arr as $keys) {
                $w = [];
                foreach ($keys as $k => $v) {
                    if ($v === null) {
                        $w[] = "\"$k\" IS NULL";
                    } else {
                        $vals[] = $v;
                        $w[]    = "\"$k\" = ?";
                    }
                }
                $where[] = "(" . join(" AND ", $w) . ")";
            }

            $delete = core::sql('master')->prepare("
                DELETE FROM \"" . self::table($self::TABLE) . "\"
                WHERE " . join(" OR ", $where) . "
            ");

            return $delete->execute($vals);
        }
    }