<?php

    namespace Neoform\Entity\Repo\RecordSource\Driver;

    use Neoform\Entity\Record;
    use Neoform\Entity\Repo\Exception;
    use Neoform\Entity;
    use Neoform\Sql;
    use Neoform;

    use PDO;
    use PDOException;
    use SplFixedArray;

    /**
     * Postgres Record\Dao driver
     */
    class PostgreSQL implements Neoform\Entity\Repo\RecordSource\Driver {

        /**
         * @var array
         */
        protected $bindingConversions = [
            Record\Dao::TYPE_STRING  => PDO::PARAM_STR,
            Record\Dao::TYPE_INTEGER => PDO::PARAM_INT,
            Record\Dao::TYPE_BINARY  => PDO::PARAM_LOB,
            Record\Dao::TYPE_FLOAT   => PDO::PARAM_STR,
            Record\Dao::TYPE_DECIMAL => PDO::PARAM_STR,
            Record\Dao::TYPE_BOOL    => PDO::PARAM_BOOL,
        ];

        /**
         * @var Sql\Pdo
         */
        protected $readPostgreSQLService;

        /**
         * @var Sql\Pdo
         */
        protected $writePostgreSQLService;

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
         * @var Entity\Record\Dao
         */
        protected $dao;

        /**
         * @param Record\Dao $dao
         * @param Sql        $readPostgreSQLService
         * @param Sql        $writePostgreSQLService
         */
        public function __construct(Entity\Record\Dao $dao, Sql $readPostgreSQLService, Sql $writePostgreSQLService) {
            $this->readPostgreSQLService  = $readPostgreSQLService;
            $this->writePostgreSQLService = $writePostgreSQLService;

            $this->primaryKey    = $dao::PRIMARY_KEY;
            $this->autoIncrement = $dao::AUTOINCREMENT;

            if (strpos($dao::TABLE, '.') !== false) {
                $table = explode('.', $dao::TABLE);
                $this->tableName = "{$table[0]}\".\"{$table[1]}";
            } else {
                $this->tableName = $dao::TABLE;
            }
            
            $this->dao = $dao;
        }

        /**
         * Get full record by primary key
         *
         * @param int|string|null $pk
         *
         * @return mixed
         */
        public function record($pk) {

            $info = $this->readPostgreSQLService->get()->prepare("
                SELECT *
                FROM \"{$this->tableName}\"
                WHERE \"{$this->primaryKey}\" = ?
            ");

            $info->bindValue(1, $pk, $this->bindingConversions[$this->dao->fieldBinding($this->primaryKey)]);
            $info->execute();

            if ($info = $info->fetch()) {
                Sql\Pdo::unbinary($info);
                return $info;
            }
        }

        /**
         * Get full records by primary key
         *
         * @param array $pks
         *
         * @return array
         */
        public function records(array $pks) {

            $infos_rs = $this->readPostgreSQLService->get()->prepare("
                SELECT *
                FROM \"{$this->tableName}\"
                WHERE \"{$this->primaryKey}\" IN (" . join(',', array_fill(0, count($pks), '?')) . ")
            ");

            $pdoBinding = $this->bindingConversions[$this->dao->fieldBinding($this->primaryKey)];
            foreach (array_values($pks) as $i => $pk) {
                $infos_rs->bindValue($i + 1, $pk, $pdoBinding);
            }
            $infos_rs->execute();

            $infos = [];
            foreach ($infos_rs->fetchAll() as $info) {
                $k = array_search($info[$this->primaryKey], $pks);
                if ($k !== false) {
                    $infos[$k] = $info;
                }
            }

            Sql\Pdo::unbinary($infos);

            return $infos;
        }

        /**
         * Get a count based on key inputs
         *
         * @param array $fieldVals
         *
         * @return integer
         */
        public function count(array $fieldVals=null) {
            $where = [];
            $vals  = [];

            if ($fieldVals) {
                foreach ($fieldVals as $k => $v) {
                    if ($v === null) {
                        $where[] = "\"{$k}\" IS NULL";
                    } else {
                        $vals[]  = $v;
                        $where[] = "\"{$k}\" = ?";
                    }
                }
            }

            $rs = $this->readPostgreSQLService->get()->prepare("
                SELECT COUNT(*) \"num\"
                FROM \"{$this->tableName}\"
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
         * @return array
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
                        $where[] = "\"{$field}\" IS NULL";
                    } else {
                        $vals[]  = $val;
                        $where[] = "\"{$field}\" = ?";
                    }
                }

                $queries[] = "(
                    SELECT COUNT(*) \"num\", ? k
                    FROM \"{$this->tableName}\"
                    " . ($where ? " WHERE " . join(" AND ", $where) : '') . "
                )";
            }

            $rs = $this->readPostgreSQLService->get()->prepare(join(' UNION ALL ', $queries));
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
                        $where[] = "\"{$field}\" IS NULL";
                    } else {
                        $vals[$field] = $val;
                        $where[]      = "\"{$field}\" = ?";
                    }
                }
            }

            $info = $this->readPostgreSQLService->get()->prepare("
                SELECT *
                FROM \"{$this->tableName}\"
                " . ($where ? " WHERE " . join(" AND ", $where) : "") . "
                ORDER BY \"{$this->primaryKey}\" ASC
            ");

            $bindings = $this->dao->fieldBindings();

            // do NOT remove this reference, it will break the bindParam() function
            foreach ($vals as $k => &$v) {
                $info->bindParam($k, $v, $this->bindingConversions[$bindings[$k]]);
            }
            unset($v);

            $info->execute();

            $infos = array_column($info->fetchAll(), null, $this->primaryKey);
            Sql\Pdo::unbinary($infos);

            return $infos;
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
                        $where[] = "\"{$field}\" IS NULL";
                    } else {
                        $vals[$field] = $val;
                        $where[]      = "\"{$field}\" = ?";
                    }
                }
            }

            $rs = $this->readPostgreSQLService->get()->prepare("
                SELECT \"{$this->primaryKey}\"
                FROM \"{$this->tableName}\"
                " . ($where ? " WHERE " . join(" AND ", $where) : "") . "
            ");

            $bindings = $this->dao->fieldBindings();

            // do NOT remove this reference, it will break the bindParam() function
            foreach ($vals as $k => &$v) {
                $rs->bindParam($k, $v, $this->bindingConversions[$bindings[$k]]);
            }
            unset($v);

            $rs->execute();

            $rs = $rs->fetchAll();

            $pks = $rs->fetchAll(PDO::FETCH_COLUMN, 0);
            Sql\Pdo::unbinary($pks);

            return $pks;
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
                SELECT \"{$this->primaryKey}\", ? \"__k__\"
                FROM \"{$this->tableName}\"
            ";
            foreach ($fieldValsArr as $k => $fieldVals) {
                $where      = [];
                $return[$k] = [];
                $vals       = $k;

                foreach ($fieldVals as $field => $val) {
                    if ($val === null) {
                        $where[] = "\"{$field}\" IS NULL";
                    } else {
                        $vals[]  = $val;
                        $where[] = "\"{$field}\" = ?";
                    }
                }
                $queries[] = "(
                    {$query}
                    WHERE " . join(" AND ", $where) . "
                )";
            }

            $rs = $this->readPostgreSQLService->get()->prepare(
                join(' UNION ALL ', $queries)
            );

            $rs->execute($vals);

            foreach ($rs->fetchAll() as $row) {
                $return[$row['__k__']][] = $row[$this->primaryKey];
            }

            Sql\Pdo::unbinary($return);

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
         * @return mixed
         */
        public function byFieldsOffset(array $fieldVals, array $orderBy, $offset, $limit) {
            $where = [];
            $vals  = [];

            if ($fieldVals) {
                foreach ($fieldVals as $field => $val) {
                    if ($val === null) {
                        $where[] = "\"{$field}\" IS NULL";
                    } else {
                        $vals[]  = $val;
                        $where[] = "\"{$field}\" = ?";
                    }
                }
            }

            // LIMIT
            if ($limit) {
                $limit = "LIMIT {$limit}";
            } else {
                $limit = '';
            }

            // OFFSET
            if ($offset !== null) {
                $offset = "OFFSET {$offset}";
            } else {
                $offset = '';
            }

            $order = [];
            foreach ($orderBy as $field => $sortDirection) {
                $order[] = "\"{$field}\" " . (Entity\Dao::SORT_DESC === $sortDirection ? 'DESC' : 'ASC');
            }
            $orderBy = join(', ', $order);

            $rs = $this->readPostgreSQLService->get()->prepare("
                SELECT \"{$this->primaryKey}\"
                FROM \"{$this->tableName}\"
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
            $limit = $limit ? "LIMIT {$limit}" : '';

            // OFFSET
            $offset = $offset !== null ? "OFFSET {$offset}" : '';

            $order = [];
            foreach ($orderBy as $field => $sortDirection) {
                $order[] = "\"{$field}\" " . (Entity\Dao::SORT_DESC === $sortDirection ? 'DESC' : 'ASC');
            }
            $orderBy = join(', ', $order);

            $query = "
                SELECT \"{$this->primaryKey}\", ? \"__k__\"
                FROM \"{$this->tableName}\"
            ";

            foreach ($fieldValsArr as $k => $fieldVals) {
                $where      = [];
                $return[$k] = [];
                $vals[]     = $k;

                foreach ($fieldVals as $field => $val) {
                    if ($val === null) {
                        $where[] = "\"{$field}\" IS NULL";
                    } else {
                        $vals[]  = $val;
                        $where[] = "\"{$field}\" = ?";
                    }
                }

                $queries[] = "(
                    {$query}
                    WHERE " . join(" AND ", $where) . "
                    ORDER BY {$orderBy}
                    {$limit} {$offset}
                )";
            }

            $rs = $this->readPostgreSQLService->get()->prepare(
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

            if ($replace) {
                throw new Exception('PostgreSQL does not support REPLACE INTO.');
            }

            if ($ttl) {
                throw new Exception('PostgreSQL does not support TTL');
            }

            $insertFields = [];
            foreach (array_keys($info) as $key) {
                $insertFields[] = "\"{$key}\"";
            }

            if ($reloadFromSource) {
                $returning = "RETURNING *";
            } else if ($this->autoIncrement) {
                $returning = "RETURNING \"{$this->primaryKey}\"";
            } else {
                $returning = '';
            }

            $insert = $this->writePostgreSQLService->get()->prepare("
                INSERT INTO \"{$this->tableName}\"
                ( " . join(', ', $insertFields) . " )
                VALUES
                ( " . join(',', array_fill(0, count($insertFields), '?')) . " )
                {$returning}
            ");

            $bindings = $this->dao->fieldBindings();

            // bindParam() expects a reference, not a value, do not remove the &
            $i = 1;
            foreach ($info as $k => &$v) {
                $insert->bindParam($i++, $v, $this->bindingConversions[$bindings[$k]]);
            }
            unset($v);

            try {
                if (! $insert->execute()) {
                    $error = $this->writePostgreSQLService->get()->errorInfo();
                    throw new Exception("Insert failed - {$error[0]}: {$error[2]}");
                }
            } catch (PDOException $e) {
                $error = $this->writePostgreSQLService->get()->errorInfo();
                throw new Exception("Insert failed - {$error[0]}: {$error[2]}", 0, $e);
            }

            if ($reloadFromSource) {
                $info = $insert->fetch();
                Sql\Pdo::unbinary($info);
            } else if ($this->autoIncrement) {
                $info[$this->primaryKey] = $insert->fetch()[$this->primaryKey];
                Sql\Pdo::unbinary($info[$this->primaryKey]);
            }

            return $info;
        }

        /**
         * Insert multiple records
         *
         * @param array $infos
         * @param bool  $keysMatch
         * @param bool  $replace
         * @param int   $ttl
         * @param bool  $reloadFromSource
         *
         * @return array
         * @throws Exception
         */
        public function insertMulti(array $infos, $keysMatch, $replace, $ttl, $reloadFromSource) {

            if ($replace) {
                throw new Exception('PostgreSQL does not support REPLACE INTO.');
            }

            if ($ttl) {
                throw new Exception('PostgreSQL does not support TTL');
            }

            if ($keysMatch) {
                $insertFields = [];

                foreach (array_keys(reset($infos)) as $k) {
                    $insertFields[] = "\"{$k}\"";
                }

                // If the table is auto increment (or reload from source), we cannot lump all inserts into one query
                // since we need the returned IDs for cache-busting and to return a model
                if ($this->autoIncrement || $reloadFromSource) {

                    $returning = $reloadFromSource ? "RETURNING *" : "RETURNING \"{$this->primaryKey}\"";

                    $sql = $this->writePostgreSQLService->get();
                    $sql->beginTransaction();

                    $insert = $sql->prepare("
                        INSERT INTO \"{$this->tableName}\"
                        ( " . join(', ', $insertFields) . " )
                        VALUES
                        ( " . join(',', array_fill(0, count($insertFields), '?')) . " )
                        {$returning}
                    ");

                    $bindings = $this->dao->fieldBindings();

                    try {
                        foreach ($infos as $info) {

                            // bindParam() expects a reference, not a value, do not remove the &
                            $i = 1;
                            foreach ($info as $k => &$v) {
                                $insert->bindParam($i++, $v, $this->bindingConversions[$bindings[$k]]);
                            }
                            unset($v);

                            if (! $insert->execute()) {
                                $error = $sql->errorInfo();
                                if ($sql->inTransaction()) {
                                    $sql->rollBack();
                                }
                                throw new Exception("Insert multi failed - {$error[0]}: {$error[2]}");
                            }

                            if ($reloadFromSource) {
                                $info = $insert->fetch();
                                Sql\Pdo::unbinary($info);
                            } else if ($this->autoIncrement) {
                                $info[$this->primaryKey] = $insert->fetch()[$this->primaryKey];
                                Sql\Pdo::unbinary($info[$this->primaryKey]);
                            }
                        }
                    } catch (PDOException $e) {
                        $error = $sql->errorInfo();
                        if ($sql->inTransaction()) {
                            $sql->rollBack();
                        }
                        throw new Exception("Insert multi failed - {$error[0]}: {$error[2]}", 0, $e);
                    }

                    if (! $sql->commit()) {
                        $error = $sql->errorInfo();
                        if ($sql->inTransaction()) {
                            $sql->rollBack();
                        }
                        throw new Exception("Insert multi failed - {$error[0]}: {$error[2]}");
                    }
                } else {
                    // this might explode if $keysMatch was a lie
                    $insertVals = new SplFixedArray(count($insertFields) * count($infos));
                    foreach ($infos as $info) {
                        foreach ($info as $v) {
                            $insertVals[] = $v;
                        }
                    }

                    $inserts = $this->writePostgreSQLService->get()->prepare("
                        INSERT INTO \"{$this->tableName}\"
                        ( " . join(', ', $insertFields) . " )
                        VALUES
                        " . join(', ', array_fill(0, count($infos), '( ' . join(',', array_fill(0, count($insertFields), '?')) . ')')) . "
                    ");

                    $bindings = $this->dao->fieldBindings();

                    // bindParam() expects a reference, not a value, do not remove the &
                    $i = 1;
                    foreach ($insertVals as $k => &$v) {
                        $inserts->bindParam($i++, $v, $this->bindingConversions[$bindings[$k]]);
                    }
                    unset($v);

                    try {
                        if (! $inserts->execute()) {
                            $error = $this->writePostgreSQLService->get()->errorInfo();
                            throw new Exception("Insert multi failed - {$error[0]}: {$error[2]}");
                        }
                    } catch (PDOException $e) {
                        $error = $this->writePostgreSQLService->get()->errorInfo();
                        throw new Exception("Insert multi failed - {$error[0]}: {$error[2]}", 0, $e);
                    }
                }
            } else {
                $sql = $this->writePostgreSQLService->get();

                $sql->beginTransaction();

                $bindings = $this->dao->fieldBindings();

                if ($reloadFromSource) {
                    $returning = "RETURNING *";
                } else if ($this->autoIncrement) {
                    $returning = "RETURNING \"{$this->primaryKey}\"";
                } else {
                    $returning = '';
                }

                try {
                    foreach ($infos as &$info) {
                        $insertFields = [];

                        foreach (array_keys($info) as $key) {
                            $insertFields[] = "\"$key\"";
                        }

                        $insert = $sql->prepare("
                            INSERT INTO \"{$this->tableName}\"
                            ( " . join(', ', $insertFields) . " )
                            VALUES
                            ( " . join(',', array_fill(0, count($info), '?')) . " )
                            {$returning}
                        ");

                        // bindParam() expects a reference, not a value, do not remove the &
                        $i = 1;
                        foreach ($info as $k => &$v) {
                            $insert->bindParam($i++, $v, $this->bindingConversions[$bindings[$k]]);
                        }
                        unset($v);

                        if (! $insert->execute()) {
                            $error = $sql->errorInfo();
                            if ($sql->inTransaction()) {
                                $sql->rollBack();
                            }
                            throw new Exception("Insert multi failed - {$error[0]}: {$error[2]}");
                        }

                        if ($reloadFromSource) {
                            $info = $insert->fetch();
                            Sql\Pdo::unbinary($info);
                        } else if ($this->autoIncrement) {
                            $info[$this->primaryKey] = $insert->fetch()[$this->primaryKey];
                            Sql\Pdo::unbinary($info[$this->primaryKey]);
                        }
                    }
                    unset($info);

                } catch (PDOException $e) {
                    $error = $sql->errorInfo();
                    if ($sql->inTransaction()) {
                        $sql->rollBack();
                    }
                    throw new Exception("Insert multi failed - {$error[0]}: {$error[2]}", 0, $e);
                }

                if (! $sql->commit()) {
                    $error = $sql->errorInfo();
                    if ($sql->inTransaction()) {
                        $sql->rollBack();
                    }
                    throw new Exception("Insert multi failed - {$error[0]}: {$error[2]}");
                }
            }

            return $infos;
        }

        /**
         * Update a record
         *
         * @param Record\Model $model
         * @param array        $info
         * @param int          $ttl
         * @param bool         $reloadFromSource
         *
         * @return array|bool
         * @throws Exception
         */
        public function update(Record\Model $model, array $info, $ttl, $reloadFromSource) {

            if ($ttl) {
                throw new Exception('PostgreSQL does not support TTL');
            }

            $updateFields = [];
            foreach (array_keys($info) as $key) {
                $updateFields[] = "\"{$key}\" = :{$key}";
            }
            $update = $this->writePostgreSQLService->get()->prepare("
                UPDATE \"{$this->tableName}\"
                SET " . \implode(", \n", $updateFields) . "
                WHERE \"{$this->primaryKey}\" = :{$this->primaryKey}
                " . ($reloadFromSource ? 'RETURNING *' : '') . "
            ");

            $info[$this->primaryKey] = $model->{$this->primaryKey};

            $bindings = $this->dao->fieldBindings();

            // bindParam() expects a reference, not a value, do not remove the &
            foreach ($info as $k => &$v) {
                $update->bindParam($k, $v, $this->bindingConversions[$bindings[$k]]);
            }
            unset($v);

            try {
                if (! $update->execute()) {
                    $error = $this->writePostgreSQLService->get()->errorInfo();
                    throw new Exception("Update failed - {$error[0]}: {$error[2]}");
                }
            } catch (PDOException $e) {
                $error = $this->writePostgreSQLService->get()->errorInfo();
                throw new Exception("Update failed - {$error[0]}: {$error[2]}", 0, $e);
            }

            if ($reloadFromSource) {
                $info = $update->fetch();
                Sql\Pdo::unbinary($info);
                return $info;
            }

            return true;
        }

        /**
         * Delete a record
         *
         * @param Record\Model $model
         *
         * @throws Exception
         */
        public function delete(Record\Model $model) {
            $delete = $this->writePostgreSQLService->get()->prepare("
                DELETE FROM \"{$this->tableName}\"
                WHERE \"{$this->primaryKey}\" = ?
            ");
            $delete->bindValue(1, $model->{$this->primaryKey}, $this->bindingConversions[$this->dao->fieldBinding($this->primaryKey)]);

            try {
                if (! $delete->execute()) {
                    $error = $this->writePostgreSQLService->get()->errorInfo();
                    throw new Exception("Delete failed - {$error[0]}: {$error[2]}");
                }
            } catch (PDOException $e) {
                $error = $this->writePostgreSQLService->get()->errorInfo();
                throw new Exception("Delete failed - {$error[0]}: {$error[2]}", 0, $e);
            }
        }

        /**
         * Delete multiple records
         *
         * @param Record\Collection $collection
         *
         * @throws Exception
         */
        public function deleteMulti(Record\Collection $collection) {
            $pks = $collection->field($this->primaryKey);
            $delete = $this->writePostgreSQLService->get()->prepare("
                DELETE FROM \"{$this->tableName}\"
                WHERE \"{$this->primaryKey}\" IN (" . join(',', array_fill(0, count($collection), '?')) . ")
            ");

            $pdoBinding = $this->bindingConversions[$this->dao->fieldBinding($this->primaryKey)];
            $i = 1;
            foreach ($pks as $pk) {
                $delete->bindValue($i++, $pk, $pdoBinding);
            }

            try {
                if (! $delete->execute()) {
                    $error = $this->writePostgreSQLService->get()->errorInfo();
                    throw new Exception("Deletes multi failed - {$error[0]}: {$error[2]}");
                }
            } catch (PDOException $e) {
                $error = $this->writePostgreSQLService->get()->errorInfo();
                throw new Exception("Delete multi failed - {$error[0]}: {$error[2]}", 0, $e);
            }
        }
    }