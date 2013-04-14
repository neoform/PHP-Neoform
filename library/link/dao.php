<?php

    /**
     * Link DAO Standard database access, for accessing tables that do not have a single primary key but instead a
     * composite key (2 column PK) that link two other tables together.
     *
     * It is strongly discouraged to include any other fields in this record type, as it breaks the convention of a
     * linking table. If you must have a linking record with additional fields, use a record entity instead.
     *
     * REQUIRED: every entity class must have the following constants in it (via definition file):
     *    string TABLE       the table name in the database
     *    string ENTITY_NAME the base name of the entity (usually the same as TABLE unless different for a specific reason)
     *    string ENTITY_POOL must have a corresponding entry in the config file for the caching engine being used, eg (core::config()->memcache['pools'] = 'entities')
     */
    abstract class link_dao {

        /**
         * Get a cached link
         *
         * @access protected
         * @static
         * @final
         * @param string   $key full cache key with namespace - it's recomended that record_dao::_build_key() is used to create this key
         * @param callable $get closure function that retreieves the recordset from its origin
         * @return array   the cached recordset
         */
        final protected static function _single($key, callable $get) {
            return cache_lib::single(
                static::CACHE_ENGINE,
                $key,
                static::ENTITY_POOL,
                $get
            );
        }

        /**
         * Delete a cached record
         *
         * @access protected
         * @static
         * @final
         * @param string   $key full cache key with namespace - it's recomended that record_dao::_build_key() is used to create this key
         * @return boolean result of the cache being deleted
         */
        final protected static function _cache_delete($key) {
            return cache_lib::delete(
                static::CACHE_ENGINE,
                $key,
                static::ENTITY_POOL
            );
        }

        /**
         * Build a cache key used by the cache_lib by combining the dao class name, the cache key and the variables found in the $params
         *
         * @access public
         * @static
         * @final
         * @param string $cache_key_name word used to identify this cache entry, it should be unique to the dao class its found in
         * @param array  $params         optional - array of table keys and their values being looked up in the table
         * @param string $class          optional - closure function that retreieves the recordset from its origin
         * @return string a cache key that is unqiue to the application
         */
        final public static function _build_key($cache_key_name, array $params=[], $class=null) {
            // each key is namespaced with the name of the class
            if (count($params) === 1) {
                //base64_encode incase the value is binary or something
                return ($class ? $class : get_called_class()) . ":$cache_key_name:" . md5(base64_encode(current($params)));
            } else {
                ksort($params);
                return ($class ? $class : get_called_class()) . ":$cache_key_name:" . md5(json_encode(array_values($params)));
            }
        }


        /**
         * Gets fields that match the $keys, this gets the columns specified by $select_fields
         *
         * @access protected
         * @static
         * @final
         * @param string  $cache_key_name word used to identify this cache entry, it should be unique to the dao class its found in
         * @param array   $select_fields  array of table fields (table columns) to be selected
         * @param array   $keys           array of table keys and their values being looked up in the table
         * @return array  array of records from cache
         * @throws record_exception
         */
        final protected static function _by_fields($cache_key_name, array $select_fields, array $keys) {

            $table = static::TABLE;

            $get = function() use ($select_fields, $keys, $cache_key_name, $table) {

                $where = [];
                $vals  = [];
                $sql   = core::sql('slave');

                switch ($sql->driver()) {
                    case 'mysql':
                        if (strpos($table, '.') !== false) {
                            $table = explode('.', $table);
                            $table = "$table[0]`.`$table[1]";
                        }

                        if (count($keys)) {
                            foreach ($keys as $k => $v) {
                                if ($v === null) {
                                    $where[] = "`$k` IS NULL";
                                } else {
                                    $vals[]  = $v;
                                    $where[] = "`$k` = ?";
                                }
                            }
                        }

                        $rs = core::sql('slave')->prepare("
                            SELECT " . join(',', $select_fields) . "
                            FROM `$table`
                            " . (count($where) ? "WHERE " . join(" AND ", $where) : "") . "
                        ");

                        break;

                    case 'pgsql':
                        if (strpos($table, '.') !== false) {
                            $table = explode('.', $table);
                            $table = "$table[0]\".\"$table[1]";
                        }

                        if (count($keys)) {
                            foreach ($keys as $k => $v) {
                                if ($v === null) {
                                    $where[] = "\"$k\" IS NULL";
                                } else {
                                    $vals[]  = $v;
                                    $where[] = "\"$k\" = ?";
                                }
                            }
                        }

                        $rs = core::sql('slave')->prepare("
                            SELECT " . join(',', $select_fields) . "
                            FROM \"$table\"
                            " . (count($where) ? "WHERE " . join(" AND ", $where) : "") . "
                        ");

                        break;

                    default:
                        throw new record_exception('Unknown SQL driver');
                }

                $rs->execute($vals);

                $rs = $rs->fetchAll();
                $return = [];
                if (count($select_fields) === 1) {
                    $field = current($select_fields);
                    foreach ($rs as $row) {
                        $return[] = $row[$field];
                    }
                } else {
                    $return = $rs;
                }
                return $return;
            };

            return cache_lib::single(
                static::CACHE_ENGINE,
                self::_build_key($cache_key_name, $keys),
                static::ENTITY_POOL,
                $get
            );
        }

        /**
         * Gets the ids of more than one set of key values
         *
         * @access protected
         * @static
         * @final
         * @param string  $cache_key_name word used to identify this cache entry, it should be unique to the dao class its found in
         * @param array   $select_fields  array of table fields (table columns) to be selected
         * @param array   $keys_arr       array of arrays of table keys and their values being looked up in the table - each sub-array must have matching keys
         * @return array  ids of records from cache
         * @throws record_exception
         */
        final protected static function _by_fields_multi($cache_key_name, array $select_fields, array $keys_arr) {

            $self  = get_called_class();
            $table = static::TABLE;

            $get = function() use ($select_fields, $keys_arr, $table) {

                $key_fields     = array_keys(current($keys_arr));
                $reverse_lookup = [];
                $return         = [];
                $vals           = [];
                $where          = [];
                $sql            = core::sql('slave');

                switch ($sql->driver()) {
                    case 'mysql':
                        if (strpos($table, '.') !== false) {
                            $table = explode('.', $table);
                            $table = "$table[0]`.`$table[1]";
                        }
                        foreach ($keys_arr as $k => $keys) {
                            $w = [];
                            $reverse_lookup[join(':', $keys)] = $k;
                            $return[$k] = [];
                            foreach ($keys as $k => $v) {
                                if ($v === null) {
                                    $w[] = "`$k` IS NULL";
                                } else {
                                    $vals[] = $v;
                                    $w[] = "`$k` = ?";
                                }
                            }
                            $where[] = '(' . join(" AND ", $w) . ')';
                        }

                        $rs = $sql->prepare("
                            SELECT
                                " . join(',', $select_fields) . ",
                                CONCAT(" . join(", ':', ", $key_fields) . ") `__cache_key__`
                            FROM `$table`
                            WHERE " . join(' OR ', $where) . "
                        ");

                        break;

                    case 'pgsql':
                        if (strpos($table, '.') !== false) {
                            $table = explode('.', $table);
                            $table = "$table[0]\".\"$table[1]";
                        }
                        foreach ($keys_arr as $k => $keys) {
                            $w = [];
                            $reverse_lookup[join(':', $keys)] = $k;
                            $return[$k] = [];
                            foreach ($keys as $k => $v) {
                                if ($v === null) {
                                    $w[] = "\"$k\" IS NULL";
                                } else {
                                    $vals[] = $v;
                                    $w[]    = "\"$k\" = ?";
                                }
                            }
                            $where[] = '(' . join(" AND ", $w) . ')';
                        }

                        $rs = $sql->prepare("
                            SELECT
                                " . join(',', $select_fields) . ",
                                CONCAT(" . join(", ':', ", $key_fields) . ") \"__cache_key__\"
                            FROM \"$table\"
                            WHERE " . join(' OR ', $where) . "
                        ");

                        break;

                    default:
                        throw new record_exception('Unknown SQL driver');
                }

                $rs->execute($vals);

                $rows = $rs->fetchAll();
                if (count($select_fields) === 1) {
                    $field = current($select_fields);
                    foreach ($rows as $row) {
                        $return[$reverse_lookup[$row['__cache_key__']]][] = $row[$field];
                    }
                } else {
                    foreach ($rows as $row) {
                        $return[$reverse_lookup[$row['__cache_key__']]][] = $row;
                    }
                }

                return $return;
            };

            $key = function($fields) use ($cache_key_name, $self) {
                return record_dao::_build_key($cache_key_name, $fields, $self);
            };

            return cache_lib::multi(
                static::CACHE_ENGINE,
                $keys_arr,
                $key,
                static::ENTITY_POOL,
                $get
            );
        }

        /**
         * Inserts a linking record into the database
         *
         * @access protected
         * @static
         * @param array   $info    an associative array of into to be put info the database
         * @param boolean $replace optional - user REPLACE INTO instead of INSERT INTO
         * @return boolean result of the PDO::execute()
         * @throws record_exception
         */
        protected static function _insert(array $info, $replace=false) {
            $insert_fields = [];
            $table         = static::TABLE;
            $sql           = core::sql('slave');

            switch ($sql->driver()) {
                case 'mysql':
                    if (strpos($table, '.') !== false) {
                        $table = explode('.', $table);
                        $table = "$table[0]`.`$table[1]";
                    }

                    foreach ($info as $k => $v) {
                        $insert_fields[] = "`$k`";
                    }

                    $insert = core::sql('master')->prepare("
                        " . ($replace ? 'REPLACE' : 'INSERT IGNORE') . " INTO
                        `$table`
                        ( " . join(', ', $insert_fields) . " )
                        VALUES
                        ( " . join(',', array_fill(0, count($info), '?')) . " )
                    ");

                    break;

                case 'pgsql':
                    if (strpos($table, '.') !== false) {
                        $table = explode('.', $table);
                        $table = "$table[0]\".\"$table[1]";
                    }

                    foreach ($info as $k => $v) {
                        $insert_fields[] = "\"$k\"";
                    }

                    $insert = core::sql('master')->prepare("
                        INSERT INTO
                        \"$table\"
                        ( " . join(', ', $insert_fields) . " )
                        VALUES
                        ( " . join(',', array_fill(0, count($info), '?')) . " )
                    ");

                    break;

                default:
                    throw new record_exception('Unknown SQL driver');
            }

            return $insert->execute(array_values($info));
        }

        /**
         * Inserts more than one linking record into the database at a time
         *
         * @access protected
         * @static
         * @param array   $infos   an array of associative array of info to be put into the database
         * @param boolean $replace optional - user REPLACE INTO instead of INSERT INTO
         * @return boolean result of the PDO::execute()
         * @throws record_exception
         */
        protected static function _inserts(array $infos, $replace=false) {
            if (! count($infos)) {
                return;
            }
            $insert_fields = [];
            $info          = current($infos);
            $sql           = core::sql('master');
            $table         = static::TABLE;

            if (count($infos) > 1) {
                $sql->beginTransaction();
            }

            switch ($sql->driver()) {
                case 'mysql':
                    if (strpos($table, '.') !== false) {
                        $table = explode('.', $table);
                        $table = "$table[0]`.`$table[1]";
                    }

                    foreach ($info as $k => $v) {
                        $insert_fields[] = "`$k`";
                    }

                    $insert = $sql->prepare("
                        " . ($replace ? 'REPLACE' : 'INSERT IGNORE') . " INTO
                        `$table`
                        ( " . join(', ', $insert_fields) . " )
                        VALUES
                        ( " . join(',', array_fill(0, count($info), '?')) . " )
                    ");

                    break;

                case 'pgsql':
                    if (strpos($table, '.') !== false) {
                        $table = explode('.', $table);
                        $table = "$table[0]\".\"$table[1]";
                    }

                    foreach ($info as $k => $v) {
                        $insert_fields[] = "\"$k\"";
                    }

                    $insert = $sql->prepare("
                        INSERT INTO
                        \"$table\"
                        ( " . join(', ', $insert_fields) . " )
                        VALUES
                        ( " . join(',', array_fill(0, count($info), '?')) . " )
                    ");

                    break;

                default:
                    throw new record_exception('Unknown SQL driver');
            }

            foreach ($infos as $info) {
                $insert->execute(array_values($info));
            }

            if (count($infos) > 1) {
                return $sql->commit();
            } else {
                return true;
            }
        }

        /**
         * Updates linking records in the database
         *
         * @access protected
         * @static
         * @param array $new_info the new info to be put into the model
         * @param array $where    return a model of the new record
         * @return boolean result of the PDO::execute()
         * @throws record_exception
         */
        protected static function _update(array $new_info, array $where) {
            if (count($new_info)) {
                $vals          = [];
                $update_fields = [];
                $table         = static::TABLE;
                $sql           = core::sql('master');

                switch ($sql->driver()) {
                    case 'mysql':
                        if (strpos($table, '.') !== false) {
                            $table = explode('.', $table);
                            $table = "$table[0]`.`$table[1]";
                        }

                        foreach ($new_info as $key => $val) {
                            $update_fields[] = "`$key` = ?";
                            $vals[] = $val;
                        }

                        $where_fields = [];
                        foreach ($where as $k => $v) {
                            if ($v === null) {
                                $where_fields[] = "`$k` IS NULL";
                            } else {
                                $vals[] = $v;
                                $where_fields[] = "`$k` = ?";
                            }
                        }

                        $update = core::sql('master')->prepare("
                            UPDATE `$table`
                            SET " . join(", \n", $update_fields) . "
                            WHERE " . join(" AND \n", $where_fields) . "
                        ");
                        break;

                    case 'pgsql':
                        if (strpos($table, '.') !== false) {
                            $table = explode('.', $table);
                            $table = "$table[0]\".\"$table[1]";
                        }

                        foreach ($new_info as $key => $val) {
                            $update_fields[] = "\"$key\" = ?";
                            $vals[] = $val;
                        }

                        $where_fields = [];
                        foreach ($where as $k => $v) {
                            if ($v === null) {
                                $where_fields[] = "\"$k\" IS NULL";
                            } else {
                                $vals[] = $v;
                                $where_fields[] = "\"$k\" = ?";
                            }
                        }

                        $update = core::sql('master')->prepare("
                            UPDATE \"$table\"
                            SET " . join(", \n", $update_fields) . "
                            WHERE " . join(" AND \n", $where_fields) . "
                        ");
                        break;

                    default:
                        throw new record_exception('Unknown SQL driver');
                }

                return $update->execute($vals);
            }
        }

        /**
         * Delete linking records from the database
         *
         * @access protected
         * @static
         * @param array $keys the where of the query
         * @return boolean result of the PDO::execute()
         * @throws record_exception
         */
        protected static function _delete(array $keys) {
            $where = [];
            $vals  = [];
            $table = static::TABLE;
            $sql   = core::sql('master');

            switch ($sql->driver()) {
                case 'mysql':
                    if (strpos($table, '.') !== false) {
                        $table = explode('.', $table);
                        $table = "$table[0]`.`$table[1]";
                    }

                    foreach ($keys as $k => $v) {
                        if ($v === null) {
                            $where[] = "`$k` IS NULL";
                        } else {
                            $vals[]  = $v;
                            $where[] = "`$k` = ?";
                        }
                    }

                    $delete = $sql->prepare("
                        DELETE FROM `$table`
                        WHERE " . join(" AND ", $where) . "
                    ");

                    break;

                case 'pgsql':
                    if (strpos($table, '.') !== false) {
                        $table = explode('.', $table);
                        $table = "$table[0]\".\"$table[1]";
                    }

                    foreach ($keys as $k => $v) {
                        if ($v === null) {
                            $where[] = "\"$k\" IS NULL";
                        } else {
                            $vals[]  = $v;
                            $where[] = "\"$k\" = ?";
                        }
                    }

                    $delete = $sql->prepare("
                        DELETE FROM \"$table\"
                        WHERE " . join(" AND ", $where) . "
                    ");

                    break;

                default:
                    throw new record_exception('Unknown SQL driver');
            }

            return $delete->execute($vals);
        }

        /**
         * Delete linking records from the database
         *
         * @access protected
         * @static
         * @param array of arrays matching the PKs of the link
         * @return boolean returns true on success
         * @throws record_exception
         */
        protected static function _deletes(array $keys_arr) {
            $vals  = [];
            $where = [];
            $table = static::TABLE;
            $sql   = core::sql('master');

            switch ($sql->driver()) {
                case 'mysql':
                    if (strpos($table, '.') !== false) {
                        $table = explode('.', $table);
                        $table = "$table[0]`.`$table[1]";
                    }

                    foreach ($keys_arr as $keys) {
                        $w = [];
                        foreach ($keys as $k => $v) {
                            if ($v === null) {
                                $w[] = "`$k` IS NULL";
                            } else {
                                $vals[] = $v;
                                $w[]    = "`$k` = ?";
                            }
                        }
                        $where[] = "(" . join(" AND ", $w) . ")";
                    }

                    $delete = core::sql('master')->prepare("
                        DELETE FROM `$table`
                        WHERE " . join(" OR ", $where) . "
                    ");

                    break;

                case 'pgsql':
                    if (strpos($table, '.') !== false) {
                        $table = explode('.', $table);
                        $table = "$table[0]\".\"$table[1]";
                    }

                    foreach ($keys_arr as $keys) {
                        $w = [];
                        foreach ($keys as $k => $v) {
                            if ($v === null) {
                                $w[] = "\"$k\" IS NULL";
                            } else {
                                $vals[] = $v;
                                $w[]    = "\"$k\" = ?";
                            }
                        }
                        $where[] = "(" . join(" AND ", $w) . ")";
                    }

                    $delete = core::sql('master')->prepare("
                        DELETE FROM \"$table\"
                        WHERE " . join(" OR ", $where) . "
                    ");

                    break;

                default:
                    throw new record_exception('Unknown SQL driver');
            }

            return $delete->execute($vals);
        }
    }





