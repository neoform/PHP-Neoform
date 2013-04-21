<?php

    interface record_driver {
        public static function by_pk($self, $pk);
        public static function by_pks($self, array $pks);
        public static function all($self, $pk, array $keys=null);
        public static function by_fields($self, array $keys, $pk);
        public static function by_fields_multi($self, array $keys_arr, $pk);
        public static function by_fields_select($self, array $select_fields, array $keys);
        public static function insert($self, array $info, $autoincrement, $replace);
        public static function inserts($self, array $infos, $keys_match, $autoincrement, $replace);
        public static function update($self, $pk, record_model $model, array $info);
        public static function delete($self, $pk, record_model $model);
        public static function deletes($self, $pk, record_collection $collection);
    }