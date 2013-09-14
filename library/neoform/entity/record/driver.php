<?php

    namespace neoform\entity\record;

    interface driver {

        public static function record(dao $self, $pool, $pk);

        public static function records(dao $self, $pool, array $pks);

        public static function count(dao $self, $pool, array $keys=null);

        public static function count_multi(dao $self, $pool, array $fieldvals_arr);

        public static function all(dao $self, $pool, $pk, array $keys=null);

        public static function by_fields(dao $self, $pool, array $keys, $pk);

        public static function by_fields_multi(dao $self, $pool, array $keys_arr, $pk);

        public static function by_fields_offset(dao $self, $pool, array $keys, $pk, array $order_by, $offset, $limit);

        public static function by_fields_offset_multi(dao $self, $pool, array $keys_arr, $pk, array $order_by, $offset,
                                                      $limit);

        public static function insert(dao $self, $pool, array $info, $autoincrement, $replace);

        public static function insert_multi(dao $self, $pool, array $infos, $keys_match, $autoincrement, $replace);

        public static function update(dao $self, $pool, $pk, model $model, array $info);

        public static function delete(dao $self, $pool, $pk, model $model);

        public static function delete_multi(dao $self, $pool, $pk, collection $collection);
    }