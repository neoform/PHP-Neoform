<?php

    namespace neoform\entity\link;

    use neoform\entity\record;

    interface driver {

        public static function by_fields(dao $self, $pool, array $select_fields, array $keys);

        public static function by_fields_multi(dao $self, $pool, array $select_fields, array $keys_arr);

        public static function by_fields_limit(dao $self, $pool, $local_field, record\dao $foreign_dao,
                                              array $fieldvals, array $order_by, $offset, $limit);

        public static function by_fields_limit_multi(dao $self, $pool, $local_field, record\dao $foreign_dao,
                                                     array $fieldvals_arr, array $order_by, $offset, $limit);

        public static function count(dao $self, $pool, array $keys=null);

        public static function count_multi(dao $self, $pool, array $fieldvals_arr);

        public static function insert(dao $self, $pool, array $info, $replace);

        public static function insert_multi(dao $self, $pool, array $infos, $replace);

        public static function update(dao $self, $pool, array $new_info, array $where);

        public static function delete(dao $self, $pool, array $keys);

        public static function delete_multi(dao $self, $pool, array $keys_arr);
    }