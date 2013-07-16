<?php

    class sql_driver_mysql implements sql_driver {

        /**
         * @param string $field_name
         *
         * @return string
         */
        public static function quote_field_name($field_name) {
            return "`{$field_name}`";
        }
    }