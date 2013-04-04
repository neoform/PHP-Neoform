<?php

    class s3_cloudfront_api extends s3_api {
        
        /**
		* Create a CloudFront distribution
		*
		* @param string $bucket Bucket name
		* @param boolean $enabled Enabled (true/false)
		* @param array $cnames Array containing CNAME aliases
		* @param string $comment Use the bucket name as the hostname
		* @return array | false
		*/
		public static function createDistribution($bucket, $enabled = true, $cnames = array(), $comment = '') {
			self::$useSSL = true; // CloudFront requires SSL
			$rest = new s3_request('POST', '', '2008-06-30/distribution', true, 'cloudfront.amazonaws.com');
			$rest->data = self::__getCloudFrontDistributionConfigXML($bucket.'.s3.amazonaws.com', $enabled, $comment, (string)microtime(true), $cnames);
			$rest->size = strlen($rest->data);
			$rest->setHeader('Content-Type', 'application/xml');
			$resp = self::__getCloudFrontResponse($rest);
	
			if ($resp->error() === null && $rest->http_code() !== 201) {
				$resp->set_error(array(
					'code'    => $resp->http_code(),
					'message' => 'Unexpected HTTP status',
				));
			}
			
			if ($resp->error() !== null) {
				throw new s3_exception(sprintf("s3_api::createDistribution({" . $bucket . "}, ". ((int) $enabled) . ", '" . $comment . "'): [%s] %s", $resp->error('code'), $resp->error('message')));
			} elseif ($resp->body()) {
				return self::__parseCloudFrontDistributionConfig($rest->body());
			}
			return false;
		}
	
	
		/**
		* Get CloudFront distribution info
		*
		* @param string $distributionId Distribution ID from listDistributions()
		* @return array | false
		*/
		public static function getDistribution($distributionId) {
			self::$useSSL = true; // CloudFront requires SSL
			$rest = new s3_request('GET', '', '2008-06-30/distribution/'.$distributionId, true, 'cloudfront.amazonaws.com');
			$rest = self::__getCloudFrontResponse($rest);
	
			if ($rest->error() === null && $rest->http_code() !== 200) {
				$rest->set_error(array(
					'code' => $rest->http_code(), 
					'message' => 'Unexpected HTTP status',
				));
			}
			if ($rest->error() !== null) {
				throw new s3_exception(sprintf("s3_api::getDistribution(" . $distributionId . "): [%s] %s", $rest->error('code'), $rest->error('message')));
			} elseif ($rest->body() instanceof SimpleXMLElement) {
				$dist = self::__parseCloudFrontDistributionConfig($rest->body);
				$dist['hash'] = $rest->headers['hash'];
				return $dist;
			}
			return false;
		}
	
	
		/**
		* Update a CloudFront distribution
		*
		* @param array $dist Distribution array info identical to output of getDistribution()
		* @return array | false
		*/
		public static function updateDistribution($dist) {
			self::$useSSL = true; // CloudFront requires SSL
			$rest = new s3_request('PUT', '', '2008-06-30/distribution/' . $dist['id'] . '/config', true, 'cloudfront.amazonaws.com');
			$rest->data = self::__getCloudFrontDistributionConfigXML($dist['origin'], $dist['enabled'], $dist['comment'], $dist['callerReference'], $dist['cnames']);
			$rest->size = strlen($rest->data);
			$rest->setHeader('If-Match', $dist['hash']);
			$rest = self::__getCloudFrontResponse($rest);
	
			if ($rest->error() === null && $rest->http_code() !== 200) {
				$rest->set_error(array('code' => $rest->http_code(), 'message' => 'Unexpected HTTP status'));
			}
			
			if ($rest->error() !== null) {
				throw new s3_exception(sprintf("s3_api::updateDistribution({" . $dist['id'] . "}, " . ((int)$enabled) . ", '" . $comment . "'): [%s] %s", $rest->error('code'), $rest->error('message')));
			} else {
				$dist = self::__parseCloudFrontDistributionConfig($rest->body);
				$dist['hash'] = $rest->headers['hash'];
				return $dist;
			}
			return false;
		}
	
	
		/**
		* Delete a CloudFront distribution
		*
		* @param array $dist Distribution array info identical to output of getDistribution()
		* @return boolean
		*/
		public static function deleteDistribution($dist) {
			self::$useSSL = true; // CloudFront requires SSL
			$rest = new s3_request('DELETE', '', '2008-06-30/distribution/'.$dist['id'], true, 'cloudfront.amazonaws.com');
			$rest->setHeader('If-Match', $dist['hash']);
			$rest = self::__getCloudFrontResponse($rest);
	
			if ($rest->error() === null && $rest->http_code() !== 204) {
				$rest->set_error(array(
					'code' => $rest->http_code(), 
					'message' => 'Unexpected HTTP status',
				));
			}
			
			if ($rest->error() !== null) {
				throw new s3_exception(sprintf("s3_api::deleteDistribution({" . $dist['id'] . "}): [%s] %s", $rest->error('code'), $rest->error('message')));
			}
			return true;
		}
	
	
		/**
		* Get a list of CloudFront distributions
		*
		* @return array
		*/
		public static function listDistributions() {
			self::$useSSL = true; // CloudFront requires SSL
			$rest = new s3_request('GET', '', '2008-06-30/distribution', true, 'cloudfront.amazonaws.com');
			$rest = self::__getCloudFrontResponse($rest);
	
			if ($rest->error() === null && $rest->http_code() !== 200) {
				$rest->set_error(array(
					'code' => $rest->http_code(), 
					'message' => 'Unexpected HTTP status',
				));
			}
			if ($rest->error() !== null) {
				throw new s3_exception(sprintf("s3_api::listDistributions(): [%s] %s", $rest->error('code'), $rest->error('message')));
			} elseif ($rest->body() instanceof SimpleXMLElement && isset($rest->body()->DistributionSummary)) {
				$list = array();
				if (isset($rest->body()->Marker, $rest->body()->MaxItems, $rest->body()->IsTruncated)) {
					//$info['marker'] = (string)$rest->body()->Marker;
					//$info['maxItems'] = (int)$rest->body()->MaxItems;
					//$info['isTruncated'] = (string)$rest->body()->IsTruncated == 'true' ? true : false;
				}
				foreach ($rest->body()->DistributionSummary as $summary) {
					$list[(string)$summary->Id] = self::__parseCloudFrontDistributionConfig($summary);
				}
				return $list;
			}
			return array();
		}
	
	
		/**
		* Get a DistributionConfig DOMDocument
		*
		* @internal Used to create XML in createDistribution() and updateDistribution()
		* @param string $bucket Origin bucket
		* @param boolean $enabled Enabled (true/false)
		* @param string $comment Comment to append
		* @param string $callerReference Caller reference
		* @param array $cnames Array of CNAME aliases
		* @return string
		*/
		private static function __getCloudFrontDistributionConfigXML($bucket, $enabled, $comment, $callerReference = '0', $cnames = array()) {
			$dom = new DOMDocument('1.0', 'UTF-8');
			$dom->formatOutput = true;
			$distributionConfig = $dom->createElement('DistributionConfig');
			$distributionConfig->setAttribute('xmlns', 'http://cloudfront.amazonaws.com/doc/2008-06-30/');
			$distributionConfig->appendChild($dom->createElement('Origin', $bucket));
			$distributionConfig->appendChild($dom->createElement('CallerReference', $callerReference));
			foreach ($cnames as $cname) {
				$distributionConfig->appendChild($dom->createElement('CNAME', $cname));
			}
			if ($comment !== '') {
				$distributionConfig->appendChild($dom->createElement('Comment', $comment));
			}
			$distributionConfig->appendChild($dom->createElement('Enabled', $enabled ? 'true' : 'false'));
			$dom->appendChild($distributionConfig);
			return $dom->saveXML();
		}
	
	
		/**
		* Parse a CloudFront distribution config
		*
		* @internal Used to parse the CloudFront DistributionConfig node to an array
		* @param object &$node DOMNode
		* @return array
		*/
		private static function __parseCloudFrontDistributionConfig(&$node) {
			$dist = array();
			if (isset($node->Id, $node->Status, $node->LastModifiedTime, $node->DomainName)) {
				$dist['id'] = (string)$node->Id;
				$dist['status'] = (string)$node->Status;
				$dist['time'] = strtotime((string)$node->LastModifiedTime);
				$dist['domain'] = (string)$node->DomainName;
			}
			if (isset($node->CallerReference)) {
				$dist['callerReference'] = (string)$node->CallerReference;
			}
			if (isset($node->Comment)) {
				$dist['comment'] = (string)$node->Comment;
			}
			if (isset($node->Enabled, $node->Origin)) {
				$dist['origin'] = (string)$node->Origin;
				$dist['enabled'] = (string)$node->Enabled == 'true' ? true : false;
			} elseif (isset($node->DistributionConfig)) {
				$dist = array_merge($dist, self::__parseCloudFrontDistributionConfig($node->DistributionConfig));
			}
			if (isset($node->CNAME)) {
				$dist['cnames'] = array();
				foreach ($node->CNAME as $cname) $dist['cnames'][(string)$cname] = (string)$cname;
			}
			return $dist;
		}
	
	
		/**
		* Grab CloudFront response
		*
		* @internal Used to parse the CloudFront s3_request::execute() output
		* @param object &$rest s3_request instance
		* @return object
		*/
		private static function __getCloudFrontResponse(&$rest) {
			$rest->execute();
			if ($rest->response->error() === null && $rest->response->body()) {
				// Grab CloudFront errors
				if ($rest->response->body()->get('Error') && $rest->response->body()->get('Error/Code') && $rest->response->body()->get('Error/Message')) {
					$rest->response->set_error(array(
						'code'    => (string)$rest->response->body()->get('Error/Code'),
						'message' => (string)$rest->response->body()->get('Error/Message')
					));
				}
			}
			return $rest->response;
		}
        
    }