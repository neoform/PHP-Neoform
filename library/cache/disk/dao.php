<?php

    class cache_disk_dao {

        const DIR = '/cache/disk';

        /**
         * Gets the disk path based on a key
         *
         * @param string $key
         *
         * @return string
         */
        public static function path($key) {
            $key_hash = sha1($key);
            return core::path('application') . self::DIR . '/' . substr($key_hash, 0, 1) . '/' . substr($key_hash, 1, 1) . '/' . substr($key_hash, 2, 1) . '/' . $key_hash . '.' . EXT;
        }

        /**
         * Checks if a record exists
         *
         * @param $file_path
         *
         * @return bool
         */
        public static function exists($file_path) {
            return (bool) file_exists($file_path);
        }

        /**
         * Get a record from disk
         *
         * @param string $file_path
         *
         * @return mixed
         * @throws cache_disk_exception
         */
        public static function get($file_path) {

            if (file_exists($file_path)) {

                require($file_path);

                //if the ttl doesn't match the cached ttl, it means we changed the caching scheme and it's outdated.
                if (! isset($__cache_expiry__) || ($__cache_expiry__ !== false && $__cache_expiry__ < time())) {
                    throw new cache_disk_exception('Cache file expired');
                } else {
                    return $__vars__;
                }
            } else {
                throw new cache_disk_exception('Cache file does not exist');
            }
        }

        /**
         * Set a record to disk
         *
         * @param string $file_path
         * @param mixed $data
         * @param bool $ttl
         *
         * @throws disk_exception
         */
        public static function set($file_path, $data, $ttl=null) {

            $code = '<'.'?'.'php' .
                    "\n\n// DO NOT MODIFY THIS FILE DIRECTLY, IT IS A CACHE FILE AND GETS OVERWRITTEN AUTOMATICALLY.\n\n" .
                    '$'.'__vars__ = ' . var_export($data, true) . ';' . "\n\n" .
                    '$__cache_expiry__ = ' . ($ttl ? time() + $ttl : 'false') . ';' . "\n\n"; // .
                    //'$__ttl__ = ' . ($ttl ? $ttl : 'false') . ';';

            $dir = dirname($file_path);
            if (! file_exists($dir)) {
                try {
                    @mkdir($dir, 0777, true);
                } catch (exception $e) {

                }
            }

            if (! file_put_contents($file_path, $code)) {
                throw new disk_exception('Could not write to cache file.');
            }
        }

        /**
         * Deletes a record from disk cache
         *
         * @param string $file_path
         */
        public static function del($file_path) {
            if (file_exists($file_path)) {
                try {
                    @unlink($file_path);
                } catch (exception $e) {

                }
            }
        }

        /**
         * Deletes the entire disk cache
         *
         * @return string
         */
        public static function flush() {
            $path = core::path('application') . self::DIR;
            $mv = $path . mt_rand(10, 999999999);
            return exec('mv ' . $path . ' ' . $mv .' && rm -r ' . $mv);
        }
    }