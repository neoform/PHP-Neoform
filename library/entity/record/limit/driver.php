<?php

    interface entity_record_limit_driver {
        public static function by_fields_offset(entity_record_dao $self, $pool, array $keys, $pk, array $order_by, $offset, $limit);
        public static function by_fields_offset_multi(entity_record_dao $self, $pool, array $keys_arr, $pk, array $order_by, $offset, $limit);
    }