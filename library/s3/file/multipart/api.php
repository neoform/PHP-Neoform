<?php

    class s3_file_multipart_api extends s3_api {
    
        const MIN_PART_SIZE = 5242880;
    
        public static function get_incomplete_objects($bucket) {
			$req = new s3_request('GET', $bucket);
			$req->set_parameter('uploads');
			$req->execute(array(200));	
			
			$files = array();
			
			if (isset($req->body()->Upload)) {
    			foreach ($req->body()->Upload as $file) {
        			$files[] = array(
        			    'key'           => isset($file->Key) ? (string) $file->Key : null,
        			    'upload_id'     => isset($file->UploadId) ? (string) $file->UploadId : null,
        			    'initiator'     => isset($file->Initiator->ID, $file->Initiator->DisplayName) ? array('id' => (string) $file->Initiator->ID, 'name' => (string) $file->Initiator->DisplayName, ) : null,
        			    'owner'         => isset($file->Owner->ID, $file->Owner->DisplayName) ? array('id' => (string) $file->Owner->ID, 'name' => (string) $file->Owner->DisplayName, ) : null,
        			    'storage_class' => isset($file->StorageClass) ? (string) $file->StorageClass : null,
        			    'initiated'     => isset($file->Initiated) ? (string) $file->Initiated : null,
        			);
    			}
			}

			return $files;
		}
	
		/**
		* Start a multipart upload of an object
		*
		* @param string $bucket Bucket name
		* @param string $uri Object URI
		* @return uploadId
		*/
		public static function put_object($bucket, $uri) {//, $download_filename=null) {
			$req = new s3_request('POST', $bucket, $uri);
			$req->set_parameter('uploads');
			//if ($download_filename) {
    		//	$req->set_header('Content-​Disposition', 'attachment; filename="' . addslashes($download_filename) . '"');
			//}
			$req->execute(array(200));
			
			if (! isset($req->body()->Bucket, $req->body()->Key, $req->body()->UploadId)) {
    			return false;
            }
			
			return array(
			    'bucket'    => (string) $req->body()->Bucket,
			    'key'       => (string) $req->body()->Key,
			    'upload_id' => (string) $req->body()->UploadId,			    
		    );
		}
	
		/**
		* Upload a part of an object
		*
		* @param string $bucket Bucket name
		* @param string $uri Object URI
		* @return uploadId
		*/
		public static function put_object_part($bucket, $uri, $upload_id, $part_number, $data) {
		  
		    if ($part_number > 1 && ! strlen($data)) {
		       throw new s3_exception('Part has no data');
		    }
		
			$req = new s3_request('PUT', $bucket, $uri);
			$req->set_parameter('partNumber', $part_number);
			$req->set_parameter('uploadId', $upload_id);
			$req->set_file_data($data);			
			
			$req->execute(array(200));
			
			return $req->header('hash'); // the request library renames ETag to "hash", not sure why
		}
	
		/**
		* Get info on a multipart upload object
		*
		* @param string $bucket Bucket name
		* @param string $uri Object URI
		* @return uploadId
		*/
		public static function get_object($bucket, $uri, $upload_id) {
			$req = new s3_request('GET', $bucket, $uri);
			$req->set_parameter('uploadId', $upload_id);
			$req->execute(array(200));
						
			$body = $req->body();			
						
			if (! isset($body->Bucket, $body->Key, $body->UploadId)) {
    			return false;
            }
            			
			$parts = array();
			if (isset($body->Part)) {
    			foreach ($body->Part as $part) {
    			    if (isset($part->PartNumber)) {
        			    $parts[(int) $part->PartNumber] = array(
        			        'part_number'   => (int) $part->PartNumber,
        			        'last_modified' => (string) $part->LastModified,
        			        'etag'          => (string) $part->ETag,
        			        'size'          => (string) $part->Size,
        			    );
    			    }
    			}
			}
			
			return array(
			    'bucket'           => (string) $body->Bucket,
			    'key'              => (string) $body->Key,
			    'upload_id'        => (string) $body->UploadId,	
			    'initiator'        => isset($body->Initiator->ID, $body->Initiator->DisplayName) ? array('id' => (string) $body->Initiator->ID, 'name' => (string) $body->Initiator->DisplayName, ) : null,
			    'owner'            => isset($body->Owner->ID, $body->Owner->DisplayName) ? array('id' => (string) $body->Owner->ID, 'name' => (string) $body->Owner->DisplayName, ) : null,
			    'storage_class'    => isset($body->StorageClass) ? (string) $body->StorageClass : null,
			    'part_number'      => isset($body->PartNumberMarker) ? (int) $body->PartNumberMarker : null,
			    'next_part_number' => isset($body->NextPartNumberMarker) ? (int) $body->NextPartNumberMarker : null,
			    'max_parts'        => isset($body->MaxParts) ? (int) $body->MaxParts : null,
			    'is_truncated'     => isset($body->IsTruncated) ? $body->IsTruncated === 'false' : null,
			    'parts'            => $parts,			    
		    );
		}

	
		/**
		* Abort a multipart upload of an object
		*
		* @param string $bucket Bucket name
		* @param string $uri Object URI
		* @param string $upload_id uploadId supplied by s3
		* @return uploadId
		*/
		public static function abort_object($bucket, $uri, $upload_id) {
			$req = new s3_request('DELETE', $bucket, $uri);
			$req->set_parameter('uploadId', $upload_id);
			
			return $req->execute(array(204, 404)) === 204;
		}

	
		/**
		* Complete a multipart upload of an object
		*
		* @param string $bucket Bucket name
		* @param string $uri Object URI
		* @param string $upload_id uploadId supplied by s3
		* @return uploadId
		*/
		public static function complete_object($bucket, $uri, $upload_id, $size, array $parts) {
			$req = new s3_request('POST', $bucket, $uri);
			$req->set_parameter('uploadId', $upload_id);
			$req->set_header('Content-Length', $size);
			
			$dom = new DOMDocument('1.0', 'UTF-8');
			$completeMultipartUpload = $dom->createElement('CompleteMultipartUpload');			
			foreach ($parts as $part_number => $etag) {
    			$element = $dom->createElement('Part');
    			$element->appendChild($dom->createElement('PartNumber', $part_number));
    			$element->appendChild($dom->createElement('ETag', $etag));
    			$completeMultipartUpload->appendChild($element);
			}
			$dom->appendChild($completeMultipartUpload);
					
			$req->set_file_data($dom->saveXML());
			$req->execute(array(200));
			
			$resp = $req->body();
			
			if (isset($resp->ETag)) {
			    $etag = $resp->ETag;
			} else {
			    return false;
			}
			
			return $etag{0} == '"' ? substr($etag, 1, -1) : $etag;
		}
	
		/**
		* Get upload POST parameters for form uploads
		*
		* @param string $bucket Bucket name
		* @param string $uriPrefix Object URI prefix
		* @param constant $acl ACL constant
		* @param integer $lifetime Lifetime in seconds
		* @param integer $maxFileSize Maximum filesize in bytes (default 5MB)
		* @param string $successRedirect Redirect URL or 200 / 201 status code
		* @param array $amzHeaders Array of x-amz-meta-* headers
		* @param array $headers Array of request headers or content type as a string
		* @param boolean $flashVars Includes additional "Filename" variable posted by Flash
		* @return object
		*/
		public static function get_http_upload_post_params($bucket, $uriPrefix = '', $acl = self::ACL_PRIVATE, $lifetime = 3600, $maxFileSize = 5242880, $successRedirect = "201", $amzHeaders = array(), $headers = array(), $flashVars = false) {
			// Create policy object
			$policy = array();
			$policy['expiration'] = gmdate('Y-m-d\TH:i:s\Z', (time() + $lifetime));
			$policy['conditions'] = array();
			
			$policy['conditions'][] = array('bucket' => $bucket);
			$policy['conditions'][] = array('acl' => $acl);
	
			if (is_numeric($successRedirect) && in_array((int)$successRedirect, array(200, 201))) {
				$policy['conditions'][] = array('success_action_status' => (string) $successRedirect);
			} else {// URL
				$policy['conditions'][] = array('success_action_redirect' => (string) $successRedirect);
			}
	
			$policy['conditions'][] = array('starts-with', '$key', $uriPrefix);
			
			if ($flashVars) {
				$policy['conditions'][] = array('starts-with', '$Filename', '');
			}
			
			foreach (array_keys($headers) as $headerKey) {
				$policy['conditions'][] = array('starts-with', '$'.$headerKey, '');
			}
			
			foreach ($amzHeaders as $headerKey => $headerVal) {
				$policy['conditions'][] = array($headerKey => (string) $headerVal);
			}
			
			$policy['conditions'][] = array('content-length-range', 0, $maxFileSize);
		
			$policy = base64_encode(str_replace('\/', '/', json_encode($policy)));
		
			// Create parameters
			$params = array();
			$params['AWSAccessKeyId'] = self::$__accessKey;
			$params['key']            = $uriPrefix . '${filename}';
			$params['acl']            = $acl;
			$params['policy']         = $policy; 
			$params['signature']      = self::__getHash($params['policy']);
			
			if (is_numeric($successRedirect) && in_array((int)$successRedirect, array(200, 201))) {
				$params['success_action_status'] = (string) $successRedirect;
			} else {
				$params['success_action_redirect'] = $successRedirect;
			}
			
			foreach ($headers as $headerKey => $headerVal) {
				$params[$headerKey] = (string) $headerVal;
			}
			foreach ($amzHeaders as $headerKey => $headerVal) {
				$params[$headerKey] = (string) $headerVal;
			}
			return $params;
		}	
    
    
    }