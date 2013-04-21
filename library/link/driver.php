<?php

    interface link_driver {
        public static function by_fields($self, array $select_fields, array $keys);
        public static function by_fields_multi($self, array $select_fields, array $keys_arr);
        public static function insert($self, array $info, $replace);
        public static function inserts($self, array $infos, $replace);
        public static function update($self, array $new_info, array $where);
        public static function delete($self, array $keys);
        public static function deletes($self, array $keys_arr);
    }