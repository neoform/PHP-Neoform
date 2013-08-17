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

            $limit = $limit ? "{$limit} OFFSET {$offset}" : $offset;

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
                LIMIT {$limit}
            ");
            $rs->execute($vals);

            return array_column($rs->fetchAll(), $pk);
        }

        public static function by_fields_offset_multi(entity_record_dao $self, $pool, array $keys_arr, $pk, array $order_by, $offset, $limit) {
            $key_fields     = [];
            $reverse_lookup = [];
            $return         = [];
            $vals           = [];
            $queries        = [];

            foreach (array_keys(reset($keys_arr)) as $k) {
                $key_fields[] = "\"{$k}\"";
            }

            // @todo, this is potentially problematic, if the field value contains colons... :(
            $cache_key_field = count($key_fields) === 1 ? reset($key_fields) : " CONCAT(" . join(", ':', ", $key_fields) . ")";

            $limit = $limit ? "{$limit} OFFSET {$offset}" : $offset;

            $order = [];
            foreach ($order_by as $field => $sort_direction) {
                $order[] = "\"{$field}\" " . (entity_record_limit_dao::SORT_DESC === $sort_direction ? 'DESC' : 'ASC');
            }
            $order_by = join(', ', $order);

            foreach ($keys_arr as $k => $keys) {
                $where = [];
                $reverse_lookup[join(':', $keys)] = $k;
                $return[$k] = [];
                foreach ($keys as $k => $v) {
                    if ($v === null) {
                        $where[] = "\"{$k}\" IS NULL";
                    } else {
                        $vals[]  = $v;
                        $where[] = "\"{$k}\" = ?";
                    }
                }

                $queries[] = "(
                    SELECT
                        \"{$pk}\",
                        {$cache_key_field} \"__cache_key__\"
                    FROM \"" . self::table($self::TABLE) . "\"
                    WHERE " . join(" AND ", $where) . "
                    ORDER BY {$order_by}
                    LIMIT {$limit}
                )";
            }

            $rs = core::sql($pool)->prepare(
                join(" UNION ", $queries)
            );

            $rs->execute($vals);

            foreach ($rs->fetchAll() as $row) {
                $return[$reverse_lookup[$row['__cache_key__']]][] = $row[$pk];
            }

            return $return;
        }

        public static function by_fields_after(entity_record_dao $self, $pool, array $keys, $pk, array $order_by, $after_pk, $limit) {
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

            //$limit = $limit ? "{$limit} OFFSET {$offset}" : $offset;

            $order = [];
            foreach ($order_by as $field => $order) {
                $order[] = "\"{$field}\" " . (strtoupper($order) === 'DESC' ? 'DESC' : 'ASC');
            }
            $order_by = join(', ', $order);

            $rs = core::sql($pool)->prepare("
                SELECT \"{$pk}\"
                FROM \"" . self::table($self::TABLE) . "\"
                " . ($where ? " WHERE " . join(" AND ", $where) : '') . "
                ORDER BY {$order_by}
                LIMIT {$limit}
            ");
            $rs->execute($vals);

            return array_column($rs->fetchAll(), $pk);
        }

        public static function by_fields_after_multi(entity_record_dao $self, $pool, array $keys_arr, $pk, array $order_by, $after_pk, $limit) {

        }
    }