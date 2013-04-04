<?php

    /**
     * APC cache instance
     * to use: core::cache_apc()
     */
    class cache_apc_instance extends ArrayObject {

        use core_instance;

        /**
         * Gets a record from APC
         *
         * @param $key
         *
         * @return mixed
         * @throws cache_apc_exception
         */
		public function __get($key) {
			$data = apc_fetch($key, $success);
			if ($success) {
				return $data;
			} else {
				throw new cache_apc_exception('Cache does not exist');
			}
		}

        /**
         * Gets a record from APC
         *
         * @param $key
         *
         * @return mixed
         * @throws cache_apc_exception
         */
        public function get($key) {
			$data = apc_fetch($key, $success);
			if ($success) {
				return $data;
			} else {
				throw new cache_apc_exception('Cache does not exist');
			}
		}

        /**
         * Creates a record in APC
         *
         * @param string $key
         * @param string $val
         * @param int    $ttl
         *
         * @return bool
         */
        public function set($key, $val, $ttl=0) {
			return apc_store($key, $val, $ttl);
		}

        /**
         * Delete's a record from APC
         *
         * @param $key
         *
         * @return bool|string[]
         */
        public function __unset($key) {
			return apc_delete($key);
		}

        /**
         * Delete's a record from APC
         *
         * @param string $key
         *
         * @return bool|string[]
         */
        public function del($key) {
			return apc_delete($key);
		}

        /**
         * Checks if a record exists
         *
         * @param string $key
         *
         * @return bool|string[]
         */
        public function __isset($key) {
			return apc_exists($key);
		}

        /**
         * Increment the value of a record
         *
         * @param string $key
         * @param int    $step
         *
         * @return bool|int
         */
        public function increment($key, $step=1) {
			return apc_inc($key, $step);
		}

        /**
         * Decrement the value of a record
         *
         * @param string $key
         * @param int    $step
         *
         * @return bool|int
         */
		public function decrement($key, $step=1) {
			return apc_dec($key, $step);
		}

        /**
         * Delete all cached vars
         *
         * @return bool
         */
        public function flush() {
			return apc_clear_cache();
		}

        /**
         * Get general info from APC
         *
         * @return array|bool
         */
        public function info() {
			return apc_cache_info();
		}
	}