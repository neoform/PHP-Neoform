<?php

    interface record_driver {
        public static function by_pk(record_dao $self, $pool, $pk);
        public static function by_pks(record_dao $self, $pool, array $pks);
        public static function all(record_dao $self, $pool, $pk, array $keys=null);
        public static function by_fields(record_dao $self, $pool, array $keys, $pk);
        public static function by_fields_multi(record_dao $self, $pool, array $keys_arr, $pk);
        public static function by_fields_select(record_dao $self, $pool, array $select_fields, array $keys);
        public static function insert(record_dao $self, $pool, array $info, $autoincrement, $replace);
        public static function inserts(record_dao $self, $pool, array $infos, $keys_match, $autoincrement, $replace);
        public static function update(record_dao $self, $pool, $pk, record_model $model, array $info);
        public static function delete(record_dao $self, $pool, $pk, record_model $model);
        public static function deletes(record_dao $self, $pool, $pk, record_collection $collection);
    }