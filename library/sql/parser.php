<?php

    class sql_parser {

        protected static function driver() {
            return core::sql()->getAttribute(PDO::ATTR_DRIVER_NAME);
        }

        public static function get_table($name) {
            switch (self::driver()) {

                case 'pgsql':
                    $parser = new sql_parser_driver_postgresql();
                    break;

                case 'mysql':
                    $parser = new sql_parser_driver_mysql();
                    break;

                default:
                    throw new exception('No parsing driver exists for "' . self::driver() . '"');
            }

            $tables = $parser->tables();

            if (isset($tables[$name])) {
                return $tables[$name];
            } else {
                throw new exception('That table could not be found');
            }
        }

        /**
         * Identify driver specific validation for this field
         *
         * @param sql_parser_field $field
         *
         * @return string
         * @throws exception
         */
        public static function driver_specific_api_validation(sql_parser_field $field) {
            switch (self::driver()) {

                case 'pgsql':
                    return sql_parser_driver_postgresql::api_type_validation($field);

                case 'mysql':
                    return sql_parser_driver_mysql::api_type_validation($field);

                default:
                    throw new exception('No parsing driver exists for "' . self::driver() . '"');
            }
        }

        /**
         * Does this table have a primary key that allows only a small number of rows?
         *
         * @param sql_parser_table $table
         *
         * @return bool
         * @throws exception
         */
        public static function is_table_tiny(sql_parser_table $table) {
            switch (self::driver()) {

                case 'pgsql':
                    return sql_parser_driver_postgresql::is_table_tiny($table);

                case 'mysql':
                    return sql_parser_driver_mysql::is_table_tiny($table);

                default:
                    throw new exception('No parsing driver exists for "' . self::driver() . '"');
            }
        }

        /**
         * Can this field be useful for an equality lookup? (datetimes are an example of a field that is not useful)
         *
         * @param sql_parser_field $field
         *
         * @return bool
         * @throws exception
         */
        public static function is_field_lookupable(sql_parser_field $field) {
            switch (self::driver()) {

                case 'pgsql':
                    return sql_parser_driver_postgresql::is_field_lookupable($field);

                case 'mysql':
                    return sql_parser_driver_mysql::is_field_lookupable($field);

                default:
                    throw new exception('No parsing driver exists for "' . self::driver() . '"');
            }
        }
    }