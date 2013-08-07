<?php

    interface link_driver {
        public static function by_fields(link_dao $self, $pool, array $select_fields, array $keys);
        public static function by_fields_multi(link_dao $self, $pool, array $select_fields, array $keys_arr);
        public static function insert(link_dao $self, $pool, array $info, $replace);
        public static function inserts(link_dao $self, $pool, array $infos, $replace);
        public static function update(link_dao $self, $pool, array $new_info, array $where);
        public static function delete(link_dao $self, $pool, array $keys);
        public static function deletes(link_dao $self, $pool, array $keys_arr);
    }