<?php

    namespace Neoform\Sql;

    use PDO;
    use Neoform;
    use Exception;

    class Parser {

        protected static function driver() {
            return Neoform\Sql::instance()->getAttribute(PDO::ATTR_DRIVER_NAME);
        }

        /**
         * @param string $name
         *
         * @return Parser\Table
         * @throws Exception
         */
        public static function getTable($name) {
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
        public static function driverSpecificApiValidation(Parser\Field $field) {
            switch (self::driver()) {

                case 'pgsql':
                    return Parser\Driver\Pgsql::apiTypeValidation($field);

                case 'mysql':
                    return Parser\Driver\Mysql::apiTypeValidation($field);

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
        public static function isTableTiny(Parser\Table $table) {
            switch (self::driver()) {

                case 'pgsql':
                    return Parser\Driver\Pgsql::isTableTiny($table);

                case 'mysql':
                    return Parser\Driver\Mysql::isTableTiny($table);

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
        public static function isFieldLookupable(Parser\Field $field) {
            switch (self::driver()) {

                case 'pgsql':
                    return Parser\Driver\Pgsql::isFieldLookupable($field);

                case 'mysql':
                    return Parser\Driver\Mysql::isFieldLookupable($field);

                default:
                    throw new Exception('No parsing driver exists for "' . self::driver() . '"');
            }
        }
    }