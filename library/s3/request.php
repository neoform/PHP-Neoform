<?php

	class s3_request {

		protected $verb;
		protected $bucket;
		protected $uri;
		protected $resource = ''; 
		protected $parameters = array();
		protected $amz_headers = array(); 
		protected $headers = array(
			'Host'           => '', 
			'Date'           => '', 
			'Content-MD5'    => '', 
			'Content-Type'   => '',
		);
		
		protected $file_handle;
		protected $file_size = 0;
		protected $file_data;
		
		// Response
		protected $response_code;
		protected $response_headers;
		protected $response_body;
		
		/**
		* Constructor
		*
		* @param string $verb Verb
		* @param string $bucket Bucket name
		* @param string $uri Object URI
		* @return mixed
		*/
		function __construct($verb, $bucket=null, $uri=null, $default_host=null) {
			$this->verb   = $verb;
			$this->bucket = strtolower($bucket);
			$this->uri    = $uri !== '' ? '/' . str_replace('%2F', '/', rawurlencode($uri)) : '/';
		
			if ($this->bucket) {
				$this->headers['Host'] = $this->bucket . '.' . ($default_host ?: 's3.amazonaws.com');
				$this->resource = '/' . $this->bucket . $this->uri;
			} else {
				$this->headers['Host'] = ($default_host ?: 's3.amazonaws.com');
				$this->resource = $this->uri;
			}
			
			$this->headers['Date'] = gmdate('D, d M Y H:i:s T');
		}
	
		public function set_parameter($k, $v=null) {
			$this->parameters[$k] = $v;
		    return $this;
		}
	
		public function set_header($k, $v) {
			$this->headers[$k] = $v;
		    return $this;
		}
		
		public function set_amz_header($k, $v) {
			$this->amz_eaders[$k] = $v;
		    return $this;
		}
	
		public function set_file_handle($file_handle) {
    		$this->file_handle = $file_handle;
    		return $this;
		}
	
		public function set_file_size($file_size) {
    		$this->file_size = $file_size;
    		return $this;
		}
	
		public function set_file_data($file_data) {
    		$this->file_data = $file_data;
    		
    		$this->set_header('Content-MD5', base64_encode(md5($file_data, 1)));
    		$this->set_header('Content-Length', strlen($file_data));
    		$this->set_header('Content-Type', 'binary/octel-stream');
    		
    		return $this;
		}
		
		public function body() {
            return $this->response_body;
		}
		
		public function headers() {
		    return $this->response_headers;
		}
		
		public function header($k) {
            if (isset($this->response_headers[$k])) {
    		    return $this->response_headers[$k];
            }
		}
		
		public function code() {
		    return $this->response_code;
		}
	
		public function execute(array $expected_http_codes) {		
			$query = '';
			if (sizeof($this->parameters) > 0) {
				$query = substr($this->uri, -1) !== '?' ? '?' : '&';
				foreach ($this->parameters as $var => $value) {
					if ($value == null || $value == '') {
						$query .= $var . '&';
					} else {
						$query .= $var . '=' . rawurlencode($value) . '&';
					}
				}
				$query = substr($query, 0, -1);
				$this->uri .= $query;
		
				//if (1 ||
				//	array_key_exists('acl', $this->parameters) ||
				//	array_key_exists('location', $this->parameters) ||
				//	array_key_exists('torrent', $this->parameters) ||
				//	array_key_exists('logging', $this->parameters)
				//) {
				$this->resource .= $query;
				//}
			}
			$url = ((s3_api::$useSSL && extension_loaded('openssl')) ? 'https://' : 'http://') . $this->headers['Host'] . $this->uri;
			
			// Basic setup
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_USERAGENT, 'S3/php');
	
			if (s3_api::$useSSL) {
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
			}
	
			curl_setopt($curl, CURLOPT_URL, $url);
	
			// Headers
			$headers = array(); 
			$amz     = array();
			
			foreach ($this->amz_headers as $header => $value) {
				if (strlen($value) > 0) {
					$headers[] = $header . ': ' . $value;
					$amz[]     = strtolower($header) . ':' . $value;
				}
			}
			
			foreach ($this->headers as $header => $value) {
				if (strlen($value) > 0) {
					$headers[] = $header . ': ' . $value;
				}
			}
	
			// AMZ headers must be sorted
			if (count($amz)) {
				sort($amz);
				$amz = "\n" . join("\n", $amz);
			} else {
				$amz = '';
			}
			
			// Authorization string (CloudFront stringToSign should only contain a date)
			if ($this->headers['Host'] === 'cloudfront.amazonaws.com') {
				$headers[] = 'Authorization: ' . s3_api::__get_signature($this->headers['Date']);
			} else {
				$headers[] = 'Authorization: ' . s3_api::__get_signature(
					$this->verb . "\n" . 
					$this->headers['Content-MD5'] . "\n".
					$this->headers['Content-Type'] . "\n" . 
					$this->headers['Date'] . $amz . "\n" . 
					$this->resource
				);
			}
			
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);
			curl_setopt($curl, CURLOPT_WRITEFUNCTION, array(& $this, '__response_write_callback'));
			curl_setopt($curl, CURLOPT_HEADERFUNCTION, array(& $this, '__response_header_callback'));
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($curl, CURLINFO_HEADER_OUT, false);
	
			// Request types
			switch ($this->verb) {
				case 'GET': 
					break;
					
				case 'PUT': 
				case 'POST': // POST only used for CloudFront
					if ($this->file_handle !== null && is_resource($this->file_handle)) {
						curl_setopt($curl, CURLOPT_PUT, true);
						curl_setopt($curl, CURLOPT_INFILE, $this->file_handle);
						if ($this->file_size >= 0) {
							curl_setopt($curl, CURLOPT_INFILESIZE, $this->file_size);
        				}
					} elseif ($this->file_data !== null) {
						curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->verb);
						curl_setopt($curl, CURLOPT_POSTFIELDS, $this->file_data);
						curl_setopt($curl, CURLOPT_BUFFERSIZE, strlen($this->file_data));
						curl_setopt($curl, CURLOPT_BINARYTRANSFER, true);
    				} else {
						curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->verb);
					}
					break;
					
				case 'HEAD':
					curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'HEAD');
					curl_setopt($curl, CURLOPT_NOBODY, true);
					break;
				
				case 'DELETE':
					curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
					break;
				
				default: 
					break;
			}
	
			if (! curl_exec($curl)) {
    			throw new s3_exception(curl_error($curl), curl_errno($curl));
			}
			
			//$h = curl_getinfo($curl);
			//echo "REQUEST\n" . $h['request_header'];
			
			$this->response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			
			@curl_close($curl);
			
			if ($this->file_handle !== null && is_resource($this->file_handle)) {
				fclose($this->file_handle);
			}
			
			// Parse body into XML
			if (
			    (
    			    (isset($this->response_headers['type']) && $this->response_headers['type'] === 'application/xml')
    			    ||
    			    // For some reason amazon doesn't past Content-type for POST commands...
    			    ($this->response_code === 200 && $this->verb === 'POST')
			    )
				&& $this->parse_xml()
			) {
			    // Grab S3 errors
				if (! in_array($this->response_code, $expected_http_codes)) {
				
				    $code    = isset($this->response_body->Code) ? $this->response_body->Code : null;
					$message = isset($this->response_body->Message) ? $this->response_body->Message : 'Server responded with HTTP/' . $this->response_code;
				
					throw new s3_exception("$code - $message");
				}
			}
			
			return $this->response_code;
		}
	
		public function parse_xml() {
            if (is_string($this->response_body)) {
                try {
                    $this->response_body = @simplexml_load_string($this->response_body);
                    return true;
                } catch (exception $e) {
                    throw new s3_exception('Invalid xml response from server');
                }
            }
		}
	
		protected function __response_write_callback(& $curl, & $data) {
			if ($this->response_code === 200 && $this->file_handle !== null) {
				return fwrite($this->file_handle, $data);
			} else {
				$this->response_body .= $data;
			}
			return strlen($data);
		}
	
		protected function __response_header_callback(& $curl, & $data) {
		    if (($strlen = strlen($data)) <= 2) {
				return $strlen;
			}
			
			if (substr($data, 0, 4) == 'HTTP') {
				$this->response_code = (int) substr($data, 9, 3);
			} else {
				list($header, $value) = explode(': ', trim($data), 2);
				if ($header == 'Last-Modified') {
					$this->response_headers['time'] = strtotime($value);
				} else if ($header == 'Content-Length') {
					$this->response_headers['size'] = (int) $value;
				} else if ($header == 'Content-Type') {
					$this->response_headers['type'] = $value;
				} else if ($header == 'ETag') {
					$this->response_headers['hash'] = $value{0} == '"' ? substr($value, 1, -1) : $value;
				} else if (preg_match('/^x-amz-meta-.*$/', $header)) {
					$this->response_headers[$header] = is_numeric($value) ? (int) $value : $value;
				}
			}
			
			return $strlen;
		}	
	}
