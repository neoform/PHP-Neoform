<?php

    class entity_record_limit_driver_mysql implements entity_record_limit_driver {


        public static function by_pk(entity_record_dao $self, $pool, $pk) {

        }

        public static function by_pks(entity_record_dao $self, $pool, array $pks) {

        }

        public static function by_fields_offset(entity_record_dao $self, $pool, array $keys, $pk, array $keys, array $order_by, $offset, $limit) {
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

            $limit = $limit ? "{$limit} OFFSET {$offset}" : $offset;

            $order = [];
            foreach ($order_by as $field => $order) {
                $order[] = "`{$field}` " . (strtoupper($order) === 'DESC' ? 'DESC' : 'ASC');
            }
            $order_by = join(', ', $order);

            $rs = core::sql($pool)->prepare("
                SELECT `{$pk}`
                FROM `" . self::table($self::TABLE) . "`
                " . ($where ? " WHERE " . join(" AND ", $where) : '') . "
                ORDER BY {$order_by}
                LIMIT {$limit}
            ");
            $rs->execute($vals);

            return array_column($rs->fetchAll(), $pk);
        }

        public static function by_fields_after(entity_record_dao $self, $pool, array $keys, $pk, array $keys, array $order_by, $after_pk, $limit) {
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

            $limit = $limit ? "{$limit} OFFSET {$offset}" : $offset;

            $order = [];
            foreach ($order_by as $field => $order) {
                $order[] = "`{$field}` " . (strtoupper($order) === 'DESC' ? 'DESC' : 'ASC');
            }
            $order_by = join(', ', $order);

            $rs = core::sql($pool)->prepare("
                SELECT `{$pk}`
                FROM `" . self::table($self::TABLE) . "`
                " . ($where ? " WHERE " . join(" AND ", $where) : '') . "
                ORDER BY {$order_by}
                LIMIT {$limit}
            ");
            $rs->execute($vals);

            return array_column($rs->fetchAll(), $pk);
        }

        public static function by_fields_multi(entity_record_dao $self, $pool, array $keys_arr, $pk, array $keys, array $order_by, $offset, $limit) {

        }

        public static function by_fields_select(entity_record_dao $self, $pool, array $select_fields, array $keys) {

        }

        public static function insert(entity_record_dao $self, $pool, array $info, $autoincrement, $replace) {

        }

        public static function inserts(entity_record_dao $self, $pool, array $infos, $keys_match, $autoincrement, $replace) {

        }

        public static function update(entity_record_dao $self, $pool, $pk, entity_record_model $model, array $info) {

        }

        public static function delete(entity_record_dao $self, $pool, $pk, entity_record_model $model) {

        }

        public static function deletes(entity_record_dao $self, $pool, $pk, entity_record_collection $collection) {

        }
    }