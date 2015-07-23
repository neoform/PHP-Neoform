<?php

    namespace Neoform\Entity\Repo\RecordSource\Driver;
    
    use Neoform\Entity\Exception;
    use Neoform\Entity;
    use Neoform\Sql;
    use Neoform;

    use PDO;
    use PDOException;
    use SplFixedArray;

    class MySQL implements Neoform\Entity\Repo\RecordSource\Driver {

        /**
         * @var Sql\Pdo
         */
        protected $readMySQLService;

        /**
         * @var Sql\Pdo
         */
        protected $writeMySQLService;

        /**
         * @var string
         */
        protected $tableName;

        /**
         * @var string
         */
        protected $primaryKey;

        /**
         * @var bool
         */
        protected $autoIncrement;

        /**
         * @param Entity\Record\Dao $dao
         * @param Sql               $readMySQLService
         * @param Sql               $writeMySQLService
         */
        public function __construct(Entity\Record\Dao $dao, Sql $readMySQLService, Sql $writeMySQLService) {
            $this->readMySQLService  = $readMySQLService;
            $this->writeMySQLService = $writeMySQLService;

            $this->primaryKey    = $dao::PRIMARY_KEY;
            $this->autoIncrement = $dao::AUTOINCREMENT;

            if (strpos($dao::TABLE, '.') !== false) {
                $table = explode('.', $dao::TABLE);
                $this->tableName = "{$table[0]}`.`{$table[1]}";
            } else {
                $this->tableName = $dao::TABLE;
            }
        }

        /**
         * Get full record by primary key
         *
         * @param int|string|null $pk
         *
         * @return array
         */
        public function record($pk) {
            $info = $this->readMySQLService->get()->prepare("
                SELECT *
                FROM `{$this->tableName}`
                WHERE `{$this->primaryKey}` = ?
            ");

            $info->execute([
                $pk,
            ]);

            return $info->fetch() ?: null;
        }

        /**
         * Get full records by primary key
         *
         * @param array $pks
         *
         * @return array
         */
        public function records(array $pks) {
            $infosRs = $this->readMySQLService->get()->prepare("
                SELECT *
                FROM `{$this->tableName}`
                WHERE `{$this->primaryKey}` IN (" . join(',', array_fill(0, count($pks), '?')) . ")
            ");
            $infosRs->execute(array_values($pks));

            $infos = [];
            foreach ($infosRs->fetchAll() as $info) {
                $k = array_search($info[$this->primaryKey], $pks);
                if ($k !== false) {
                    $infos[$k] = $info;
                }
            }

            return $infos;
        }

        /**
         * Get a count
         *
         * @param array $fieldVals
         *
         * @return int
         */
        public function count(array $fieldVals=null) {
            $where = [];
            $vals  = [];

            if ($fieldVals) {
                foreach ($fieldVals as $field => $val) {
                    if ($val === null) {
                        $where[] = "`{$field}` IS NULL";
                    } else {
                        $vals[]  = $val;
                        $where[] = "`{$field}` = ?";
                    }
                }
            }

            $rs = $this->readMySQLService->get()->prepare("
                SELECT COUNT(0) `num`
                FROM `{$this->tableName}`
                " . ($where ? " WHERE " . join(" AND ", $where) : '') . "
            ");
            $rs->execute($vals);
            return (int) $rs->fetch()['num'];
        }

        /**
         * Get multiple counts
         *
         * @param array $fieldValsArr
         *
         * @return int[]
         */
        public function countMulti(array $fieldValsArr) {
            $queries = [];
            $vals    = [];
            $counts  = [];

            foreach ($fieldValsArr as $k => $fieldVals) {
                $where      = [];
                $counts[$k] = [];
                $vals[]     = $k;

                foreach ($fieldVals as $field => $val) {
                    if ($val === null) {
                        $where[] = "`{$field}` IS NULL";
                    } else {
                        $vals[]  = $val;
                        $where[] = "`{$field}` = ?";
                    }
                }

                $queries[] = "(
                    SELECT COUNT(0) `num`, ? k
                    FROM `{$this->tableName}`
                    " . ($where ? " WHERE " . join(" AND ", $where) : '') . "
                )";
            }

            $rs = $this->readMySQLService->get()->prepare(join(' UNION ALL ', $queries));
            $rs->execute($vals);

            foreach ($rs->fetchAll() as $row) {
                $counts[$row['k']] = (int) $row['num'];
            }

            return $counts;
        }

        /**
         * Get all records in the table
         *
         * @param array $fieldVals
         *
         * @return array
         */
        public function all(array $fieldVals=null) {
            $where = [];
            $vals  = [];

            if ($fieldVals) {
                foreach ($fieldVals as $field => $val) {
                    if ($val === null) {
                        $where[] = "`{$field}` IS NULL";
                    } else {
                        $vals[]  = $val;
                        $where[] = "`{$field}` = ?";
                    }
                }
            }

            $info = $this->readMySQLService->get()->prepare("
                SELECT *
                FROM `{$this->tableName}`
                " . ($where ? " WHERE " . join(" AND ", $where) : '') . "
                ORDER BY `{$this->primaryKey}` ASC
            ");

            $info->execute($vals);

            return array_column($info->fetchAll(), null, $this->primaryKey);
        }

        /**
         * Get record primary key by fields
         *
         * @param array $fieldVals
         *
         * @return array
         */
        public function byFields(array $fieldVals) {
            $where = [];
            $vals  = [];

            if ($fieldVals) {
                foreach ($fieldVals as $field => $val) {
                    if ($val === null) {
                        $where[] = "`{$field}` IS NULL";
                    } else {
                        $vals[]  = $val;
                        $where[] = "`{$field}` = ?";
                    }
                }
            }

            $rs = $this->readMySQLService->get()->prepare("
                SELECT `{$this->primaryKey}`
                FROM `{$this->tableName}`
                " . ($where ? " WHERE " . join(" AND ", $where) : '') . "
            ");
            $rs->execute($vals);

            return $rs->fetchAll(PDO::FETCH_COLUMN, 0);
        }

        /**
         * Get multiple record primary keys by fields
         *
         * @param array $fieldValsArr
         *
         * @return array
         */
        public function byFieldsMulti(array $fieldValsArr) {
            $return  = [];
            $vals    = [];
            $queries = [];

            $query = "
                SELECT `{$this->primaryKey}`, ? `__k__`
                FROM `{$this->tableName}`
            ";
            foreach ($fieldValsArr as $k => $fieldVals) {
                $where      = [];
                $return[$k] = [];
                $vals[]     = $k;

                foreach ($fieldVals as $field => $val) {
                    if ($val === null) {
                        $where[] = "`{$field}` IS NULL";
                    } else {
                        $vals[]  = $val;
                        $where[] = "`{$field}` = ?";
                    }
                }

                $queries[] = "(
                    {$query}
                    WHERE " . join(' AND ', $where) . "
                )";
            }

            $rs = $this->readMySQLService->get()->prepare(
                join(' UNION ALL ', $queries)
            );

            $rs->execute($vals);

            foreach ($rs->fetchAll() as $row) {
                $return[$row['__k__']][] = $row[$this->primaryKey];
            }

            return $return;
        }

        /**
         * Get a set of PKs based on params, in a given order and offset/limit
         *
         * @param array        $fieldVals
         * @param array        $orderBy
         * @param integer|null $offset
         * @param integer      $limit
         *
         * @return array
         */
        public function byFieldsOffset(array $fieldVals, array $orderBy, $offset, $limit) {
            $where = [];
            $vals  = [];

            if ($fieldVals) {
                foreach ($fieldVals as $field => $val) {
                    if ($val === null) {
                        $where[] = "`{$field}` IS NULL";
                    } else {
                        $vals[]  = $val;
                        $where[] = "`{$field}` = ?";
                    }
                }
            }

            // LIMIT
            if ($limit) {
                $limit = "LIMIT {$limit}";
            } else if ($offset !== null) {
                $limit = 'LIMIT 18446744073709551610'; // Official mysql docs say to do this... :P
            } else {
                $limit = '';
            }

            // OFFSET
            $offset = $offset !== null ? "OFFSET {$offset}" : '';

            $order = [];
            foreach ($orderBy as $field => $sort_direction) {
                $order[] = "`{$field}` " . (Entity\Dao::SORT_DESC === $sort_direction ? 'DESC' : 'ASC');
            }
            $orderBy = join(', ', $order);

            $rs = $this->readMySQLService->get()->prepare("
                SELECT `{$this->primaryKey}`
                FROM `{$this->tableName}`
                " . ($where ? " WHERE " . join(" AND ", $where) : '') . "
                ORDER BY {$orderBy}
                {$limit} {$offset}
            ");
            $rs->execute($vals);

            return $rs->fetchAll(PDO::FETCH_COLUMN, 0);
        }

        /**
         * Get multiple sets of PKs based on params, in a given order and offset/limit
         *
         * @param array        $fieldValsArr
         * @param array        $orderBy
         * @param integer|null $offset
         * @param integer      $limit
         *
         * @return array
         */
        public function byFieldsOffsetMulti(array $fieldValsArr, array $orderBy, $offset, $limit) {
            $return  = [];
            $vals    = [];
            $queries = [];

            // LIMIT
            if ($limit) {
                $limit = "LIMIT {$limit}";
            } else if ($offset !== null) {
                $limit = 'LIMIT 18446744073709551610'; // Official mysql docs say to do this... :P
            } else {
                $limit = '';
            }

            // OFFSET
            $offset = $offset !== null ? "OFFSET {$offset}" : '';

            // ORDER BY
            $order = [];
            foreach ($orderBy as $field => $sort_direction) {
                $order[] = "`{$field}` " . (Entity\Dao::SORT_DESC === $sort_direction ? 'DESC' : 'ASC');
            }
            $orderBy = join(', ', $order);

            // QUERY
            $query = "
                SELECT `{$this->primaryKey}`, ? `__k__`
                FROM `{$this->tableName}`
            ";

            foreach ($fieldValsArr as $k => $fieldVals) {
                $where      = [];
                $return[$k] = [];
                $vals[]     = $k;
                foreach ($fieldVals as $field => $val) {
                    if ($val === null) {
                        $where[] = "`{$field}` IS NULL";
                    } else {
                        $vals[]  = $val;
                        $where[] = "`{$field}` = ?";
                    }
                }

                $queries[] = "(
                    {$query}
                    WHERE " . join(" AND ", $where) . "
                    ORDER BY {$orderBy}
                    {$limit} {$offset}
                )";
            }

            $rs = $this->readMySQLService->get()->prepare(
                join(' UNION ALL ', $queries)
            );

            $rs->execute($vals);

            foreach ($rs->fetchAll() as $row) {
                $return[$row['__k__']][] = $row[$this->primaryKey];
            }

            return $return;
        }

        /**
         * Insert record
         *
         * @param array $info
         * @param bool  $replace
         * @param int   $ttl
         * @param bool  $reloadFromSource
         *
         * @return array
         * @throws Exception
         */
        public function insert(array $info, $replace, $ttl, $reloadFromSource) {

            if ($ttl) {
                throw new Exception('MySQL does not support TTL');
            }

            $insertFields = [];
            foreach (array_keys($info) as $key) {
                $insertFields[] = "`{$key}`";
            }

            $writeMySQL = $this->writeMySQLService->get();

            $insert = $writeMySQL->prepare("
                " . ($replace ? 'REPLACE' : 'INSERT') . " INTO `{$this->tableName}`
                ( " . join(', ', $insertFields) . " )
                VALUES
                ( " . join(',', array_fill(0, count($insertFields), '?')) . " )
            ");

            try {
                if (! $insert->execute(array_values($info))) {
                    $error = $writeMySQL->errorInfo();
                    throw new Exception("Insert failed - {$error[0]}: {$error[2]}");
                }
            } catch (PDOException $e) {
                $error = $writeMySQL->errorInfo();
                throw new Exception("Insert failed - {$error[0]}: {$error[2]}");
            }

            if ($reloadFromSource) {
                /**
                 * Use master to avoid race condition
                 */
                $infosRs = $writeMySQL->prepare("
                    SELECT *
                    FROM `{$this->tableName}`
                    WHERE `{$this->primaryKey}` = ?
                ");

                $infosRs->execute([
                    $this->autoIncrement ? $writeMySQL->lastInsertId() : $info[$this->primaryKey],
                ]);

                return $infosRs->fetch();
            }

            if ($this->autoIncrement) {
                $info[$this->primaryKey] = $writeMySQL->lastInsertId();
            }

            return $info;
        }

        /**
         * Insert multiple records
         *
         * @param array $infos
         * @param bool  $keyMatch
         * @param bool  $replace
         * @param int   $ttl
         * @param bool  $reloadFromSource
         *
         * @return array
         * @throws Exception
         */
        public function insertMulti(array $infos, $keyMatch, $replace, $ttl, $reloadFromSource) {

            if ($ttl) {
                throw new Exception('MySQL does not support TTL');
            }

            $writeMySQL = $this->writeMySQLService->get();

            if ($keyMatch) {
                $insertFields = [];
                foreach (array_keys(reset($infos)) as $k) {
                    $insertFields[] = "`{$k}`";
                }

                /**
                 * If the table is auto increment, we cannot lump all inserts into one query
                 * since we need the returned IDs for cache-busting and to return a model
                 */
                if ($this->autoIncrement) {
                    $writeMySQL->beginTransaction();

                    $insert = $writeMySQL->prepare("
                        " . ($replace ? 'REPLACE' : 'INSERT') . " INTO
                        `{$this->tableName}`
                        ( " . join(', ', $insertFields) . " )
                        VALUES
                        ( " . join(',', array_fill(0, count($insertFields), '?')) . " )
                    ");
                    try {
                        foreach ($infos as &$info) {
                            if (! $insert->execute(array_values($info))) {
                                $error = $writeMySQL->errorInfo();
                                if ($writeMySQL->inTransaction()) {
                                    $writeMySQL->rollBack();
                                }
                                throw new Exception("Insert multi failed - {$error[0]}: {$error[2]}");
                            }

                            $info[$this->primaryKey] = $writeMySQL->lastInsertId();
                        }
                        unset($info);
                    } catch (PDOException $e) {
                        $error = $writeMySQL->errorInfo();
                        if ($writeMySQL->inTransaction()) {
                            $writeMySQL->rollBack();
                        }
                        throw new Exception("Insert multi failed - {$error[0]}: {$error[2]}");
                    }

                    if (! $writeMySQL->commit()) {
                        $error = $writeMySQL->errorInfo();
                        if ($writeMySQL->inTransaction()) {
                            $writeMySQL->rollBack();
                        }
                        throw new Exception("Insert multi failed - {$error[0]}: {$error[2]}");
                    }
                } else {
                    /**
                     * This might explode if $keyMatch was a lie
                     */
                    $insertVals = new SplFixedArray(count($insertFields) * count($infos));
                    foreach ($infos as $info) {
                        foreach ($info as $v) {
                            $insertVals[] = $v;
                        }
                    }

                    try {
                        if (! $writeMySQL->prepare("
                            INSERT INTO
                            `{$this->tableName}`
                            ( " . join(', ', $insertFields) . " )
                            VALUES
                            " . join(', ', array_fill(0, count($infos), '( ' . join(',', array_fill(0, count($insertFields), '?')) . ')')) . "
                        ")->execute($insertVals)) {
                            $error = $writeMySQL->errorInfo();
                            throw new Exception("Insert multi failed - {$error[0]}: {$error[2]}");
                        }
                    } catch (PDOException $e) {
                        $error = $writeMySQL->errorInfo();
                        throw new Exception("Insert multi failed - {$error[0]}: {$error[2]}");
                    }
                }
            } else {
                $writeMySQL->beginTransaction();

                foreach ($infos as &$info) {
                    $insertFields = [];
                    foreach (array_keys($info) as $key) {
                        $insertFields[] = "`{$key}`";
                    }

                    try {
                        if (! $writeMySQL->prepare("
                            INSERT INTO
                            `{$this->tableName}`
                            ( " . join(', ', $insertFields) . " )
                            VALUES
                            ( " . join(',', \array_fill(0, count($info), '?')) . " )
                        ")->execute(array_values($info))) {
                            $error = $writeMySQL->errorInfo();
                            if ($writeMySQL->inTransaction()) {
                                $writeMySQL->rollBack();
                            }
                            throw new Exception("Insert multi failed - {$error[0]}: {$error[2]}");
                        }
                    } catch (PDOException $e) {
                        $error = $writeMySQL->errorInfo();
                        if ($writeMySQL->inTransaction()) {
                            $writeMySQL->rollBack();
                        }
                        throw new Exception("Insert multi failed - {$error[0]}: {$error[2]}");
                    }

                    if ($this->autoIncrement) {
                        $info[$this->primaryKey] = $writeMySQL->lastInsertId();
                    }
                }
                unset($info);

                if (! $writeMySQL->commit()) {
                    $error = $writeMySQL->errorInfo();
                    if ($writeMySQL->inTransaction()) {
                        $writeMySQL->rollBack();
                    }
                    throw new Exception("Insert multi failed - {$error[0]}: {$error[2]}");
                }
            }
            
            if ($reloadFromSource) {
                /**
                 *  Use master to avoid race condition
                 */
                $infosRs = $writeMySQL->prepare("
                    SELECT *
                    FROM `{$this->tableName}`
                    WHERE `{$this->primaryKey}` IN (" . join(',', array_fill(0, count($infos), '?')) . ")
                ");
                $pks = [];
                foreach ($infos as $k => $info) {
                    $pks[$k] = $info[$this->primaryKey];
                }

                $infosRs->execute($pks);
    
                $reloadedInfos = [];
                foreach ($infosRs->fetchAll() as $info) {
                    $k = array_search($info[$this->primaryKey], $pks);
                    if ($k !== false) {
                        $reloadedInfos[$k] = $info;
                    }
                }
    
                return $reloadedInfos;
            }            

            return $infos;
        }

        /**
         * Update a record
         *
         * @param Entity\Record\Model $model
         * @param array               $info
         * @param int                 $ttl
         * @param bool                $reloadFromSource
         *
         * @return array|bool
         * @throws Exception
         */
        public function update(Entity\Record\Model $model, array $info, $ttl, $reloadFromSource) {

            if ($ttl) {
                throw new Exception('MySQL does not support TTL');
            }

            $update_fields = [];
            foreach (array_keys($info) as $field) {
                $update_fields[] = "`{$field}` = :{$field}";
            }

            $info[$this->primaryKey] = $model->{$this->primaryKey};
            $writeMySQL              = $this->writeMySQLService->get();

            try {
                if (! $writeMySQL->prepare("
                    UPDATE `{$this->tableName}`
                    SET " . join(", \n", $update_fields) . "
                    WHERE `{$this->primaryKey}` = :{$this->primaryKey}
                ")->execute($info)) {
                    $error = $writeMySQL->errorInfo();
                    throw new Exception("Update failed - {$error[0]}: {$error[2]}");
                }
            } catch (PDOException $e) {
                $error = $writeMySQL->errorInfo();
                throw new Exception("Update failed - {$error[0]}: {$error[2]}");
            }

            if ($reloadFromSource) {
                /**
                 * Use master to avoid race condition
                 */
                $infosRs = $writeMySQL->prepare("
                    SELECT *
                    FROM `{$this->tableName}`
                    WHERE `{$this->primaryKey}` = ?
                ");

                $infosRs->execute([
                    array_key_exists($this->primaryKey, $info) ? $info[$this->primaryKey] : $model->{$this->primaryKey},
                ]);

                return $infosRs->fetch();
            }

            return true;
        }

        /**
         * Delete a record
         *
         * @param Entity\Record\Model $model
         *
         * @throws Exception
         */
        public function delete(Entity\Record\Model $model) {
            $writeMySQL = $this->writeMySQLService->get();
            $delete = $writeMySQL->prepare("
                DELETE FROM `{$this->tableName}`
                WHERE `{$this->primaryKey}` = ?
            ");

            try {
                if (! $delete->execute([
                    $model->{$this->primaryKey},
                ])) {
                    $error = $writeMySQL->errorInfo();
                    throw new Exception("Delete failed - {$error[0]}: {$error[2]}");
                }
            } catch (PDOException $e) {
                $error = $writeMySQL->errorInfo();
                throw new Exception("Delete failed - {$error[0]}: {$error[2]}");
            }
        }

        /**
         * Delete multiple records
         *
         * @param Entity\Record\Collection $collection
         *
         * @throws Exception
         */
        public function deleteMulti(Entity\Record\Collection $collection) {
            $writeMySQL = $this->writeMySQLService->get();
            $delete = $writeMySQL->prepare("
                DELETE FROM `{$this->tableName}`
                WHERE `{$this->primaryKey}` IN (" . join(',', array_fill(0, count($collection), '?')) . ")
            ");

            try {
                if (! $delete->execute(
                    array_values($collection->field($this->primaryKey))
                )) {
                    $error = $writeMySQL->errorInfo();
                    throw new Exception("Delete multi failed - {$error[0]}: {$error[2]}");
                }
            } catch (PDOException $e) {
                $error = $writeMySQL->errorInfo();
                throw new Exception("Delete multi failed - {$error[0]}: {$error[2]}");
            }
        }
    }