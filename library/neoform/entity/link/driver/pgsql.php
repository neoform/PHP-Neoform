<?php

    namespace neoform\entity\link\driver;

    use neoform\entity\link;
    use neoform\entity\record;
    use neoform\entity\exception;
    use neoform\entity;
    use neoform\sql;
    use PDO;

    class pgsql implements link\driver {

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
         * @param link\dao $self the name of the DAO
         * @param string   $pool which source engine pool to use
         * @param array    $select_fields
         * @param array    $fieldvals
         *
         * @return array
         */
        public static function by_fields(link\dao $self, $pool, array $select_fields, array $fieldvals) {
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

            $rs = sql::instance($pool)->prepare("
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
         * @param link\dao $self the name of the DAO
         * @param string          $pool which source engine pool to use
         * @param array           $select_fields
         * @param array           $fieldvals_arr
         *
         * @return array
         */
        public static function by_fields_multi(link\dao $self, $pool, array $select_fields, array $fieldvals_arr) {
            $fields  = [];
            $return  = [];
            $vals    = [];
            $queries = [];

            foreach ($select_fields as $field) {
                $fields[] = "\"{$field}\"";
            }

            $query = "
                SELECT " . join(',', $fields) . ", ? \"__k__\"
                FROM \"" . self::table($self::TABLE) . "\"
            ";

            foreach ($fieldvals_arr as $k => $fieldvals) {
                $where      = [];
                $return[$k] = [];
                $vals       = $k;

                foreach ($fieldvals as $field => $val) {
                    if ($val === null) {
                        $where[] = "\"{$field}\" IS NULL";
                    } else {
                        $vals[]  = $val;
                        $where[] = "\"{$field}\" = ?";
                    }
                }
                $queries[] = "(
                    {$query}
                    WHERE " . join(' OR ', $where) . "
                )";
            }

            $rs = sql::instance($pool)->prepare(
                join(' UNION ALL ', $queries)
            );
            $rs->execute($vals);

            if (count($select_fields) === 1) {
                $field = reset($select_fields);
                foreach ($rs->fetchAll() as $row) {
                    $return[$row['__k__']][] = $row[$field];
                }
            } else {
                foreach ($rs->fetchAll() as $row) {
                    $return[$row['__k__']][] = array_intersect_key($row, array_keys($select_fields));
                }
            }

            return $return;
        }

        /**
         * Get specific fields from a record, by keys - joined to its related foreign table - and limited
         *
         * @param link\dao   $self the name of the DAO
         * @param string     $pool which source engine pool to use
         * @param string     $local_field
         * @param record\dao $foreign_dao
         * @param array      $fieldvals
         * @param array      $order_by
         * @param integer    $limit
         * @param integer    $offset
         *
         * @return array
         * @throws exception
         */
        public static function by_fields_limit(link\dao $self, $pool, $local_field, record\dao $foreign_dao,
                                               array $fieldvals, array $order_by, $offset, $limit) {

            $quoted_table = self::table($self::TABLE);

            // FK Relation
            $quoted_foreign_table = self::table($foreign_dao::TABLE);
            $foreign_pk           = $foreign_dao::PRIMARY_KEY;

            // WHERE
            $where = [];
            $vals  = [];

            if ($fieldvals) {
                foreach ($fieldvals as $field => $val) {
                    if ($val === null) {
                        $where[] = "\"{$quoted_table}\".\"{$field}\" IS NULL";
                    } else {
                        $vals[]  = $val;
                        $where[] = "\"{$quoted_table}\".\"{$field}\" = ?";
                    }
                }
            }

            // LIMIT
            $limit = $limit ? "LIMIT {$limit}" : '';

            // OFFSET
            $offset = $offset !== null ? "OFFSET {$offset}" : '';

            // ORDER BY
            $order = [];
            foreach ($order_by as $field => $sort_direction) {
                $order[] = "\"{$quoted_foreign_table}\".\"{$field}\" " . (entity\dao::SORT_DESC === $sort_direction ? 'DESC' : 'ASC');
            }
            $order_by = join(', ', $order);

            $rs = sql::instance($pool)->prepare("
                SELECT \"{$quoted_foreign_table}\".\"{$foreign_pk}\"
                FROM \"{$quoted_table}\"
                INNER JOIN \"{$quoted_foreign_table}\"
                ON \"{$quoted_foreign_table}\".\"{$foreign_pk}\" = \"{$quoted_table}\".\"{$local_field}\"
                " . (count($where) ? "WHERE " . join(" AND ", $where) : "") . "
                ORDER BY {$order_by}
                {$limit} {$offset}
            ");

            $rs->execute($vals);

            return $rs->fetchAll(PDO::FETCH_COLUMN, 0);
        }

        /**
         * Get specific fields from a record, by keys - joined to its related foreign table - and limited
         *
         * @param link\dao   $self the name of the DAO
         * @param string     $pool which source engine pool to use
         * @param string     $local_field
         * @param record\dao $foreign_dao
         * @param array      $fieldvals_arr
         * @param array      $order_by
         * @param integer    $limit
         * @param integer    $offset
         *
         * @return array
         * @throws exception
         */
        public static function by_fields_limit_multi(link\dao $self, $pool, $local_field, record\dao $foreign_dao,
                                                     array $fieldvals_arr, array $order_by, $offset, $limit) {
            $return  = [];
            $vals    = [];
            $queries = [];

            // LOCAL
            $quoted_table = self::table($self::TABLE);

            // FK Relation
            $quoted_foreign_table = self::table($foreign_dao::TABLE);
            $foreign_pk           = $foreign_dao::PRIMARY_KEY;

            // LIMIT
            $limit = $limit ? "LIMIT {$limit}" : '';

            // OFFSET
            $offset = $offset !== null ? "OFFSET {$offset}" : '';

            // ORDER BY
            $order = [];
            foreach ($order_by as $field => $sort_direction) {
                $order[] = "\"{$quoted_foreign_table}\".\"{$field}\" " . (entity\dao::SORT_DESC === $sort_direction ? 'DESC' : 'ASC');
            }
            $order_by = join(', ', $order);

            // QUERIES
            $query = "
                SELECT \"{$local_field}\", ? \"__k__\"
                FROM \"" . self::table($self::TABLE) . "\"
                INNER JOIN \"{$quoted_foreign_table}\"
                ON \"{$quoted_foreign_table}\".\"{$foreign_pk}\" = \"{$quoted_table}\".\"{$local_field}\"
            ";
            foreach ($fieldvals_arr as $k => $fieldvals) {
                $where      = [];
                $return[$k] = [];
                $vals[]     = $k;

                foreach ($fieldvals as $field => $val) {
                    if ($val === null) {
                        $where[] = "\"{$quoted_table}\".\"{$field}\" IS NULL";
                    } else {
                        $vals[]  = $val;
                        $where[] = "\"{$quoted_table}\".\"{$field}\" = ?";
                    }
                }

                $queries[] = "(
                    {$query}
                    WHERE" . join(" AND ", $where) . "
                    ORDER BY {$order_by}
                    {$limit} {$offset}
                )";
            }

            $rs = sql::instance($pool)->prepare(
                join(' UNION ALL ', $queries)
            );
            $rs->execute($vals);

            foreach ($rs->fetchAll() as $row) {
                $return[$row['__k__']][] = $row[$local_field];
            }

            return $return;
        }

        /**
         * Get a count based on key inputs
         *
         * @param link\dao $dao
         * @param string   $pool
         * @param array    $fieldvals
         *
         * @return integer
         */
        public static function count(link\dao $dao, $pool, array $fieldvals=null) {
            $where = [];
            $vals  = [];

            if ($fieldvals) {
                foreach ($fieldvals as $k => $v) {
                    if ($v === null) {
                        $where[] = "\"{$k}\" IS NULL";
                    } else {
                        $vals[]  = $v;
                        $where[] = "\"{$k}\" = ?";
                    }
                }
            }

            $rs = sql::instance($pool)->prepare("
                SELECT COUNT(*) \"num\"
                FROM \"" . self::table($dao::TABLE) . "\"
                " . ($where ? " WHERE " . join(" AND ", $where) : '') . "
            ");
            $rs->execute($vals);
            return (int) $rs->fetch()['num'];
        }

        /**
         * Get multiple counts
         *
         * @param link\dao $self
         * @param string   $pool
         * @param array    $fieldvals_arr
         *
         * @return array
         */
        public static function count_multi(link\dao $self, $pool, array $fieldvals_arr) {
            $queries = [];
            $vals    = [];
            $counts  = [];

            foreach ($fieldvals_arr as $k => $fieldvals) {
                $where      = [];
                $counts[$k] = [];
                $vals[]     = $k;

                foreach ($fieldvals as $field => $val) {
                    if ($val === null) {
                        $where[] = "\"{$field}\" IS NULL";
                    } else {
                        $vals[]  = $val;
                        $where[] = "\"{$field}\" = ?";
                    }
                }

                $queries[] = "(
                    SELECT COUNT(*) \"num\", ? k
                    FROM \"" . self::table($self::TABLE) . "\"
                    " . ($where ? " WHERE " . join(" AND ", $where) : '') . "
                )";
            }

            $rs = sql::instance($pool)->prepare(join(' UNION ALL ', $queries));
            $rs->execute($vals);

            foreach ($rs->fetchAll() as $row) {
                $counts[$row['k']] = (int) $row['num'];
            }

            return $counts;
        }

        /**
         * Insert a link
         *
         * @param link\dao $self the name of the DAO
         * @param string   $pool which source engine pool to use
         * @param array    $info
         * @param bool     $replace
         *
         * @return array
         * @throws exception
         */
        public static function insert(link\dao $self, $pool, array $info, $replace) {

            if ($replace) {
                throw new exception('PostgreSQL does not support REPLACE INTO.');
            }

            $insert_fields = [];
            foreach ($info as $k => $v) {
                $insert_fields[] = "\"{$k}\"";
            }

            $insert = sql::instance($pool)->prepare("
                INSERT INTO
                \"" . self::table($self::TABLE) . "\"
                ( " . join(', ', $insert_fields) . " )
                VALUES
                ( " . join(',', \array_fill(0, count($info), '?')) . " )
            ");

            if (! $insert->execute(array_values($info))) {
                $error = sql::instance($pool)->errorInfo();
                throw new exception("Insert failed - {$error[0]}: {$error[2]}");
            }

            return $info;
        }

        /**
         * Insert multiple links
         *
         * @param link\dao $self the name of the DAO
         * @param string   $pool which source engine pool to use
         * @param array    $infos
         * @param bool     $replace
         *
         * @return array
         * @throws exception
         */
        public static function insert_multi(link\dao $self, $pool, array $infos, $replace) {

            if ($replace) {
                throw new exception('PostgreSQL does not support REPLACE INTO.');
            }

            $insert_fields = [];
            $info          = current($infos);
            $sql           = sql::instance($pool);
            $multi         = count($infos) > 1;

            if ($multi) {
                $sql->beginTransaction();
            }

            foreach ($info as $k => $v) {
                $insert_fields[] = "\"{$k}\"";
            }

            $insert = $sql->prepare("
                INSERT INTO \"" . self::table($self::TABLE) . "\"
                ( " . join(', ', $insert_fields) . " )
                VALUES
                ( " . join(',', \array_fill(0, count($info), '?')) . " )
            ");

            foreach ($infos as $info) {
                if (! $insert->execute(array_values($info))) {
                    $error = $sql->errorInfo();
                    $sql->rollback();
                    throw new exception("Insert multi failed - {$error[0]}: {$error[2]}");
                }
            }

            if ($multi && ! $sql->commit()) {
                $error = $sql->errorInfo();
                throw new exception("Insert multi failed - {$error[0]}: {$error[2]}");
            }

            return $infos;
        }

        /**
         * Update a set of links
         *
         * @param link\dao $self the name of the DAO
         * @param string   $pool which source engine pool to use
         * @param array    $new_info
         * @param array    $where
         *
         * @throws exception
         */
        public static function update(link\dao $self, $pool, array $new_info, array $where) {
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
                    $vals[]         = $v;
                    $where_fields[] = "\"{$k}\" = ?";
                }
            }

            if (! sql::instance($pool)->prepare("
                UPDATE \"" . self::table($self::TABLE) . "\"
                SET " . join(", \n", $update_fields) . "
                WHERE " . join(" AND \n", $where_fields) . "
            ")->execute($vals)) {
                $error = sql::instance($pool)->errorInfo();
                throw new exception("Update failed - {$error[0]}: {$error[2]}");
            }
        }

        /**
         * Delete one or more links
         *
         * @param link\dao $self the name of the DAO
         * @param string          $pool which source engine pool to use
         * @param array           $fieldvals
         *
         * @throws exception
         */
        public static function delete(link\dao $self, $pool, array $fieldvals) {
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

            if (! sql::instance($pool)->prepare("
                DELETE FROM \"" . self::table($self::TABLE) . "\"
                WHERE " . join(" AND ", $where) . "
            ")->execute($vals)) {
                $error = sql::instance($pool)->errorInfo();
                throw new exception("Delete failed - {$error[0]}: {$error[2]}");
            }
        }

        /**
         * Delete sets of links
         *
         * @param link\dao $self the name of the DAO
         * @param string          $pool which source engine pool to use
         * @param array           $fieldvals_arr
         *
         * @throws exception
         */
        public static function delete_multi(link\dao $self, $pool, array $fieldvals_arr) {
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

            if (! sql::instance($pool)->prepare("
                DELETE FROM \"" . self::table($self::TABLE) . "\"
                WHERE " . join(" OR ", $where) . "
            ")->execute($vals)) {
                $error = sql::instance($pool)->errorInfo();
                throw new exception("Delete multi failed - {$error[0]}: {$error[2]}");
            }
        }
    }