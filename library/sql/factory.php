<?php

    class sql_factory implements core_factory {

        public static function init(array $args) {
            $name = count($args) ? current($args) : null;

            //get the class that instatiated this singleton
            $config = core::config()->sql;

            if (! isset($config[$name])) {
                if ($name !== $config['fallback_connection']) {
                    //try fallback connection
                    return core::sql($config['fallback_connection']);
                } else {
                    throw new error_exception('The database connection "' . $name . '" configuration could not be found.');
                }
            }

            //select a random connection id if there is more than one:
            $count = count($config[$name]);
            $id    = $count > 1 ? mt_rand(0, $count - 1) : 0;
            $dsn   = isset($config[$name][$id]['dsn']) ? $config[$name][$id]['dsn'] : false;
            $user  = isset($config[$name][$id]['user']) ? $config[$name][$id]['user'] : false;

            if (! $dsn || ! $user) {
                throw new error_exception('The database connection "' . $name . '" has not been configured properly.');
            }

            $password = isset($config[$name][$id]['password']) ? $config[$name][$id]['password'] : '';

            try {
                $options = [
                    //PDO::ATTR_CASE                 => PDO::CASE_LOWER, // force lower case for all field names
                    PDO::ATTR_ERRMODE                => PDO::ERRMODE_EXCEPTION, // all errors should be exceptions
                    PDO::ATTR_DEFAULT_FETCH_MODE     => PDO::FETCH_ASSOC,
                    //PDO::ATTR_PERSISTENT           => true,
                ];

                if (isset($config['encoding'])) {
                    $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES " . $config['encoding'];
                }

                //return new sql_debug(
                return new sql_pdo(
                    $dsn,
                    $user,
                    $password,
                    $options
                );
            } catch (exception $e) {
                core::log('Could not connect to database configuration "' . $name . '" -- ' . $e->getMessage(), 'CRITICAL');
                throw new error_exception('We are experiencing a brief interruption of service', 'Please try again in a few moments...');
            }
        }
    }