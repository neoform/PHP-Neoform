<?php

    class s3_file_api extends s3_api {

        /**
        * Create input info array for putObject()
        *
        * @param string $file Input file
        * @param mixed $md5sum Use MD5 hash (supply a string if you want to use your own)
        * @return array | false
        */
        public static function input_file($file, $md5sum = true) {
            if (! file_exists($file) || !is_file($file) || !is_readable($file)) {
                throw new s3_exception('Unable to open input file: ' . $file);
            }
            return array(
                'file'   => $file,
                'size'   => filesize($file),
                'md5sum' => $md5sum !== false ? (is_string($md5sum) ? $md5sum : base64_encode(md5_file($file, true))) : ''
            );
        }

        /**
        * Create input array info for putObject() with a resource
        *
        * @param string $resource Input resource to read from
        * @param integer $bufferSize Input byte size
        * @param string $md5sum MD5 hash to send (optional)
        * @return array | false
        */
        public static function input_resource(& $resource, $bufferSize, $md5sum = '') {
            if (! is_resource($resource) || $bufferSize < 0) {
                throw new s3_exception('Invalid resource or buffer size');
            }
            $input = array(
                'size'   => $bufferSize,
                'md5sum' => $md5sum,
            );
            $input['fp'] = & $resource;
            return $input;
        }

        /**
        * Put an object
        *
        * @param mixed $input Input data
        * @param string $bucket Bucket name
        * @param string $uri Object URI
        * @param constant $acl ACL constant
        * @param array $metaHeaders Array of x-amz-meta-* headers
        * @param array $requestHeaders Array of request headers or content type as a string
        * @return boolean
        */
        public static function put_object($bucket, $uri, $input, $acl = self::ACL_PRIVATE, $metaHeaders = array(), $requestHeaders = array()) {
            if ($input === false) {
                return false;
            }
            $req = new s3_request('PUT', $bucket, $uri);

            if (is_string($input)) {
                $input = array(
                    'data'   => $input,
                    'size'   => strlen($input),
                    'md5sum' => base64_encode(md5($input, true))
                );
            }

            // Data
            if (isset($input['fp'])) {
                $req->set_file_handle($input['fp']);
            } else if (isset($input['file'])) {
                $req->set_file_handle(@fopen($input['file'], 'rb'));
            } else if (isset($input['data'])) {
                $req->set_file_data($input['data']);
            }

            $size = 0;

            // Content-Length (required)
            if (isset($input['size']) && $input['size'] >= 0) {
                $size = $input['size'];
            } else if (isset($input['file'])) {
                $size = filesize($input['file']);
            }

            $req->set_file_size($size);

            // Custom request headers (Content-Type, Content-Disposition, Content-Encoding)
            if (is_array($requestHeaders)) {
                foreach ($requestHeaders as $h => $v) {
                    $rest->set_header($h, $v);
                }
            } else if (is_string($requestHeaders)) {// Support for legacy contentType parameter
                $input['type'] = $requestHeaders;
            }

            // Content-Type
            if (! isset($input['type'])) {
                if (isset($requestHeaders['Content-Type'])) {
                    $input['type'] = $requestHeaders['Content-Type'];
                } else if (isset($input['file'])) {
                    $input['type'] = self::__get_mime_type($input['file']);
                } else {
                    $input['type'] = 'application/octet-stream';
                }
            }

            // We need to post with Content-Length and Content-Type, MD5 is optional
            if ($size >= 0) {
                $req->set_header('Content-Type', $input['type']);
                if (isset($input['md5sum'])) {
                    $req->set_header('Content-MD5', $input['md5sum']);
                }
                $req->set_amz_header('x-amz-acl', $acl);
                foreach ($metaHeaders as $h => $v) {
                    $req->set_amz_header('x-amz-meta-'.$h, $v);
                }
                $req->execute(array(200));
            } else {
                throw new s3_exception('Missing input parameters');
            }

            return true;
        }

        /**
        * Put an object from a file (legacy function)
        *
        * @param string $file Input file path
        * @param string $bucket Bucket name
        * @param string $uri Object URI
        * @param constant $acl ACL constant
        * @param array $metaHeaders Array of x-amz-meta-* headers
        * @param string $contentType Content type
        * @return boolean
        */
        public static function put_object_file($bucket, $uri, $file, $acl = self::ACL_PRIVATE, $metaHeaders = array(), $contentType = null) {
            return self::put_object($bucket, $uri, self::input_file($file), $acl, $metaHeaders, $contentType);
        }

        /**
        * Put an object from a string (legacy function)
        *
        * @param string $string Input data
        * @param string $bucket Bucket name
        * @param string $uri Object URI
        * @param constant $acl ACL constant
        * @param array $metaHeaders Array of x-amz-meta-* headers
        * @param string $contentType Content type
        * @return boolean
        */
        public static function put_object_string($bucket, $uri, $string, $acl = self::ACL_PRIVATE, $metaHeaders = array(), $contentType = 'text/plain') {
            return self::put_object($bucket, $uri, $string, $acl, $metaHeaders, $contentType);
        }

        /**
        * Get an object
        *
        * @param string $bucket Bucket name
        * @param string $uri Object URI
        * @param mixed $saveTo Filename or resource to write to
        * @return mixed
        */
        public static function get_object($bucket, $uri, $saveTo = null) {
            $req = new s3_request('GET', $bucket, $uri);

            if ($saveTo !== null) {
                if (is_resource($saveTo)) {
                    $req->set_file_handle($saveTo);
                } else {
                    if (($handle = @fopen($saveTo, 'wb')) !== false) {
                        $req->set_file_handle($handle);
                    } else {
                        throw new s3_exception('Unable to open save file for writing: ' . $saveTo);
                    }
                }
            }

            return $req->execute(array(200, 404)) === 200;
        }


        /**
        * Get object information
        *
        * @param string $bucket Bucket name
        * @param string $uri Object URI
        * @param boolean $returnInfo Return response information
        * @return mixed | false
        */
        public static function get_object_info($bucket, $uri, $return_info = true) {
            $req = new s3_request('HEAD', $bucket, $uri);

            return $req->execute(array(200, 404)) === 200 ? ($return_info ? $req->headers() : true) : false;
        }


        /**
        * Copy an object
        *
        * @param string $bucket Source bucket name
        * @param string $uri Source object URI
        * @param string $bucket Destination bucket name
        * @param string $uri Destination object URI
        * @param constant $acl ACL constant
        * @param array $metaHeaders Optional array of x-amz-meta-* headers
        * @param array $requestHeaders Optional array of request headers (content type, disposition, etc.)
        * @return mixed | false
        */
        public static function copy_object($src_bucket, $srcUri, $bucket, $uri, $acl = self::ACL_PRIVATE, array $meta_headers = array(), array $request_headers = array()) {
            $req = new s3_request('PUT', $bucket, $uri);
            $req->set_header('Content-Length', 0);
            foreach ($request_headers as $h => $v) {
                $req->set_header($h, $v);
            }
            foreach ($meta_headers as $h => $v) {
                $req->set_amz_header('x-amz-meta-' . $h, $v);
            }
            $req->set_amz_header('x-amz-acl', $acl);
            $req->set_amz_header('x-amz-copy-source', sprintf('/%s/%s', $src_bucket, $srcUri));
            if (sizeof($request_headers) > 0 || sizeof($meta_headers) > 0) {
                $req->set_amz_header('x-amz-metadata-directive', 'REPLACE');
            }

            $req->execute(array(200));

            return isset($req->body()->LastModified, $req->body()->ETag) ?
                array(
                    'time' => strtotime((string) $req->body()->LastModified),
                    'hash' => substr((string) $req->body()->ETag, 1, -1),
                )
             : false;
        }

        /**
        * Delete an object
        *
        * @param string $bucket Bucket name
        * @param string $uri Object URI
        * @return boolean
        */
        public static function deleteObject($bucket, $uri) {
            $req = new s3_request('DELETE', $bucket, $uri);
            $req->execute(array(204));

            return true;
        }

        /**
        * Get a query string authenticated URL
        *
        * @param string $bucket Bucket name
        * @param string $uri Object URI
        * @param integer $lifetime Lifetime in seconds
        * @param boolean $hostBucket Use the bucket name as the hostname
        * @param boolean $https Use HTTPS ($hostBucket should be false for SSL verification)
        * @return string
        */
        public static function getAuthenticatedURL($bucket, $uri, $lifetime, $hostBucket=false, $https=false) {
            $expires = time() + $lifetime;
            $uri     = str_replace('%2F', '/', rawurlencode($uri)); // URI should be encoded (thanks Sean O'Dea)

            return sprintf(
                ($https ? 'https' : 'http') . '://%s/%s?AWSAccessKeyId=%s&Expires=%u&Signature=%s',
                $hostBucket ? $bucket : 's3.amazonaws.com/' . $bucket,
                $uri,
                self::$__accessKey,
                $expires,
                urlencode(self::__get_hash("GET\n\n\n" . $expires . "\n/" . $bucket . "/" . $uri))
            );
        }

        /**
        * Get MIME type for file
        *
        * @internal Used to get mime types
        * @param string &$file File path
        * @return string
        */
        public static function __get_mime_type(&$file) {
            $type = false;
            // Fileinfo documentation says fileinfo_open() will use the
            // MAGIC env var for the magic file
            if (extension_loaded('fileinfo') && isset($_ENV['MAGIC']) && ($finfo = finfo_open(FILEINFO_MIME, $_ENV['MAGIC'])) !== false) {
                if (($type = finfo_file($finfo, $file)) !== false) {
                    // Remove the charset and grab the last content-type
                    $type = explode(' ', str_replace('; charset=', ';charset=', $type));
                    $type = array_pop($type);
                    $type = explode(';', $type);
                    $type = trim(array_shift($type));
                }
                finfo_close($finfo);

            // If anyone is still using mime_content_type()
            } else if (function_exists('mime_content_type')) {
                $type = trim(mime_content_type($file));
            }

            if ($type !== false && strlen($type) > 0) {
                return $type;
            }

            // Otherwise do it the old fashioned way
            static $exts = array(
                'jpg'  => 'image/jpeg',
                'gif'  => 'image/gif',
                'png'  => 'image/png',
                'tif'  => 'image/tiff',
                'tiff' => 'image/tiff',
                'ico'  => 'image/x-icon',
                'swf'  => 'application/x-shockwave-flash',
                'pdf'  => 'application/pdf',
                'zip'  => 'application/zip',
                'gz'   => 'application/x-gzip',
                'tar'  => 'application/x-tar',
                'bz'   => 'application/x-bzip',
                'bz2'  => 'application/x-bzip2',
                'txt'  => 'text/plain',
                'asc'  => 'text/plain',
                'htm'  => 'text/html',
                'html' => 'text/html',
                'css'  => 'text/css',
                'js'   => 'text/javascript',
                'xml'  => 'text/xml',
                'xsl'  => 'application/xsl+xml',
                'ogg'  => 'application/ogg',
                'mp3'  => 'audio/mpeg',
                'wav'  => 'audio/x-wav',
                'avi'  => 'video/x-msvideo',
                'mpg'  => 'video/mpeg',
                'mpeg' => 'video/mpeg',
                'mov'  => 'video/quicktime',
                'flv'  => 'video/x-flv',
                'php'  => 'text/x-php'
            );
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            return isset($exts[$ext]) ? $exts[$ext] : 'application/octet-stream';
        }
    }