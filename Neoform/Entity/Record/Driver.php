<?php

    namespace Neoform\Entity\Record;

    interface Driver {

        public static function record(Dao $self, $pool, $pk);

        public static function records(Dao $self, $pool, array $pks);

        public static function count(Dao $self, $pool, array $keys=null);

        public static function countMulti(Dao $self, $pool, array $fieldvals_arr);

        public static function all(Dao $self, $pool, $pk, array $keys=null);

        public static function by_fields(Dao $self, $pool, array $keys, $pk);

        public static function by_fields_multi(Dao $self, $pool, array $keys_arr, $pk);

        public static function by_fields_offset(Dao $self, $pool, array $keys, $pk, array $order_by, $offset, $limit);

        public static function by_fields_offset_multi(Dao $self, $pool, array $keys_arr, $pk, array $order_by, $offset,
                                                      $limit);

        public static function insert(Dao $self, $pool, array $info, $autoincrement, $replace, $ttl);

        public static function insertMulti(Dao $self, $pool, array $infos, $keys_match, $autoincrement, $replace, $ttl);

        public static function update(Dao $self, $pool, $pk, Model $model, array $info, $ttl);

        public static function delete(Dao $self, $pool, $pk, Model $model);

        public static function deleteMulti(Dao $self, $pool, $pk, collection $collection);
    }