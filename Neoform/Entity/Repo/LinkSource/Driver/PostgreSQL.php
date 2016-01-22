<?php

    namespace Neoform\Entity\Repo\LinkSource\Driver;

    use Neoform\Entity\Link;
    use Neoform\Entity\Record;
    use Neoform\Entity\Repo\Exception;
    use Neoform\Entity;
    use Neoform\Sql;
    use Neoform;
    use PDO;
    use PDOException;

    class PostgreSQL implements Neoform\Entity\Repo\LinkSource\Driver {

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
         * @param Entity\Link\Dao $dao
         * @param Sql             $readPostgreSQLService
         * @param Sql             $writePostgreSQLService
         */
        public function __construct(Entity\Link\Dao $dao, Sql $readPostgreSQLService, Sql $writePostgreSQLService) {
            $this->readPostgreSQLService  = $readPostgreSQLService;
            $this->writePostgreSQLService = $writePostgreSQLService;
            $this->tableName              = $this->tableName($dao);
        }

        /**
         * Parse the table name into a properly escaped table string
         *
         * @param Entity\Dao $dao
         *
         * @return string
         */
        protected function tableName(Entity\Dao $dao) {
            $tableName = $dao::getSourceIdentifier();
            if (strpos($tableName, '.') !== false) {
                $table = explode('.', $tableName);
                return "{$table[0]}\".\"{$table[1]}";
            }
            
            return $tableName;
        }

        /**
         * Get specific fields from a record, by keys
         *
         * @param array $selectFields
         * @param array $fieldVals
         *
         * @return array
         */
        public function byFields(array $selectFields, array $fieldVals) {
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

            $rs = $this->readPostgreSQLService->get()->prepare("
                SELECT " . join(',', $selectFields) . "
                FROM \"{$this->tableName}\"
                " . (count($where) ? "WHERE " . join(" AND ", $where) : "") . "
            ");

            $rs->execute($vals);

            if (count($selectFields) === 1) {
                return $rs->fetchAll(PDO::FETCH_COLUMN, 0);
            } else {
                return $rs->fetchAll();
            }
        }

        /**
         * Get specific fields from multiple records, by keys
         *
         * @param array $selectFields
         * @param array $fieldValsArr
         *
         * @return array
         */
        public function byFieldsMulti(array $selectFields, array $fieldValsArr) {
            $fields  = [];
            $return  = [];
            $vals    = [];
            $queries = [];

            foreach ($selectFields as $field) {
                $fields[] = "\"{$field}\"";
            }

            $query = "
                SELECT " . join(',', $fields) . ", ? \"__k__\"
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
                    WHERE " . join(' OR ', $where) . "
                )";
            }

            $rs = $this->readPostgreSQLService->get()->prepare(
                join(' UNION ALL ', $queries)
            );
            $rs->execute($vals);

            if (count($selectFields) === 1) {
                $field = reset($selectFields);
                foreach ($rs->fetchAll() as $row) {
                    $return[$row['__k__']][] = $row[$field];
                }
            } else {
                foreach ($rs->fetchAll() as $row) {
                    $return[$row['__k__']][] = array_intersect_key($row, array_keys($selectFields));
                }
            }

            return $return;
        }

        /**
         * Get specific fields from a record, by keys - joined to its related foreign table - and limited
         *
         * @param string     $localField
         * @param Record\Dao $foreignDao
         * @param array      $fieldVals
         * @param array      $orderBy
         * @param integer    $limit
         * @param integer    $offset
         *
         * @return array
         * @throws Exception
         */
        public function byFieldsLimit($localField, Record\Dao $foreignDao, array $fieldVals, array $orderBy, $offset, $limit) {

            // FK Relation
            $quotedForeignTable = $this->tableName($foreignDao);
            $foreignPk          = $foreignDao::getPrimaryKeyName();

            // WHERE
            $where = [];
            $vals  = [];

            if ($fieldVals) {
                foreach ($fieldVals as $field => $val) {
                    if ($val === null) {
                        $where[] = "\"{$this->tableName}\".\"{$field}\" IS NULL";
                    } else {
                        $vals[]  = $val;
                        $where[] = "\"{$this->tableName}\".\"{$field}\" = ?";
                    }
                }
            }

            // LIMIT
            $limit = $limit ? "LIMIT {$limit}" : '';

            // OFFSET
            $offset = $offset !== null ? "OFFSET {$offset}" : '';

            // ORDER BY
            $order = [];
            foreach ($orderBy as $field => $sort_direction) {
                $order[] = "\"{$quotedForeignTable}\".\"{$field}\" " . (Entity\Dao::SORT_DESC === $sort_direction ? 'DESC' : 'ASC');
            }
            $orderBy = join(', ', $order);

            $rs = $this->readPostgreSQLService->get()->prepare("
                SELECT \"{$quotedForeignTable}\".\"{$foreignPk}\"
                FROM \"{$this->tableName}\"
                INNER JOIN \"{$quotedForeignTable}\"
                ON \"{$quotedForeignTable}\".\"{$foreignPk}\" = \"{$this->tableName}\".\"{$localField}\"
                " . (count($where) ? "WHERE " . join(" AND ", $where) : "") . "
                ORDER BY {$orderBy}
                {$limit} {$offset}
            ");

            $rs->execute($vals);

            return $rs->fetchAll(PDO::FETCH_COLUMN, 0);
        }

        /**
         * Get specific fields from a record, by keys - joined to its related foreign table - and limited
         *
         * @param string     $localField
         * @param Record\Dao $foreignDao
         * @param array      $fieldValsArr
         * @param array      $orderBy
         * @param integer    $limit
         * @param integer    $offset
         *
         * @return array
         * @throws Exception
         */
        public function byFieldsLimitMulti($localField, Record\Dao $foreignDao, array $fieldValsArr, array $orderBy, $offset, $limit) {
            $return  = [];
            $vals    = [];
            $queries = [];

            // FK Relation
            $quotedForeignTable = $this->tableName($foreignDao);
            $foreignPk           = $foreignDao::getPrimaryKeyName();

            // LIMIT
            $limit = $limit ? "LIMIT {$limit}" : '';

            // OFFSET
            $offset = $offset !== null ? "OFFSET {$offset}" : '';

            // ORDER BY
            $order = [];
            foreach ($orderBy as $field => $sort_direction) {
                $order[] = "\"{$quotedForeignTable}\".\"{$field}\" " . (Entity\Dao::SORT_DESC === $sort_direction ? 'DESC' : 'ASC');
            }
            $orderBy = join(', ', $order);

            // QUERIES
            $query = "
                SELECT \"{$localField}\", ? \"__k__\"
                FROM \"{$this->tableName}\"
                INNER JOIN \"{$quotedForeignTable}\"
                ON \"{$quotedForeignTable}\".\"{$foreignPk}\" = \"{$this->tableName}\".\"{$localField}\"
            ";
            foreach ($fieldValsArr as $k => $fieldVals) {
                $where      = [];
                $return[$k] = [];
                $vals[]     = $k;

                foreach ($fieldVals as $field => $val) {
                    if ($val === null) {
                        $where[] = "\"{$this->tableName}\".\"{$field}\" IS NULL";
                    } else {
                        $vals[]  = $val;
                        $where[] = "\"{$this->tableName}\".\"{$field}\" = ?";
                    }
                }

                $queries[] = "(
                    {$query}
                    WHERE" . join(" AND ", $where) . "
                    ORDER BY {$orderBy}
                    {$limit} {$offset}
                )";
            }

            $rs = $this->readPostgreSQLService->get()->prepare(
                join(' UNION ALL ', $queries)
            );
            $rs->execute($vals);

            foreach ($rs->fetchAll() as $row) {
                $return[$row['__k__']][] = $row[$localField];
            }

            return $return;
        }

        /**
         * Get a count based on key inputs
         *
         * @param array $fieldVals
         *
         * @return int
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
         * Insert a link
         *
         * @param Link\Dao $self the name of the DAO
         * @param string   $pool which source engine pool to use
         * @param array    $info
         * @param bool     $replace
         *
         * @return array
         * @throws Exception
         */
        public function insert(array $info, $replace) {

            if ($replace) {
                throw new Exception('PostgreSQL does not support REPLACE INTO.');
            }

            $insert_fields = [];
            foreach ($info as $k => $v) {
                $insert_fields[] = "\"{$k}\"";
            }

            $insert = $this->writePostgreSQLService->get()->prepare("
                INSERT INTO
                \"{$this->tableName}\"
                ( " . join(', ', $insert_fields) . " )
                VALUES
                ( " . join(',', array_fill(0, count($info), '?')) . " )
            ");

            try {
                if (! $insert->execute(array_values($info))) {
                    $error = $this->writePostgreSQLService->get()->errorInfo();
                    throw new Exception("Insert failed - {$error[0]}: {$error[2]}");
                }
            } catch (PDOException $e) {
                $error = $this->writePostgreSQLService->get()->errorInfo();
                throw new Exception("Insert failed - {$error[0]}: {$error[2]}");
            }

            return $info;
        }

        /**
         * Insert multiple links
         *
         * @param array $infos
         * @param bool  $replace
         *
         * @return array
         * @throws Exception
         */
        public function insertMulti(array $infos, $replace) {

            if ($replace) {
                throw new Exception('PostgreSQL does not support REPLACE INTO.');
            }

            $insert_fields = [];
            $info          = current($infos);
            $sql           = $this->writePostgreSQLService->get();
            $multi         = count($infos) > 1;

            if ($multi) {
                $sql->beginTransaction();
            }

            foreach ($info as $k => $v) {
                $insert_fields[] = "\"{$k}\"";
            }

            $insert = $sql->prepare("
                INSERT INTO \"{$this->tableName}\"
                ( " . join(', ', $insert_fields) . " )
                VALUES
                ( " . join(',', array_fill(0, count($info), '?')) . " )
            ");

            try {
                foreach ($infos as $info) {
                    if (! $insert->execute(array_values($info))) {
                        $error = $sql->errorInfo();
                        if ($sql->inTransaction()) {
                            $sql->rollBack();
                        }
                        throw new Exception("Insert multi failed - {$error[0]}: {$error[2]}");
                    }
                }
            } catch (PDOException $e) {
                $error = $sql->errorInfo();
                if ($sql->inTransaction()) {
                    $sql->rollBack();
                }
                throw new Exception("Insert multi failed - {$error[0]}: {$error[2]}");
            }

            if ($multi && ! $sql->commit()) {
                $error = $sql->errorInfo();
                if ($sql->inTransaction()) {
                    $sql->rollBack();
                }
                throw new Exception("Insert multi failed - {$error[0]}: {$error[2]}");
            }

            return $infos;
        }

        /**
         * Update a set of links
         *
         * @param array $newInfo
         * @param array $where
         *
         * @throws Exception
         */
        public function update(array $newInfo, array $where) {
            $vals         = [];
            $updateFields = [];

            foreach ($newInfo as $k => $v) {
                $updateFields[] = "\"{$k}\" = ?";
                $vals[]          = $v;
            }

            $whereFields = [];
            foreach ($where as $k => $v) {
                if ($v === null) {
                    $whereFields[] = "\"{$k}\" IS NULL";
                } else {
                    $vals[]         = $v;
                    $whereFields[] = "\"{$k}\" = ?";
                }
            }

            try {
                if (! $this->writePostgreSQLService->get()->prepare("
                    UPDATE \"{$this->tableName}\"
                    SET " . join(", \n", $updateFields) . "
                    WHERE " . join(" AND \n", $whereFields) . "
                ")->execute($vals)) {
                    $error = $this->writePostgreSQLService->get()->errorInfo();
                    throw new Exception("Update failed - {$error[0]}: {$error[2]}");
                }
            } catch (PDOException $e) {
                $error = $this->writePostgreSQLService->get()->errorInfo();
                throw new Exception("Update failed - {$error[0]}: {$error[2]}");
            }
        }

        /**
         * Delete one or more links
         *
         * @param array $fieldVals
         *
         * @throws Exception
         */
        public function delete(array $fieldVals) {
            $where = [];
            $vals  = [];

            foreach ($fieldVals as $field => $v) {
                if ($v === null) {
                    $where[] = "\"{$field}\" IS NULL";
                } else {
                    $vals[]  = $v;
                    $where[] = "\"{$field}\" = ?";
                }
            }

            try {
                if (! $this->writePostgreSQLService->get()->prepare("
                    DELETE FROM \"{$this->tableName}\"
                    WHERE " . join(" AND ", $where) . "
                ")->execute($vals)) {
                    $error = $this->writePostgreSQLService->get()->errorInfo();
                    throw new Exception("Delete failed - {$error[0]}: {$error[2]}");
                }
            } catch (PDOException $e) {
                $error = $this->writePostgreSQLService->get()->errorInfo();
                throw new Exception("Delete failed - {$error[0]}: {$error[2]}");
            }
        }

        /**
         * Delete sets of links
         *
         * @param array $fieldValsArr
         *
         * @throws Exception
         */
        public function deleteMulti(array $fieldValsArr) {
            $vals  = [];
            $where = [];

            foreach ($fieldValsArr as $fieldVals) {
                $w = [];
                foreach ($fieldVals as $field => $v) {
                    if ($v === null) {
                        $w[] = "\"{$field}\" IS NULL";
                    } else {
                        $vals[] = $v;
                        $w[]    = "\"{$field}\" = ?";
                    }
                }
                $where[] = "(" . join(" AND ", $w) . ")";
            }

            try {
                if (! $this->writePostgreSQLService->get()->prepare("
                    DELETE FROM \"{$this->tableName}\"
                    WHERE " . join(" OR ", $where) . "
                ")->execute($vals)) {
                    $error = $this->writePostgreSQLService->get()->errorInfo();
                    throw new Exception("Delete multi failed - {$error[0]}: {$error[2]}");
                }
            } catch (PDOException $e) {
                $error = $this->writePostgreSQLService->get()->errorInfo();
                throw new Exception("Delete multi failed - {$error[0]}: {$error[2]}");
            }
        }
    }