<?php

	class s3_api {
		
		// ACL flags
		const ACL_PRIVATE             = 'private';
		const ACL_PUBLIC              = 'public-read';
    	const ACL_OPEN                = 'public-read-write';
    	const ACL_AUTH_READ           = 'authenticated-read';
    	const ACL_OWNER_READ          = 'bucket-owner-read';
    	const ACL_OWNER_FULL_CONTROL  = 'bucket-owner-full-control';
		
		// Storage flags
    	const STORAGE_STANDARD          = 'STANDARD';
    	const STORAGE_REDUCED           = 'REDUCED_REDUNDANCY';
    	const STORAGE_GLACIER           = 'GLACIER';
	
		public static $useSSL = true;
	
		protected static $__accessKey; // AWS Access key
		protected static $__secretKey; // AWS Secret key
	
		public static function set_auth($accessKey, $secretKey) {
			self::$__accessKey = $accessKey;
			self::$__secretKey = $secretKey;
		}
		
		/**
		* Set object or bucket Access Control Policy
		*
		* @param string $bucket Bucket name
		* @param string $uri Object URI
		* @param array $acp Access Control Policy Data (same as the data returned from getAccessControlPolicy)
		* @return boolean
		*/
		public static function set_access_control_policy($bucket, $uri = '', $acp = array()) {
			$dom = new DOMDocument;
			$dom->formatOutput   = true;
			$accessControlPolicy = $dom->createElement('AccessControlPolicy');
			$accessControlList   = $dom->createElement('AccessControlList');
	
			// It seems the owner has to be passed along too
			$owner = $dom->createElement('Owner');
			$owner->appendChild($dom->createElement('ID', $acp['owner']['id']));
			$owner->appendChild($dom->createElement('DisplayName', $acp['owner']['name']));
			$accessControlPolicy->appendChild($owner);
	
			foreach ($acp['acl'] as $g) {
				$grant = $dom->createElement('Grant');
				$grantee = $dom->createElement('Grantee');
				$grantee->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
				if (isset($g['id'])) { // CanonicalUser (DisplayName is omitted)
					$grantee->setAttribute('xsi:type', 'CanonicalUser');
					$grantee->appendChild($dom->createElement('ID', $g['id']));
				} else if (isset($g['email'])) { // AmazonCustomerByEmail
					$grantee->setAttribute('xsi:type', 'AmazonCustomerByEmail');
					$grantee->appendChild($dom->createElement('EmailAddress', $g['email']));
				} else if ($g['type'] == 'Group') { // Group
					$grantee->setAttribute('xsi:type', 'Group');
					$grantee->appendChild($dom->createElement('URI', $g['uri']));
				}
				$grant->appendChild($grantee);
				$grant->appendChild($dom->createElement('Permission', $g['permission']));
				$accessControlList->appendChild($grant);
			}
	
			$accessControlPolicy->appendChild($accessControlList);
			$dom->appendChild($accessControlPolicy);
	
			$req = new s3_request('PUT', $bucket, $uri);
			$req->set_parameter('acl', null);
			$req->set_data($dom->saveXML());
			$req->set_header('Content-Type', 'application/xml');
			$req->execute(array(200));
			
			return true;
		}
	
	
		/**
		* Get object or bucket Access Control Policy
		*
		* @param string $bucket Bucket name
		* @param string $uri Object URI
		* @return mixed | false
		*/
		public static function get_access_control_policy($bucket, $uri = '') {
			$req = new s3_request('GET', $bucket, $uri);
			$req->set_parameter('acl', null);
			$req->execute(array(200));
			
			$acp = array();
			if (isset($req->body()->Owner, $req->body()->Owner->ID, $req->body()->Owner->DisplayName)) {
				$acp['owner'] = array(
					'id'   => (string) $req->body()->Owner->ID, 
					'name' => (string) $req->body()->Owner->DisplayName
				);
			}
			if (isset($req->body()->AccessControlList)) {
				$acp['acl'] = array();
				foreach ($req->body()->AccessControlList->Grant as $grant) {
					foreach ($grant->Grantee as $grantee) {
						if (isset($grantee->ID, $grantee->DisplayName)) {// CanonicalUser
							$acp['acl'][] = array(
								'type'       => 'CanonicalUser',
								'id'         => (string) $grantee->ID,
								'name'       => (string) $grantee->DisplayName,
								'permission' => (string) $grant->Permission
							);
						} else if (isset($grantee->EmailAddress)) {// AmazonCustomerByEmail
							$acp['acl'][] = array(
								'type'       => 'AmazonCustomerByEmail',
								'email'      => (string) $grantee->EmailAddress,
								'permission' => (string) $grant->Permission
							);
						} else if (isset($grantee->URI)) {// Group
							$acp['acl'][] = array(
								'type'       => 'Group',
								'uri'        => (string) $grantee->URI,
								'permission' => (string) $grant->Permission
							);
						} else {
							continue;
						}
					}
				}
			}
			
			return $acp;
		}
		
		
	
		/**
		* Generate the auth string: "AWS AccessKey:Signature"
		*
		* @internal Used by s3_request::getResponse()
		* @param string $string String to sign
		* @return string
		*/
		public static function __get_signature($string) {
			return 'AWS ' . self::$__accessKey . ':' . self::__get_hash($string);
		}
		
		/**
		* Creates a HMAC-SHA1 hash
		*
		* This uses the hash extension if loaded
		*
		* @internal Used by __getSignature()
		* @param string $string String to sign
		* @return string
		*/
		protected static function __get_hash($string) {
			return base64_encode(
			    hash_hmac(
			        'sha1', 
			        $string, 
			        self::$__secretKey, 
			        true
                )
            );
		}	
	}

