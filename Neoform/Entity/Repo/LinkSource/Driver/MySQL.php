<?php

    namespace Neoform\Entity\Repo\LinkSource\Driver;

    use Neoform\Entity\Link;
    use Neoform\Entity\Record;
    use Neoform\Entity;
    use Neoform\Sql;
    use Neoform;
    use PDO;
    use PDOException;

    class MySQL implements Neoform\Entity\Repo\LinkSource\Driver {

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
         * @param Entity\Link\Dao $dao
         * @param Sql        $readMySQLService
         * @param Sql        $writeMySQLService
         */
        public function __construct(Entity\Link\Dao $dao, Sql $readMySQLService, Sql $writeMySQLService) {
            $this->readMySQLService  = $readMySQLService;
            $this->writeMySQLService = $writeMySQLService;
            $this->tableName         = $this->tableName($dao);
        }

        /**
         * @param Entity\Dao $dao
         *
         * @return string
         */
        protected function tableName(Entity\Dao $dao) {
            if (strpos($dao::TABLE, '.') !== false) {
                $table = explode('.', $dao::TABLE);
                return "{$table[0]}`.`{$table[1]}";
            }

            return $dao::TABLE;
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
                foreach ($fieldVals as $k => $v) {
                    if ($v === null) {
                        $where[] = "`{$k}` IS NULL";
                    } else {
                        $vals[]  = $v;
                        $where[] = "`{$k}` = ?";
                    }
                }
            }

            $rs = $this->readMySQLService->get()->prepare("
                SELECT " . join(',', $selectFields) . "
                FROM `{$this->tableName}`
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
         * @param array $fieldvalsArr
         *
         * @return array
         */
        public function byFieldsMulti(array $selectFields, array $fieldvalsArr) {
            $quotedSelectFields = [];
            $return               = [];
            $queries              = [];
            $vals                 = [];

            foreach ($selectFields as $field) {
                $quotedSelectFields[] = "`{$field}`";
            }

            $query = "
                SELECT " . join(',', $quotedSelectFields) . ", ? `__k__`
                FROM `{$this->tableName}`
            ";

            foreach ($fieldvalsArr as $k => $fieldVals) {
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

            if (count($selectFields) === 1) {
                $field = reset($selectFields);
                foreach ($rs->fetchAll() as $row) {
                    $return[$row['__k__']][] = $row[$field];
                }
            } else {
                // If the selected field count is different than the requested fields, only return the requested fields
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
         */
        public function byFieldsLimit($localField, Record\Dao $foreignDao, array $fieldVals, array $orderBy, $offset, $limit) {

            // FK Relation
            $quotedForeignTable = $this->tableName($foreignDao);
            $foreignPk           = $foreignDao::PRIMARY_KEY;

            // WHERE
            $where = [];
            $vals  = [];

            if ($fieldVals) {
                foreach ($fieldVals as $field => $val) {
                    if ($val === null) {
                        $where[] = "`{$this->tableName}`.`{$field}` IS NULL";
                    } else {
                        $vals[]  = $val;
                        $where[] = "`{$this->tableName}`.`{$field}` = ?";
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

            // ORDER BY
            $order = [];
            foreach ($orderBy as $field => $sort_direction) {
                $order[] = "`{$quotedForeignTable}`.`{$field}` " . (Entity\Dao::SORT_DESC === $sort_direction ? 'DESC' : 'ASC');
            }
            $orderBy = join(', ', $order);

            $rs = $this->readMySQLService->get()->prepare("
                SELECT `{$quotedForeignTable}`.`{$foreignPk}`
                FROM `{$this->tableName}`
                INNER JOIN `{$quotedForeignTable}`
                ON `{$quotedForeignTable}`.`{$foreignPk}` = `{$this->tableName}`.`{$localField}`
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
         * @param array      $fieldvalsArr
         * @param array      $orderBy
         * @param integer    $limit
         * @param integer    $offset
         *
         * @return array
         */
        public function byFieldsLimitMulti($localField, Record\Dao $foreignDao, array $fieldvalsArr, array $orderBy, $offset, $limit) {
            $return  = [];
            $vals    = [];
            $queries = [];

            // FK Relation
            $quotedForeignTable = $this->tableName($foreignDao);
            $foreignPk           = $foreignDao::PRIMARY_KEY;

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
                $order[] = "`{$quotedForeignTable}`.`{$field}` " . (Entity\Dao::SORT_DESC === $sort_direction ? 'DESC' : 'ASC');
            }
            $orderBy = join(', ', $order);

            // QUERIES
            $query = "
                SELECT `{$localField}`, ? `__k__`
                FROM `{$this->tableName}`
                INNER JOIN `{$quotedForeignTable}`
                ON `{$quotedForeignTable}`.`{$foreignPk}` = `{$this->tableName}`.`{$localField}`
            ";
            foreach ($fieldvalsArr as $k => $fieldVals) {
                $where      = [];
                $return[$k] = [];
                $vals[]     = $k;

                foreach ($fieldVals as $field => $val) {
                    if ($val === null) {
                        $where[] = "`{$this->tableName}`.`{$field}` IS NULL";
                    } else {
                        $vals[]  = $val;
                        $where[] = "`{$this->tableName}`.`{$field}` = ?";
                    }
                }
                $queries[] = "(
                    {$query}
                    WHERE" . join(" AND ", $where) . "
                    ORDER BY {$orderBy}
                    {$limit} {$offset}
                )";
            }

            $rs = $this->readMySQLService->get()->prepare(
                join(' UNION ALL ', $queries)
            );
            $rs->execute($vals);

            foreach ($rs->fetchAll() as $row) {
                $return[$row['__k__']][] = $row[$localField];
            }

            return $return;
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
         * @param array $fieldvalsArr
         *
         * @return int[]
         */
        public function countMulti(array $fieldvalsArr) {
            $queries = [];
            $vals    = [];
            $counts  = [];

            foreach ($fieldvalsArr as $k => $fieldVals) {
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
                    SELECT COUNT(0) `num`, ? `k`
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
         * Insert a link
         *
         * @param array $info
         * @param bool  $replace
         *
         * @return array
         * @throws Neoform\Entity\Repo\Exception
         */
        public function insert(array $info, $replace) {

            $insert_fields = [];
            foreach ($info as $k => $v) {
                $insert_fields[] = "`{$k}`";
            }

            $insert = $this->writeMySQLService->get()->prepare("
                " . ($replace ? 'REPLACE' : 'INSERT') . " INTO
                `{$this->tableName}`
                ( " . join(', ', $insert_fields) . " )
                VALUES
                ( " . join(',', array_fill(0, count($info), '?')) . " )
            ");

            try {
                if (! $insert->execute(array_values($info))) {
                    $error = $this->writeMySQLService->get()->errorInfo();
                    throw new Neoform\Entity\Repo\Exception("Insert failed - {$error[0]}: {$error[2]}");
                }
            } catch (PDOException $e) {
                $error = $this->writeMySQLService->get()->errorInfo();
                throw new Neoform\Entity\Repo\Exception("Insert failed - {$error[0]}: {$error[2]}");
            }

            return $info;
        }

        /**
         * Insert multiple links
         *
         * @param array    $infos
         * @param bool     $replace
         *
         * @return array
         * @throws Neoform\Entity\Repo\Exception
         */
        public function insertMulti(array $infos, $replace) {
            $insert_fields = [];
            $info          = current($infos);
            $sql           = $this->writeMySQLService->get();
            $multi         = count($infos) > 1;


            if ($multi) {
                $sql->beginTransaction();
            }

            foreach ($info as $k => $v) {
                $insert_fields[] = "`{$k}`";
            }

            $insert = $sql->prepare("
                " . ($replace ? 'REPLACE' : 'INSERT') . " INTO `{$this->tableName}`
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
                        throw new Neoform\Entity\Repo\Exception("Insert multi failed - {$error[0]}: {$error[2]}");
                    }
                }

                if ($multi && ! $sql->commit()) {
                    $error = $sql->errorInfo();
                    if ($sql->inTransaction()) {
                        $sql->rollBack();
                    }
                    throw new Neoform\Entity\Repo\Exception("Insert multi failed - {$error[0]}: {$error[2]}");
                }
            } catch (PDOException $e) {
                $error = $sql->errorInfo();
                if ($sql->inTransaction()) {
                    $sql->rollBack();
                }
                throw new Neoform\Entity\Repo\Exception("Insert multi failed - {$error[0]}: {$error[2]}");
            }

            return $infos;
        }

        /**
         * Update a set of links
         *
         * @param array $newInfo
         * @param array $where
         *
         * @throws Neoform\Entity\Repo\Exception
         */
        public function update(array $newInfo, array $where) {
            $vals         = [];
            $updateFields = [];

            foreach ($newInfo as $field => $val) {
                $updateFields[] = "`{$field}` = ?";
                $vals[]         = $val;
            }

            $whereFields = [];
            foreach ($where as $field => $val) {
                if ($val === null) {
                    $whereFields[] = "`{$field}` IS NULL";
                } else {
                    $vals[]        = $val;
                    $whereFields[] = "`{$field}` = ?";
                }
            }

            try {
                if (! $this->writeMySQLService->get()->prepare("
                    UPDATE `{$this->tableName}`
                    SET " . join(", \n", $updateFields) . "
                    WHERE " . join(" AND \n", $whereFields) . "
                ")->execute($vals)) {
                    $error = $this->writeMySQLService->get()->errorInfo();
                    throw new Neoform\Entity\Repo\Exception("Update failed - {$error[0]}: {$error[2]}");
                }
            } catch (PDOException $e) {
                $error = $this->writeMySQLService->get()->errorInfo();
                Neoform\Core::Debug("
                    UPDATE `{$this->tableName}`
                    SET " . join(", \n", $updateFields) . "
                    WHERE " . join(" AND \n", $whereFields) . "
                ", $vals);
                throw new Neoform\Entity\Repo\Exception("Update failed - {$error[0]}: {$error[2]}");
            }
        }

        /**
         * Delete one or more links
         *
         * @param array $fieldVals
         *
         * @throws Neoform\Entity\Repo\Exception
         */
        public function delete(array $fieldVals) {
            $where = [];
            $vals  = [];

            foreach ($fieldVals as $field => $val) {
                if ($val === null) {
                    $where[] = "`{$field}` IS NULL";
                } else {
                    $vals[]  = $val;
                    $where[] = "`{$field}` = ?";
                }
            }

            try {
                if (! $this->writeMySQLService->get()->prepare("
                    DELETE FROM `{$this->tableName}`
                    WHERE " . join(" AND ", $where) . "
                ")->execute($vals)) {
                    $error = $this->writeMySQLService->get()->errorInfo();
                    throw new Neoform\Entity\Repo\Exception("Delete failed - {$error[0]}: {$error[2]}");
                }
            } catch (PDOException $e) {
                $error = $this->writeMySQLService->get()->errorInfo();
                throw new Neoform\Entity\Repo\Exception("Delete failed - {$error[0]}: {$error[2]}");
            }
        }

        /**
         * Delete sets of links
         *
         * @param array $fieldvalsArr
         *
         * @throws Neoform\Entity\Repo\Exception
         */
        public function deleteMulti(array $fieldvalsArr) {
            $vals  = [];
            $where = [];

            foreach ($fieldvalsArr as $fieldVals) {
                $w = [];
                foreach ($fieldVals as $field => $val) {
                    if ($val === null) {
                        $w[] = "`{$field}` IS NULL";
                    } else {
                        $vals[] = $val;
                        $w[]    = "`{$field}` = ?";
                    }
                }
                $where[] = "(" . join(" AND ", $w) . ")";
            }

            try {
                if (! $this->writeMySQLService->get()->prepare("
                    DELETE FROM `{$this->tableName}`
                    WHERE " . join(" OR ", $where) . "
                ")->execute($vals)) {
                    $error = $this->writeMySQLService->get()->errorInfo();
                    throw new Neoform\Entity\Repo\Exception("Delete multi failed - {$error[0]}: {$error[2]}");
                }
            } catch (PDOException $e) {
                $error = $this->writeMySQLService->get()->errorInfo();
                throw new Neoform\Entity\Repo\Exception("Delete multi failed - {$error[0]}: {$error[2]}");
            }
        }
    }