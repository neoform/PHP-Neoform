<?php

    interface entity_record_driver {
        public static function record(entity_record_dao $self, $pool, $pk);
        public static function records(entity_record_dao $self, $pool, array $pks);
        public static function count(entity_record_dao $self, $pool, array $keys=null);
        public static function all(entity_record_dao $self, $pool, $pk, array $keys=null);
        public static function by_fields(entity_record_dao $self, $pool, array $keys, $pk);
        public static function by_fields_multi(entity_record_dao $self, $pool, array $keys_arr, $pk);
        public static function by_fields_offset(entity_record_dao $self, $pool, array $keys, $pk, array $order_by, $offset, $limit);
        public static function by_fields_offset_multi(entity_record_dao $self, $pool, array $keys_arr, $pk, array $order_by, $offset, $limit);
        public static function insert(entity_record_dao $self, $pool, array $info, $autoincrement, $replace);
        public static function inserts(entity_record_dao $self, $pool, array $infos, $keys_match, $autoincrement, $replace);
        public static function update(entity_record_dao $self, $pool, $pk, entity_record_model $model, array $info);
        public static function delete(entity_record_dao $self, $pool, $pk, entity_record_model $model);
        public static function deletes(entity_record_dao $self, $pool, $pk, entity_record_collection $collection);
    }