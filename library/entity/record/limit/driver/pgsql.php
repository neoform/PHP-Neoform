<?php

    class entity_record_limit_driver_pgsql extends entity_record_driver_pgsql implements entity_record_limit_driver {

        public static function by_fields_offset(entity_record_dao $self, $pool, array $keys, $pk, array $order_by, $offset, $limit) {
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

            if ($limit) {
                $limit = "LIMIT {$limit}" . ($offset !== null ? " OFFSET {$offset}" : '');
            } else {
                $limit = '';
            }

            $order = [];
            foreach ($order_by as $field => $sort_direction) {
                $order[] = "\"{$field}\" " . (entity_record_limit_dao::SORT_DESC === $sort_direction ? 'DESC' : 'ASC');
            }
            $order_by = join(', ', $order);

            $rs = core::sql($pool)->prepare("
                SELECT \"{$pk}\"
                FROM \"" . self::table($self::TABLE) . "\"
                " . ($where ? " WHERE " . join(" AND ", $where) : '') . "
                ORDER BY {$order_by}
                {$limit}
            ");
            $rs->execute($vals);

            return array_column($rs->fetchAll(), $pk);
        }

        public static function by_fields_offset_multi(entity_record_dao $self, $pool, array $keys_arr, $pk, array $order_by, $offset, $limit) {
            $select_fields  = [ "\"{$pk}\"" ];
            $reverse_lookup = [];
            $return         = [];
            $vals           = [];
            $queries        = [];
            $fields         = array_keys(reset($keys_arr));

            foreach ($fields as $k) {
                $select_fields[] = "`{$k}`";
            }

            if ($limit) {
                $limit = "LIMIT {$limit}" . ($offset !== null ? " OFFSET {$offset}" : '');
            } else {
                $limit = '';
            }

            $order = [];
            foreach ($order_by as $field => $sort_direction) {
                $order[] = "\"{$field}\" " . (entity_record_limit_dao::SORT_DESC === $sort_direction ? 'DESC' : 'ASC');
            }
            $order_by = join(', ', $order);

            foreach ($keys_arr as $k => $keys) {
                $where         = [];
                $return[$k]    = [];
                $hashed_valued = [];
                foreach ($keys as $key => $val) {
                    if ($val === null) {
                        $where[]         = "\"{$key}\" IS NULL";
                        $hashed_valued[] = '';
                    } else {
                        $vals[]  = $val;
                        $where[] = "\"{$key}\" = ?";
                        $hashed_valued[] = md5($val);
                    }
                }
                $reverse_lookup[join(':', $hashed_valued)] = $k;

                $queries[] = "(
                    SELECT " . join(", ", $select_fields) . "
                    FROM \"" . self::table($self::TABLE) . "\"
                    WHERE " . join(" AND ", $where) . "
                    ORDER BY {$order_by}
                    {$limit}
                )";
            }

            $rs = core::sql($pool)->prepare(
                join(" UNION ", $queries)
            );

            $rs->execute($vals);

            foreach ($rs->fetchAll() as $row) {
                $hashed = [];
                foreach ($fields as $k) {
                    $hashed[$row[$k]] = md5($row[$k]);
                }
                $return[$reverse_lookup[join(':', $hashed)]][] = $row[$pk];
            }

            return $return;
        }
    }