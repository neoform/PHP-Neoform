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
         * Get specific fields from a record, by keys - joined to its related foreign table - and limited
         *
         * @param entity_link_dao   $self the name of the DAO
         * @param string            $pool which source engine pool to use
         * @param string            $local_field
         * @param entity_record_dao $foreign_dao
         * @param array             $fieldvals
         * @param array             $order_by
         * @param integer           $limit
         * @param integer           $offset
         *
         * @return array
         * @throws entity_exception
         */
        public static function by_fields_limit(entity_link_dao $self, $pool, $local_field, entity_record_dao $foreign_dao,
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
            if ($limit) {
                $limit = "LIMIT {$limit}";
            } else {
                $limit = '';
            }

            // OFFSET
            if ($offset !== null) {
                $offset = "OFFSET {$offset}";
            } else {
                $offset = '';
            }

            // ORDER BY
            $order = [];
            foreach ($order_by as $field => $sort_direction) {
                $order[] = "\"{$quoted_foreign_table}\".\"{$field}\" " . (entity_dao::SORT_DESC === $sort_direction ? 'DESC' : 'ASC');
            }
            $order_by = join(', ', $order);

            $rs = core::sql($pool)->prepare("
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
         * @param entity_link_dao   $self the name of the DAO
         * @param string            $pool which source engine pool to use
         * @param string            $local_field
         * @param entity_record_dao $foreign_dao
         * @param array             $fieldvals_arr
         * @param array             $order_by
         * @param integer           $limit
         * @param integer           $offset
         *
         * @return array
         * @throws entity_exception
         */
        public static function by_fields_limit_multi(entity_link_dao $self, $pool, $local_field, entity_record_dao $foreign_dao,
                                                     array $fieldvals_arr, array $order_by, $offset, $limit) {

            $fieldvals_fields = array_keys(reset($fieldvals_arr));
            $select_fields    = [];
            $reverse_lookup   = [];
            $return           = [];
            $vals             = [];
            $queries          = [];

            // LOCAL
            $quoted_table = self::table($self::TABLE);

            // FK Relation
            $quoted_foreign_table = self::table($foreign_dao::TABLE);
            $foreign_pk           = $foreign_dao::PRIMARY_KEY;

            // SELECT
            foreach (array_unique(array_merge([ $local_field ], $fieldvals_fields)) as $field) {
                $select_fields[] = "\"{$quoted_table}\".\"{$field}\"";
            }

            // LIMIT
            if ($limit) {
                $limit = "LIMIT {$limit}";
            } else {
                $limit = '';
            }

            // OFFSET
            if ($offset !== null) {
                $offset = "OFFSET {$offset}";
            } else {
                $offset = '';
            }

            // ORDER BY
            $order = [];
            foreach ($order_by as $field => $sort_direction) {
                $order[] = "\"{$quoted_foreign_table}\".\"{$field}\" " . (entity_dao::SORT_DESC === $sort_direction ? 'DESC' : 'ASC');
            }
            $order_by = join(', ', $order);

            // QUERIES
            $query = "
                SELECT " . join(',', $select_fields) . "
                FROM \"" . self::table($self::TABLE) . "\"
                INNER JOIN \"{$quoted_foreign_table}\"
                ON \"{$quoted_foreign_table}\".\"{$foreign_pk}\" = \"{$quoted_table}\".\"{$local_field}\"
            ";
            foreach ($fieldvals_arr as $k => $fieldvals) {
                $where         = [];
                $return[$k]    = [];
                $hashed_valued = [];
                foreach ($fieldvals as $field => $val) {
                    if ($val === null) {
                        $where[]         = "\"{$quoted_table}\".\"{$field}\" IS NULL";
                        $hashed_valued[] = '';
                    } else {
                        $vals[]          = $val;
                        $where[]         = "\"{$quoted_table}\".\"{$field}\" = ?";
                        $hashed_valued[] = md5($val);
                    }
                }
                $reverse_lookup[join(':', $hashed_valued)] = $k;
                $queries[] = "(
                    {$query}
                    WHERE" . join(" AND ", $where) . "
                    ORDER BY {$order_by}
                    {$limit} {$offset}
                )";
            }

            $rs = core::sql($pool)->prepare(
                join(' UNION ALL ', $queries)
            );
            $rs->execute($vals);

            foreach ($rs->fetchAll() as $row) {
                $hashed = [];
                foreach ($fieldvals_fields as $field) {
                    $hashed[$row[$field]] = md5($row[$field]);
                }
                $return[$reverse_lookup[join(':', $hashed)]][] = $row[$local_field];
            }

            return $return;
        }

        /**
         * Get a count based on key inputs
         *
         * @param entity_record_dao $dao
         * @param string            $pool
         * @param array             $fieldvals
         *
         * @return integer
         */
        public static function count(entity_record_dao $dao, $pool, array $fieldvals=null) {
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

            $rs = core::sql($pool)->prepare("
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
         * @param entity_record_dao $self
         * @param string            $pool
         * @param array             $fieldvals_arr
         *
         * @return array
         */
        public static function count_multi(entity_record_dao $self, $pool, array $fieldvals_arr) {
            $queries = [];
            $vals    = [];

            foreach ($fieldvals_arr as $fieldvals) {
                $where = [];
                foreach ($fieldvals as $field => $val) {
                    if ($val === null) {
                        $where[] = "\"{$field}\" IS NULL";
                    } else {
                        $vals[]  = $val;
                        $where[] = "\"{$field}\" = ?";
                    }
                }

                $queries[] = "(
                    SELECT COUNT(*) \"num\"
                    FROM \"" . self::table($self::TABLE) . "\"
                    " . ($where ? " WHERE " . join(" AND ", $where) : '') . "
                )";
            }

            $rs = core::sql($pool)->prepare(join(' UNION ALL ', $queries));
            $rs->execute($vals);

            $keys   = array_keys($fieldvals_arr);
            $counts = [];
            foreach ($rs->fetchAll(PDO::FETCH_COLUMN, 0) as $k => $count) {
                $counts[$keys[$k]] = (int) $count;
            }
            return $counts;
        }

        /**
         * Insert a link
         *
         * @param entity_link_dao $self the name of the DAO
         * @param string          $pool which source engine pool to use
         * @param array           $info
         * @param bool            $replace
         *
         * @throws entity_exception
         */
        public static function insert(entity_link_dao $self, $pool, array $info, $replace) {

            if ($replace) {
                throw new entity_exception('PostgreSQL does not support REPLACE INTO.');
            }

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

            if (! $insert->execute(array_values($info))) {
                $error = core::sql($pool)->errorInfo();
                throw new entity_exception("Insert failed - {$error[0]}: {$error[2]}");
            }
        }

        /**
         * Insert multiple links
         *
         * @param entity_link_dao $self the name of the DAO
         * @param string          $pool which source engine pool to use
         * @param array           $infos
         * @param bool            $replace
         *
         * @throws entity_exception
         */
        public static function insert_multi(entity_link_dao $self, $pool, array $infos, $replace) {

            if ($replace) {
                throw new entity_exception('PostgreSQL does not support REPLACE INTO.');
            }

            $insert_fields = [];
            $info          = current($infos);
            $sql           = core::sql($pool);
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
                ( " . join(',', array_fill(0, count($info), '?')) . " )
            ");

            foreach ($infos as $info) {
                if (! $insert->execute(array_values($info))) {
                    $error = $sql->errorInfo();
                    $sql->rollback();
                    throw new entity_exception("Insert multi failed - {$error[0]}: {$error[2]}");
                }
            }

            if ($multi && ! $sql->commit()) {
                $error = $sql->errorInfo();
                throw new entity_exception("Insert multi failed - {$error[0]}: {$error[2]}");
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
         * @throws entity_exception
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
                    $vals[]         = $v;
                    $where_fields[] = "\"{$k}\" = ?";
                }
            }

            if (! core::sql($pool)->prepare("
                UPDATE \"" . self::table($self::TABLE) . "\"
                SET " . join(", \n", $update_fields) . "
                WHERE " . join(" AND \n", $where_fields) . "
            ")->execute($vals)) {
                $error = core::sql($pool)->errorInfo();
                throw new entity_exception("Update failed - {$error[0]}: {$error[2]}");
            }
        }

        /**
         * Delete one or more links
         *
         * @param entity_link_dao $self the name of the DAO
         * @param string          $pool which source engine pool to use
         * @param array           $fieldvals
         *
         * @throws entity_exception
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

            if (! core::sql($pool)->prepare("
                DELETE FROM \"" . self::table($self::TABLE) . "\"
                WHERE " . join(" AND ", $where) . "
            ")->execute($vals)) {
                $error = core::sql($pool)->errorInfo();
                throw new entity_exception("Delete failed - {$error[0]}: {$error[2]}");
            }
        }

        /**
         * Delete sets of links
         *
         * @param entity_link_dao $self the name of the DAO
         * @param string          $pool which source engine pool to use
         * @param array           $fieldvals_arr
         *
         * @throws entity_exception
         */
        public static function delete_multi(entity_link_dao $self, $pool, array $fieldvals_arr) {
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

            if (! core::sql($pool)->prepare("
                DELETE FROM \"" . self::table($self::TABLE) . "\"
                WHERE " . join(" OR ", $where) . "
            ")->execute($vals)) {
                $error = core::sql($pool)->errorInfo();
                throw new entity_exception("Delete multi failed - {$error[0]}: {$error[2]}");
            }
        }
    }