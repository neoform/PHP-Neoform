<?php

	class disk_lib {

		const READ  = 1;
		const WRITE = 2;

        /**
         * Read directory (recursively) and return file paths
         *
         * @param      $path
         * @param bool $recursive
         *
         * @return array file paths
         */
        public static function readdir($path, $recursive=false) {
			$return = [];
			if ($handle = opendir($path)) {
				while (($file = readdir($handle)) !== false) {
					if ($file !== '.' && $file !== '..') {
						$filepath = $path . '/' . $file;
						if ($recursive && is_dir($filepath)) {
							$return[$file] = self::readdir($filepath, $recursive);
						} else {
							$return[$file] = (is_readable($filepath) ? self::READ : 0) | (is_writeable($filepath) ? self::WRITE : 0);
						}
					}
				}
				closedir($handle);
			}
			return $return;
		}

        /**
         * Put contents into a file, even if the parent directory does not exist, and chowns it to be www-data and 0777
         *
         * @param $path
         * @param $contents
         *
         * @return bool|int
         */
        public static function file_put_contents($path, $contents) {

			$dir = dirname($path);

			if (! file_exists($dir)) {
				try {
					mkdir($dir, 0777, true);
                    self::www_chown($dir);
                } catch (exception $e) {

				}
			}

			try {
				$return = file_put_contents($path, $contents);
                self::www_chown($path);
                return $return;
			} catch (exception $e) {

			}

			return false;
		}

        /**
         * Read a segment of a file
         *
         * @param $fp
         * @param $offset
         * @param $length
         *
         * @return null|string
         */
        public static function read($fp, $offset, $length) {
            $data = null;
            try {
                if (! is_resource($fp)) {
                    $fp = fopen($fp, 'r');
                }

                // move the pointer to the offset
                fseek($fp, $offset, SEEK_SET);

                // if file is at the end, nothing to return
                if (feof($fp)) {
                    return null;
                }

                $data = fread($fp, $length);

            } catch (exception $e) {

            }

            if ($fp && is_resource($fp)) {
                @fclose($fp);
            }

            return $data;
        }

        /**
         * Chown a file to owned by www-data, only works in CLI context when user is root or www-data.
         *
         * @param $path
         */
        public static function www_chown($path) {
            if (core::context() === 'cli') {
                @chown($path, 'www-data');
                @chgrp($path, 'www-data');
            }
        }
	}