<?php

    namespace Neoform;

    use PDO;

    class Sql extends Service\Singleton {

        /**
         * @var PDO
         */
        protected $pdo;

        /**
         * @var string
         */
        protected $connectionPool;

        /**
         * @param string $connectionPool
         */
        public function __construct($connectionPool) {
            $this->connectionPool = $connectionPool;
        }

        /**
         * @return PDO
         */
        public function get() {
            if (! $this->pdo) {
                $this->open();
            }

            return $this->pdo;
        }

        /**
         * @return $this
         * @throws Sql\Exception
         */
        public function open() {

            // Get the class that instantiated this singleton
            $config = Sql\Config::get();

            // No connection name
            if (! $this->connectionPool) {
                if (! $config->getDefaultPoolWrite()) {
                    throw new Sql\Exception("No default write database connection has been specified");
                }

                // Try fallback connection
                $this->connectionPool = $config->getDefaultPoolWrite();
            }

            $connectionPools = $config->getPools();

            if (! isset($connectionPools[$this->connectionPool])) {
                throw new Sql\Exception("The database connection \"{$this->connectionPool}\" configuration could not be found.");
            }

            // Select a random connection:
            $connection = $connectionPools[$this->connectionPool][array_rand($connectionPools[$this->connectionPool])];

            $dsn  = isset($connection['dsn']) ? $connection['dsn'] : null;
            $user = isset($connection['user']) ? $connection['user'] : null;

            if (! $dsn || ! $user) {
                throw new Sql\Exception("The database connection \"{$this->connectionPool}\" has not been configured properly.");
            }

            try {
                $options = [
                    //PDO::ATTR_CASE                 => PDO::CASE_LOWER, // force lower case for all field names
                    PDO::ATTR_ERRMODE                => PDO::ERRMODE_EXCEPTION, // all errors should be exceptions
                    PDO::ATTR_DEFAULT_FETCH_MODE     => PDO::FETCH_ASSOC,
                    //PDO::ATTR_PERSISTENT           => true,
                ];

                if ($config->getEncoding()) {
                    $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES {$config->getEncoding()}";
                }

                $this->pdo = new Sql\Pdo(
                    $dsn,
                    $user,
                    isset($connection['password']) ? $connection['password'] : '',
                    $options
                );
            } catch (\Exception $e) {
                Core::log("Could not connect to database configuration \"{$this->connectionPool}\" -- {$e->getMessage()}", 'CRITICAL');
                throw new Sql\Exception('We are experiencing a brief interruption of service', 0, $e);
            }

            return $this;
        }

        /**
         * @return $this
         */
        public function close() {
            $this->pdo = null;
            return $this;
        }
    }