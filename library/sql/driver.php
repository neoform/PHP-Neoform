<?php

    interface sql_driver {
        public static function quote_field_name($field_name);
    }