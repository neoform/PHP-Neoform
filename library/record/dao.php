<?php

    /**
     * record_dao Standard database access, each extended DAO class must have a corresponding table with a primary key
     *
     * REQUIRED: every extended class *must* have the following constants:
     *    string TABLE       the table name in the database
     *    string NAME        the name of this record type (shown to users)
     *    string MODEL       the name of the model class
     *    string COLLECTION  the name of the collection class
     *    string EXCEPTION   the name of the exception class
     *    string ENTITY_POOL must have a corresponding entry in the config file for the caching engine being used, eg (core::config()->memcache['pools'] = 'entities')
     *    bool AUTOINCREMENT is this table an auto-increment table
     *    string PRIMARY_KEY column name of the primary key for this table
     *    bool BINARY_PK     is the primary key a binary string
     */

    abstract class record_dao {

        // Key name used for primary key lookups
        const BY_PK = 'by_pk';

        /**
         * Get a cached recordset
         *
         * @access protected
         * @static
         * @final
         * @param string   $key       full cache key with namespace - it's recomended that record_dao::_build_key() is used to create this key
         * @param function $get       closure function that retreieves the recordset from its origin
         * @return array   the cached recordset
         */
        final protected static function _single($key, $get) {
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
         * @param string   $key           full cache key with namespace - it's recomended that record_dao::_build_key() is used to create this key
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
         * @param string  $cache_key_name word used to identify this cache entry, it should be unique to the dao class its found in
         * @param array   $params         optional - array of table keys and their values being looked up in the table
         * @param string  $class          optional - closure function that retreieves the recordset from its origin
         * @return string a cache key that is unqiue to the application
         */
        final public static function _build_key($cache_key_name, array $params=[], $class=null) {
            // each key is namespaced with the name of the class
            if (count($params) === 1) {
                return ($class ? $class : get_called_class()) . ":$cache_key_name:" . md5(base64_encode(current($params)));
            } else {
                ksort($params);
                foreach ($params as & $param) {
                    $param = base64_encode($param);
                }
                return ($class ? $class : get_called_class()) . ":$cache_key_name:" . md5(json_encode(array_values($params)));
            }
        }

        /**
         * Pulls a single record's information from the database
         *
         * @param int $pk primary key of a record
         *
         * @return array cached record data
         * @throws record_exception
         */
        public static function by_pk($pk) {

            $self = get_called_class();

            $get = function() use ($pk, $self) {

                $table = $self::TABLE;
                $sql   = core::sql('slave');

                switch ($sql->driver()) {
                    case 'mysql':

                        if (strpos($table, '.') !== false) {
                            $table = explode('.', $table);
                            $table = "$table[0]`.`$table[1]";
                        }

                        $info = $sql->prepare("
                            SELECT
                                *
                            FROM
                                `$table`
                            WHERE
                                `" . $self::PRIMARY_KEY . "` = ?
                        ");

                        $info->execute([
                            $pk,
                        ]);
                        break;

                    case 'pgsql':

                        if (strpos($table, '.') !== false) {
                            $table = explode('.', $table);
                            $table = "$table[0]\".\"$table[1]";
                        }

                        $info = $sql->prepare("
                            SELECT
                                *
                            FROM
                                \"$table\"
                            WHERE
                                \"" . $self::PRIMARY_KEY . "\" = ?
                        ");

                        // PG handles binary data differently than strings
                        if ($self::BINARY_PK) {
                            $info->bindValue(1, $pk, PDO::PARAM_LOB);
                            $info->execute();
                        } else {
                            $info->execute([
                                $pk,
                            ]);
                        }

                        break;

                    default:
                       throw new record_exception('Unknown SQL driver');
                }

                if (! ($info = $info->fetch())) {
                    $exception = $self::ENTITY_NAME . '_exception';
                    throw new $exception('That ' . $self::NAME . ' doesn\'t exist');
                }

                return $info;
            };

            return cache_lib::single(
                static::CACHE_ENGINE,
                "$self:" . self::BY_PK . (static::BINARY_PK ? ':'. md5(base64_encode($pk)) : ":$pk"),
                static::ENTITY_POOL,
                $get
            );
        }

        /**
         * Pulls a single record's information from the database
         *
         * @access public
         * @static
         * @param array   $pks primary key of a records
         * @return array  cached records data - with preserved key names from $pks.
         * @throws record_exception
         */
        public static function by_pks(array $pks) {

            if (! count($pks)) {
                return [];
            }

            $self = get_called_class();

            $get = function(array $pks) use ($self) {
                $table = $self::TABLE;
                $sql   = core::sql('slave');

                switch ($sql->driver()) {
                    case 'mysql':
                        if (strpos($table, '.') !== false) {
                            $table = explode('.', $table);
                            $table = "$table[0]`.`$table[1]";
                        }

                        $infos_rs = $sql->prepare("
                            SELECT
                                *
                            FROM
                                `$table`
                            WHERE
                                `" . $self::PRIMARY_KEY . "` IN (" . join(',', array_fill(0, count($pks), '?')) . ")
                        ");
                        $infos_rs->execute(array_values($pks));

                        break;

                    case 'pgsql':
                        if (strpos($table, '.') !== false) {
                            $table = explode('.', $table);
                            $table = "$table[0]\".\"$table[1]";
                        }

                        $infos_rs = $sql->prepare("
                            SELECT
                                *
                            FROM
                                \"$table\"
                            WHERE
                                \"" . $self::PRIMARY_KEY . "\" IN (" . join(',', array_fill(0, count($pks), '?')) . ")
                        ");

                        // PG handles binary data differently than strings
                        if ($self::BINARY_PK) {
                            foreach (array_values($pks) as $i => $pk) {
                                $infos_rs->bindValue($i + 1, $pk, PDO::PARAM_LOB);
                            }
                            $infos_rs->execute();
                        } else {
                            $infos_rs->execute();
                        }

                        break;

                    default:
                        throw new record_exception('Unknown SQL driver');
                }

                $infos = [];
                foreach ($infos_rs->fetchAll() as $info) {
                    $k = array_search($info[$self::PRIMARY_KEY], $pks);
                    if ($k !== false) {
                        $infos[$k] = $info;
                    }
                }

                return $infos;
            };

            $key = function($pk) use ($self) {
                return "$self:" . record_dao::BY_PK . ($self::BINARY_PK ? ':'. md5(base64_encode($pk)) : ":$pk");
            };

            return cache_lib::multi(
                static::CACHE_ENGINE,
                $pks,
                $key,
                static::ENTITY_POOL,
                $get
            );
        }

        /**
         * Gets the primary keys of records that match the $keys
         *
         * @access protected
         * @static
         * @final
         * @param string  $cache_key_name word used to identify this cache entry, it should be unique to the dao class its found in
         * @param array   $keys           array of table keys and their values being looked up in the table
         * @return array  pks of records from cache
         * @throws record_exception
         */
        final protected static function _by_fields($cache_key_name, array $keys) {

            $table = static::TABLE;
            $pk    = static::PRIMARY_KEY;

            $get = function() use ($keys, $table, $pk) {

                if (strpos($table, '.') !== false) {
                    $table = explode('.', $table);
                    $table = "$table[0]`.`$table[1]";
                }

                $sql   = core::sql('slave');
                $where = [];
                $vals  = [];

                switch ($sql->driver()) {
                    case 'mysql':
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

                        $rs = $sql->prepare("
                            SELECT
                                `$pk`
                            FROM
                                `$table`
                            " . (count($where) ? " WHERE " . join(" AND ", $where) : "") . "
                        ");
                        break;

                    case 'pgsql':
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

                        $rs = $sql->prepare("
                            SELECT
                                \"$pk\"
                            FROM
                                \"$table\"
                            " . (count($where) ? " WHERE " . join(" AND ", $where) : "") . "
                        ");
                        break;

                    default:
                        throw new record_exception('Unknown SQL driver');
                }

                $rs->execute($vals);

                $rs = $rs->fetchAll();
                $pks = [];
                foreach ($rs as $row) {
                    $pks[] = $row[$pk];
                }
                return $pks;
            };

            return cache_lib::single(
                static::CACHE_ENGINE,
                self::_build_key($cache_key_name, $keys),
                static::ENTITY_POOL,
                $get
            );
        }

        /**
         * Gets the full record(s) that match the $keys
         *
         * @access protected
         * @static
         * @final
         * @param string  $cache_key_name word used to identify this cache entry, it should be unique to the dao class its found in
         * @param array   $keys           array of table keys and their values being looked up in the table
         * @return array  pks of records from cache
         * @throws record_exception
         */
        final protected static function _all($cache_key_name, array $keys=null) {

            $pk    = static::PRIMARY_KEY;
            $table = static::TABLE;

            $get = function() use ($keys, $table, $pk) {

                $sql   = core::sql('slave');
                $where = [];
                $vals  = [];

                switch ($sql->driver()) {
                    case 'mysql':
                        if ($keys) {
                            foreach ($keys as $k => $v) {
                                if (is_array($v) && count($v)) {
                                    foreach ($v as $arr_v) {
                                        $vals[] = $arr_v;
                                    }
                                    $where[] = "`$k` IN(" . join(',', array_fill(0, count($v), '?')) . ")";
                                } else {
                                    if ($v === null) {
                                        $where[] = "`$k` IS NULL";
                                    } else {
                                        $vals[] = $v;
                                        $where[] = "`$k` = ?";
                                    }
                                }
                            }
                        }

                        if (strpos($table, '.') !== false) {
                            $table = explode('.', $table);
                            $table = "$table[0]`.`$table[1]";
                        }

                        $info = $sql->prepare("
                            SELECT
                                *
                            FROM
                                `$table`
                            " . (count($where) ? " WHERE " . join(" AND ", $where) : "") . "
                            ORDER BY
                                `$pk` ASC
                        ");
                        break;

                    case 'pgsql':
                        if ($keys) {
                            foreach ($keys as $k => $v) {
                                if (is_array($v) && count($v)) {
                                    foreach ($v as $arr_v) {
                                        $vals[] = $arr_v;
                                    }
                                    $where[] = "\"$k\" IN(" . join(',', array_fill(0, count($v), '?')) . ")";
                                } else {
                                    if ($v === null) {
                                        $where[] = "\"$k\" IS NULL";
                                    } else {
                                        $vals[] = $v;
                                        $where[] = "\"$k\" = ?";
                                    }
                                }
                            }
                        }

                        if (strpos($table, '.') !== false) {
                            $table = explode('.', $table);
                            $table = "$table[0]\".\"$table[1]";
                        }

                        $info = $sql->prepare("
                            SELECT
                                *
                            FROM
                                \"$table\"
                            " . (count($where) ? " WHERE " . join(" AND ", $where) : "") . "
                            ORDER BY
                                `$pk` ASC
                        ");
                        break;

                    default:
                        throw new record_exception('Unknown SQL driver');
                }

                $info->execute($vals);

                $infos = [];
                foreach ($info->fetchAll() as $info) {
                    $infos[$info[$pk]] = $info;
                }

                return $infos;
            };

            return cache_lib::single(
                static::CACHE_ENGINE,
                self::_build_key($cache_key_name, []),
                static::ENTITY_POOL,
                $get
            );
        }

        /**
         * Gets the pks of more than one set of key values
         *
         * @access protected
         * @static
         * @final
         * @param string  $cache_key_name word used to identify this cache entry, it should be unique to the dao class its found in
         * @param array   $keys_arr       array of arrays of table keys and their values being looked up in the table - each sub-array must have matching keys
         * @return array  pks of records from cache
         * @throws record_exception
         */
        final protected static function _by_fields_multi($cache_key_name, array $keys_arr) {

            $self  = get_called_class();
            $table = static::TABLE;
            $pk    = static::PRIMARY_KEY;

            $get = function() use ($keys_arr, $table, $pk) {

                $sql            = core::sql('slave');
                $key_fields     = array_keys(current($keys_arr));
                $reverse_lookup = [];
                $return         = [];
                $vals           = [];
                $where          = [];

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
                                    $w[]    = "`$k` = ?";
                                }
                            }
                            $where[] = '(' . join(" AND ", $w) . ')';
                        }

                        $rs = $sql->prepare("
                            SELECT
                                `$pk`,
                                CONCAT(" . join(", ':', ", $key_fields) . ") `__cache_key__`
                            FROM
                                `$table`
                            WHERE
                                " . join(' OR ', $where) . "
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
                                \"$pk\",
                                CONCAT(" . join(", ':', ", $key_fields) . ") \"__cache_key__\"
                            FROM
                                \"$table\"
                            WHERE
                                " . join(' OR ', $where) . "
                        ");
                        break;

                    default:
                        throw new record_exception('Unknown SQL driver');
                }

                $rs->execute($vals);

                $rows = $rs->fetchAll();
                foreach ($rows as $row) {
                    $return[
                        $reverse_lookup[$row['__cache_key__']]
                    ][] = $row[$pk];
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
         * Gets records that match the $keys, rather than just getting the pk, this gets the columns specified by $select_fields
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
        final protected static function _by_fields_select($cache_key_name, array $select_fields, array $keys) {

            $table = static::TABLE;

            $get = function() use ($select_fields, $keys, $table) {

                $sql   = core::sql('slave');
                $where = [];
                $vals  = [];

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
                            SELECT
                                " . join(',', $select_fields) . "
                            FROM
                                `$table`
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
                            SELECT
                                " . join(',', $select_fields) . "
                            FROM
                                \"$table\"
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
         * Inserts a record into the database
         *
         * @access protected
         * @static
         * @param array   $info         an associative array of into to be put into the database
         * @param boolean $replace      optional - user REPLACE INTO instead of INSERT INTO
         * @param boolean $return_model optional - return a model of the new record
         * @return record_model|true if $return_model is set to true, the model created from the info is returned
         * @throws record_exception
         */
        protected static function _insert(array $info, $replace=false, $return_model = true) {
            $table = static::TABLE;
            $sql   = core::sql('master');

            switch ($sql->driver()) {
                case 'mysql':
                    if (strpos($table, '.') !== false) {
                        $table = explode('.', $table);
                        $table = "$table[0]`.`$table[1]";
                    }

                    $insert_fields = [];
                    foreach (array_keys($info) as $key) {
                        $insert_fields[] = "`$key`";
                    }

                    $insert = $sql->prepare("
                        " . ($replace ? 'REPLACE' : 'INSERT IGNORE') . " INTO
                            `$table`
                            ( " . join(', ', $insert_fields) . " )
                            VALUES
                            ( " . join(',', array_fill(0, count($insert_fields), '?')) . " )
                    ");

                    $insert->execute(array_values($info));

                    if (static::AUTOINCREMENT) {
                        $info[static::PRIMARY_KEY] = $sql->last_insert_id();
                    }

                    break;

                case 'pgsql':
                    if (strpos($table, '.') !== false) {
                        $table = explode('.', $table);
                        $table = "$table[0]\".\"$table[1]";
                    }
                    $insert_fields = [];
                    foreach (array_keys($info) as $key) {
                        $insert_fields[] = "\"$key\"";
                    }

                    $pk = static::PRIMARY_KEY;

                    $insert = $sql->prepare("
                        INSERT INTO
                            \"$table\"
                            ( " . join(', ', $insert_fields) . " )
                            VALUES
                            ( " . join(',', array_fill(0, count($insert_fields), '?')) . " )
                            RETURNING \"$pk\"
                    ");

                    sql_pdo::bind_by_casting(
                        $insert,
                        static::castings(),
                        $info
                    );

                    $insert->execute();

                    if (static::AUTOINCREMENT) {
                        $info[static::PRIMARY_KEY] = $insert->fetch()[$pk];
                    }

                    break;

                default:
                    throw new record_exception('Unknown SQL driver');
            }

            //incase a blank record was cached
            cache_lib::delete(
                static::CACHE_ENGINE,
                get_called_class() . ':' . self::BY_PK . ':' . (static::BINARY_PK ? md5(base64_encode($info[static::PRIMARY_KEY])) : $info[static::PRIMARY_KEY]),
                static::ENTITY_POOL
            );

            if ($return_model) {
                $model = static::ENTITY_NAME . '_model';
                return new $model(null, $info);
            } else {
                return true;
            }
        }

        /**
         * Inserts multiple record into the database
         *
         * @access protected
         * @static
         * @param array   $infos             an array of associative arrays of into to be put into the database, if this dao represents multiple tables, the info will be split up across the applicable tables.
         * @param boolean $keys_match        optional - if all the records being inserted have the same array keys this should be true. it is faster to insert all the records at the same time, but this can only be done if they all have the same keys.
         * @param boolean $replace           optional - user REPLACE INTO instead of INSERT INTO
         * @param boolean $return_collection optional - return a collection of models created
         * @return record_collection|true if $return_collection is true function returns a collection
         * @throws record_exception
         */
        protected static function _inserts(array $infos, $keys_match = true, $replace=false, $return_collection = true) {
            $table  = static::TABLE;
            $self   = get_called_class();
            $sql    = core::sql('master');
            $driver = $sql->driver();

            if (strpos($table, '.') !== false) {
                switch ($driver) {
                    case 'mysql':
                        $table = explode('.', $table);
                        $table = "$table[0]`.`$table[1]";
                        break;

                    case 'pgsql':
                        $table = explode('.', $table);
                        $table = "$table[0]\".\"$table[1]";
                        break;

                    default:
                        throw new record_exception('Unknown SQL driver');
                }
            }

            if ($return_collection) {
                $models = [];
            }
            $delete_keys = [];

            if ($keys_match) {
                $insert_fields = [];

                switch ($driver) {
                    case 'mysql':
                        foreach (array_keys(current($infos)) as $k) {
                            $insert_fields[] = "`$k`";
                        }
                        break;

                    case 'pgsql':
                        foreach (array_keys(current($infos)) as $k) {
                            $insert_fields[] = "\"$k\"";
                        }
                        break;

                    default:
                        throw new record_exception('Unknown SQL driver');
                }

                // If the table is auto increment, we cannot lump all inserts into one query
                // since we need the returned IDs for cache-busting and to return a model
                if (static::AUTOINCREMENT || $return_collection) {
                    $sql->beginTransaction();
                    $pk = static::PRIMARY_KEY;

                    switch ($driver) {
                        case 'mysql':
                            $insert = $sql->prepare("
                                " . ($replace ? 'REPLACE' : 'INSERT IGNORE') . " INTO
                                    `$table`
                                    ( " . join(', ', $insert_fields) . " )
                                    VALUES
                                    ( " . join(',', array_fill(0, count($insert_fields), '?')) . " )
                            ");
                            foreach ($infos as $k => $info) {
                                $insert->execute(array_values($info));

                                if (static::AUTOINCREMENT) {
                                    $info[static::PRIMARY_KEY] = $sql->last_insert_id();
                                }

                                if ($return_collection) {
                                    $models[$k] = new $model(null, $info);
                                }

                                $delete_keys[] = "$self:" . self::BY_PK . ':' . (static::BINARY_PK ? md5(base64_encode($info[static::PRIMARY_KEY])) : $info[static::PRIMARY_KEY]);
                            }

                            break;

                        case 'pgsql':
                            $insert = $sql->prepare("
                                INSERT INTO
                                    \"$table\"
                                    ( " . join(', ', $insert_fields) . " )
                                    VALUES
                                    ( " . join(',', array_fill(0, count($insert_fields), '?')) . " )
                                    RETURNING \"$pk\"
                            ");

                            foreach ($infos as $k => $info) {
                                $insert->execute(array_values($info));

                                if (static::AUTOINCREMENT) {
                                    $info[static::PRIMARY_KEY] = $insert->fetch()[$pk];
                                }

                                if ($return_collection) {
                                    $models[$k] = new $model(null, $info);
                                }

                                $delete_keys[] = "$self:" . self::BY_PK . ':' . (static::BINARY_PK ? md5(base64_encode($info[static::PRIMARY_KEY])) : $info[static::PRIMARY_KEY]);
                            }

                            break;

                        default:
                            throw new record_exception('Unknown SQL driver');
                    }

                    $sql->commit();
                } else {
                    // this might explode if $keys_match was a lie
                    $insert_vals = new splFixedArray(count($insert_fields) * count($infos));
                    $i = 0;
                    foreach ($infos as $info) {
                        foreach ($info as $v) {
                            $insert_vals[$i] = $v;
                        }
                        $delete_keys[] = "$self:" . self::BY_PK . ':' . (static::BINARY_PK ? md5(base64_encode($info[static::PRIMARY_KEY])) : $info[static::PRIMARY_KEY]);
                    }

                    switch ($driver) {
                        case 'mysql':
                            $inserts = $sql->prepare("
                                INSERT INTO
                                    `$table`
                                    ( " . implode(', ', $insert_fields) . " )
                                    VALUES
                                    " . join(', ', array_fill(0, count($infos), '( ' . join(',', array_fill(0, count($insert_fields), '?')) . ')')) . "
                            ");
                            break;

                        case 'pgsql':
                            $inserts = $sql->prepare("
                                INSERT INTO
                                    \"$table\"
                                    ( " . implode(', ', $insert_fields) . " )
                                    VALUES
                                    " . join(', ', array_fill(0, count($infos), '( ' . join(',', array_fill(0, count($insert_fields), '?')) . ')')) . "
                            ");
                            break;

                        default:
                            throw new record_exception('Unknown SQL driver');
                    }

                    $inserts->execute($insert_vals);
                }
            } else {
                $sql->beginTransaction();

                foreach ($infos as $k => $info) {
                    $insert_fields = [];

                    switch ($driver) {
                        case 'mysql':
                            foreach (array_keys($info) as $key) {
                                $insert_fields[] = "`$key`";
                            }

                            $insert = $sql->prepare("
                                INSERT INTO
                                    `$table`
                                    ( " . join(', ', $insert_fields) . " )
                                    VALUES
                                    ( " . join(',', array_fill(0, count($info), '?')) . " )
                            ");
                            break;

                        case 'pgsql':
                            foreach (array_keys($info) as $key) {
                                $insert_fields[] = "\"$key\"";
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

                    $insert->execute(array_values($info));

                    if (static::AUTOINCREMENT) {
                        $info[static::PRIMARY_KEY] = $sql->last_insert_id();
                    }

                    if ($return_collection) {
                        $models[$k] = new $model(null, $info);
                    }

                    $delete_keys[] = "$self:" . self::BY_PK . ':' . (static::BINARY_PK ? md5(base64_encode($info[static::PRIMARY_KEY])) : $info[static::PRIMARY_KEY]);
                }

                $sql->commit();
            }

            cache_lib::delete_multi(
                static::CACHE_ENGINE,
                $delete_keys,
                static::ENTITY_POOL
            );

            if ($return_collection) {
                $collection    = static::ENTITY_NAME . '_collection';
                return new $collection(null, $models);
            } else {
                return true;
            }
        }

        /**
         * Updates a record in the database
         *
         * @access protected
         * @static
         * @param record_model $model         the model that is to be updated
         * @param array        $info          the new info to be put into the model
         * @param boolean      $return_model  optional - return a model of the new record
         * @return record_model|true if $return_model is true, an updated model is returned
         * @throws record_exception
         */
        protected static function _update(record_model $model, array $info, $return_model = true) {

            if (! count($info)) {
                return $return_model ? $model : false;
            }

            $table = static::TABLE;
            $pk    = static::PRIMARY_KEY;
            $sql   = core::sql('master');

            switch ($sql->driver()) {
                case 'mysql':
                    if (strpos($table, '.') !== false) {
                        $table = explode('.', $table);
                        $table = "$table[0]`.`$table[1]";
                    }
                    $update_fields = [];
                    foreach (array_keys($info) as $key) {
                        $update_fields[] = "`$key` = :$key";
                    }
                    $update = $sql->prepare("
                        UPDATE
                            `$table`
                        SET
                            " . implode(", \n", $update_fields) . "
                        WHERE
                            `$pk` = :$pk
                    ");

                    $info[$pk] = $model->$pk;
                    $update->execute($info);

                    break;

                case 'pgsql':
                    if (strpos($table, '.') !== false) {
                        $table = explode('.', $table);
                        $table = "$table[0]\".\"$table[1]";
                    }
                    $update_fields = [];
                    foreach (array_keys($info) as $key) {
                        $update_fields[] = "\"$key\" = :$key";
                    }
                    $update = $sql->prepare("
                        UPDATE
                            \"$table\"
                        SET
                            " . implode(", \n", $update_fields) . "
                        WHERE
                            \"$pk\" = :$pk
                    ");

                    $info[$pk] = $model->$pk;

                    sql_pdo::bind_by_casting(
                        $update,
                        static::castings(),
                        $info,
                        true
                    );

                    $update->execute($info);

                    break;

                default:
                    throw new record_exception('Unknown SQL driver');
            }

            cache_lib::delete(
                static::CACHE_ENGINE,
                get_called_class() . ':' . self::BY_PK . ':' . (static::BINARY_PK ? md5(base64_encode($model->$pk)) : $model->$pk),
                static::ENTITY_POOL
            );

            // if the primary key was changed, bust the cache for that new key too
            if (isset($info[$pk])) {
                cache_lib::delete(
                    static::CACHE_ENGINE,
                    get_called_class() . ':' . self::BY_PK . ':' . (static::BINARY_PK ? md5(base64_encode($info[$pk])) : $info[$pk]),
                    static::ENTITY_POOL
                );
            }

            if ($return_model) {
                $updated_model = clone $model;
                $updated_model->_update($info);
                return $updated_model;
            } else {
                return true;
            }
        }

        /**
         * Deletes a record from the database
         *
         * @access protected
         * @static
         * @param record_model $model the model that is to be deleted
         * @return boolean returns true on success
         * @throws record_exception
         */
        protected static function _delete(record_model $model) {
            $table = static::TABLE;
            $pk    = static::PRIMARY_KEY;
            $sql   = core::sql('master');

            switch ($sql->driver()) {
                case 'mysql':
                    if (strpos($table, '.') !== false) {
                        $table = explode('.', $table);
                        $table = "$table[0]`.`$table[1]";
                    }

                    $delete = $sql->prepare("
                        DELETE FROM
                            `$table`
                        WHERE
                            `$pk` = ?
                    ");
                    $delete->execute([
                        $model->$pk,
                    ]);

                    break;

                case 'pgsql':
                    if (strpos($table, '.') !== false) {
                        $table = explode('.', $table);
                        $table = "$table[0]\".\"$table[1]";
                    }

                    $delete = $sql->prepare("
                        DELETE FROM
                            \"$table\"
                        WHERE
                            \"$pk\" = ?
                    ");

                    // PG handles binary data differently than strings
                    if (static::BINARY_PK) {
                        $delete->bindValue(1, $pk, PDO::PARAM_LOB);
                        $delete->execute();
                    } else {
                        $delete->execute([
                            $model->$pk,
                        ]);
                    }

                    break;

                default:
                    throw new record_exception('Unknown SQL driver');
            }

            cache_lib::delete(
                static::CACHE_ENGINE,
                get_called_class() . ':' . self::BY_PK . ':' . (static::BINARY_PK ? md5(base64_encode($model->$pk)) : $model->$pk),
                static::ENTITY_POOL
            );
            return true;
        }

        /**
         * Deletes a record from the database
         *
         * @access protected
         * @static
         * @param record_collection $collection the collection of models that is to be deleted
         * @return boolean returns true on success
         * @throws record_exception
         */
        protected static function _deletes(record_collection $collection) {

            if (! count($collection)) {
                return;
            }

            $self  = get_called_class();
            $pks   = $collection->field(static::PRIMARY_KEY);
            $table = static::TABLE;
            $sql   = core::sql('master');

            switch ($sql->driver()) {
                case 'mysql':
                    if (strpos($table, '.') !== false) {
                        $table = explode('.', $table);
                        $table = "$table[0]`.`$table[1]";
                    }

                    $delete = $sql->prepare("
                        DELETE FROM
                            `$table`
                        WHERE
                            `" . static::PRIMARY_KEY . "` IN (" . join(',', array_fill(0, count($pks), '?')) . ")
                    ");

                    $delete->execute($pks);

                    break;

                case 'pgsql':
                    if (strpos($table, '.') !== false) {
                        $table = explode('.', $table);
                        $table = "$table[0]\".\"$table[1]";
                    }

                    $delete = $sql->prepare("
                        DELETE FROM
                            \"$table\"
                        WHERE
                            \"" . static::PRIMARY_KEY . "\" IN (" . join(',', array_fill(0, count($pks), '?')) . ")
                    ");

                    // PG handles binary data differently than strings
                    if (static::BINARY_PK) {
                        $i = 0;
                        foreach ($pks as $pk) {
                            $delete->bindValue($i++, $pk, PDO::PARAM_LOB);
                        }
                        $delete->execute();
                    } else {
                        $delete->execute($pks);
                    }

                    break;

                default:
                    throw new record_exception('Unknown SQL driver');
            }

            $delete_cache_keys = [];
            foreach ($pks as $pk) {
                $delete_cache_keys[] = "$self:" . self::BY_PK . (static::BINARY_PK ? ':' . md5(base64_encode($pk)) : ":$pk");
            }

            cache_lib::delete_multi(
                static::CACHE_ENGINE,
                $delete_cache_keys,
                static::ENTITY_POOL
            );
            return true;
        }
    }