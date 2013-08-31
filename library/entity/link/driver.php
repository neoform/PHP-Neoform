<?php

    interface entity_link_driver {
        public static function by_fields(entity_link_dao $self, $pool, array $select_fields, array $keys);

        public static function by_fields_multi(entity_link_dao $self, $pool, array $select_fields, array $keys_arr);

        public static function by_field_limit(entity_link_dao $self, $pool, $local_field, entity_record_dao $foreign_dao,
                                              array $fieldvals, array $order_by, $offset, $limit);

        public static function by_field_multi_limit(entity_link_dao $self, $pool, $local_field,
                                                    entity_record_dao $foreign_dao, array $fieldvals_arr,
                                                    array $order_by, $offset, $limit);

        public static function insert(entity_link_dao $self, $pool, array $info, $replace);

        public static function inserts(entity_link_dao $self, $pool, array $infos, $replace);

        public static function update(entity_link_dao $self, $pool, array $new_info, array $where);

        public static function delete(entity_link_dao $self, $pool, array $keys);

        public static function deletes(entity_link_dao $self, $pool, array $keys_arr);
    }