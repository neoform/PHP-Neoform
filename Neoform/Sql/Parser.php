<?php

    namespace Neoform\Sql;

    use PDO;
    use Neoform;
    use Exception;

    class Parser {

        protected static function driver() {
            return Neoform\Sql::instance()->getAttribute(PDO::ATTR_DRIVER_NAME);
        }

        public static function get_table($name) {
            switch (self::driver()) {

                case 'pgsql':
                    $parser = new Parser\Driver\Pgsql;
                    break;

                case 'mysql':
                    $parser = new Parser\Driver\Mysql;
                    break;

                default:
                    throw new Exception('No parsing driver exists for "' . self::driver() . '"');
            }

            $tables = $parser->tables();

            if (isset($tables[$name])) {
                return $tables[$name];
            } else {
                throw new Exception('That table could not be found');
            }
        }

        /**
         * Identify driver specific validation for this field
         *
         * @param Parser\Field $field
         *
         * @return string
         * @throws exception
         */
        public static function driver_specific_api_validation(Parser\Field $field) {
            switch (self::driver()) {

                case 'pgsql':
                    return Parser\Driver\Pgsql::api_type_validation($field);

                case 'mysql':
                    return Parser\Driver\Mysql::api_type_validation($field);

                default:
                    throw new Exception('No parsing driver exists for "' . self::driver() . '"');
            }
        }

        /**
         * Does this table have a primary key that allows only a small number of rows?
         *
         * @param Parser\table $table
         *
         * @return bool
         * @throws exception
         */
        public static function is_table_tiny(Parser\Table $table) {
            switch (self::driver()) {

                case 'pgsql':
                    return Parser\Driver\Pgsql::is_table_tiny($table);

                case 'mysql':
                    return Parser\Driver\Mysql::is_table_tiny($table);

                default:
                    throw new Exception('No parsing driver exists for "' . self::driver() . '"');
            }
        }

        /**
         * Can this field be useful for an equality lookup? (datetimes are an example of a field that is not useful)
         *
         * @param Parser\Field $field
         *
         * @return bool
         * @throws exception
         */
        public static function is_field_lookupable(Parser\Field $field) {
            switch (self::driver()) {

                case 'pgsql':
                    return Parser\Driver\Pgsql::is_field_lookupable($field);

                case 'mysql':
                    return Parser\Driver\Mysql::is_field_lookupable($field);

                default:
                    throw new Exception('No parsing driver exists for "' . self::driver() . '"');
            }
        }
    }