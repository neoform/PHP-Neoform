<?php

    class s3_bucket_api extends s3_api {

        public static function list_buckets($detailed=false) {
            $req = new s3_request('GET');
            $req->execute(array(200));

            $results = array();
            if (! isset($resp->body()->Buckets)) {
                return $results;
            }

            if ($detailed) {
                if (isset($resp->body()->Owner, $resp->body()->Owner->ID, $resp->body()->Owner->DisplayName)) {
                    $results['owner'] = array(
                        'id'   => (string) $resp->body()->Owner->ID,
                        'name' => (string) $resp->body()->Owner->ID,
                    );
                }

                $results['buckets'] = array();
                foreach ($resp->body()->Buckets->Bucket as $b) {
                    $results['buckets'][] = array(
                        'name' => (string) $b->get('Name'),
                        'time' => strtotime((string) $b->get('CreationDate'))
                    );
                }
            } else {
                foreach ($resp->body()->Buckets->Bucket as $b) {
                    $results[] = (string) $b->Name;
                }
            }

            return $results;
        }

        /*
        * Get contents for a bucket
        *
        * If maxKeys is null this method will loop through truncated result sets
        *
        * @param string $bucket Bucket name
        * @param string $prefix Prefix
        * @param string $marker Marker (last file listed)
        * @param string $maxKeys Max keys (maximum number of keys to return)
        * @param string $delimiter Delimiter
        * @param boolean $returnCommonPrefixes Set to true to return CommonPrefixes
        * @return array | false
        */
        public static function get_bucket($bucket, $prefix = null, $marker = null, $maxKeys = null, $delimiter = null, $returnCommonPrefixes = false) {
            $req = new s3_request('GET', $bucket);
            if ($prefix !== null && $prefix !== '') {
                $req->set_parameter('prefix', $prefix);
            }
            if ($marker !== null && $marker !== '') {
                 $req->set_parameter('marker', $marker);
            }
            if ($maxKeys !== null && $maxKeys !== '') {
                 $req->set_parameter('max-keys', $maxKeys);
            }
            if ($delimiter !== null && $delimiter !== '') {
                 $req->set_parameter('delimiter', $delimiter);
            }

            $req->execute(array(200));

            $results = array();

            $nextMarker = null;
            if ($req->body() && isset($req->body()->Contents)) {
                foreach ($response->body()->Contents as $c) {
                    $results[(string) $c->Key] = array(
                        'name' => (string) $c->Key,
                        'time' => strtotime((string) $c->LastModified),
                        'size' => (int) $c->Size,
                        'hash' => substr((string) $c->ETag, 1, -1)
                    );
                    $nextMarker = (string) $c->Key;
                }
            }

            if ($returnCommonPrefixes && $req->body() && $req->body()->CommonPrefixes) {
                foreach ($response->body()->CommonPrefixes as $c) {
                    $results[(string) $c->Prefix] = array('prefix' => (string) $c->Prefix);
                }
            }

            if ($response->body() && isset($response->body()->IsTruncated) && (string) $response->body()->IsTruncated === 'false') {
                return $results;
            }

            if ($response->body() && isset($response->body()->NextMarker)) {
                $nextMarker = (string) $response->body()->NextMarker;
            }

            // Loop through truncated results if maxKeys isn't specified
            if ($maxKeys == null && $nextMarker !== null && (string) $response->body()->IsTruncated === 'true') {
                do {
                    $req = new s3_request('GET', $bucket);

                    if ($prefix !== null && $prefix !== '') {
                        $req->set_parameter('prefix', $prefix);
                    }

                    $req->set_parameter('marker', $nextMarker);

                    if ($delimiter !== null && $delimiter !== '') {
                        $req->set_parameter('delimiter', $delimiter);
                    }

                    if (! $rest->execute(array(200))) {
                        break;
                    }

                    if ($response->body() && isset($response->body()->Contents)) {
                        foreach ($response->body()->Contents as $c) {
                            $results[(string) $c->Key] = array(
                                'name' => (string) $c->Key,
                                'time' => strtotime((string) $c->LastModified),
                                'size' => (int) $c->Size,
                                'hash' => substr((string) $c->ETag, 1, -1)
                            );
                            $nextMarker = (string) $c->Key;
                        }
                    }

                    if ($returnCommonPrefixes && $response->body() && $response->body()->CommonPrefixes) {
                        foreach ($response->body()->CommonPrefixes as $c) {
                            $results[(string) $c->Prefix] = array('prefix' => (string) $c->Prefix);
                        }
                    }

                    if ($response->body() && isset($response->body()->NextMarker)) {
                        $nextMarker = (string) $response->body()->NextMarker;
                    }

                } while ($response !== null && (string) $response->body()->IsTruncated === 'true');
            }

            return $results;
        }

        /**
        * Put a bucket
        *
        * @param string $bucket Bucket name
        * @param constant $acl ACL flag
        * @param string $location Set as "EU" to create buckets hosted in Europe
        * @return boolean
        */
        public static function put_bucket($bucket, $acl = self::ACL_PRIVATE, $location = false) {
            $req = new s3_request('PUT', $bucket);
            $req->set_amz_header('x-amz-acl', $acl);

            if ($location !== false) {
                $dom = new DOMDocument;
                $createBucketConfiguration = $dom->createElement('CreateBucketConfiguration');
                $locationConstraint = $dom->createElement('LocationConstraint', strtoupper($location));
                $createBucketConfiguration->appendChild($locationConstraint);
                $dom->appendChild($createBucketConfiguration);
                $rest->data = $dom->saveXML();
                $rest->size = strlen($rest->data);
                $rest->set_header('Content-Type', 'application/xml');
            }

            $req->execute(array(200));

            return true;
        }


        /**
        * Delete an empty bucket
        *
        * @param string $bucket Bucket name
        * @return boolean
        */
        public static function delete_bucket($bucket) {
            $req = new s3_request('DELETE', $bucket);
            $req->execute(array(204));

            return true;
        }

        /**
        * Set logging for a bucket
        *
        * @param string $bucket Bucket name
        * @param string $targetBucket Target bucket (where logs are stored)
        * @param string $targetPrefix Log prefix (e,g; domain.com-)
        * @return boolean
        */
        public static function set_bucket_logging($bucket, $targetBucket, $targetPrefix = null) {
            // The S3 log delivery group has to be added to the target bucket's ACP
            if ($targetBucket !== null && ($acp = self::getAccessControlPolicy($targetBucket, '')) !== false) {
                // Only add permissions to the target bucket when they do not exist
                $aclWriteSet = false;
                $aclReadSet = false;
                foreach ($acp['acl'] as $acl) {
                    if ($acl['type'] == 'Group' && $acl['uri'] == 'http://acs.amazonaws.com/groups/s3/LogDelivery') {
                        if ($acl['permission'] == 'WRITE') {
                            $aclWriteSet = true;
                        } elseif ($acl['permission'] == 'READ_ACP') {
                            $aclReadSet = true;
                        }
                    }
                }

                if (!$aclWriteSet) $acp['acl'][] = array(
                    'type' => 'Group', 'uri' => 'http://acs.amazonaws.com/groups/s3/LogDelivery', 'permission' => 'WRITE'
                );
                if (!$aclReadSet) $acp['acl'][] = array(
                    'type' => 'Group', 'uri' => 'http://acs.amazonaws.com/groups/s3/LogDelivery', 'permission' => 'READ_ACP'
                );
                if (!$aclReadSet || !$aclWriteSet) {
                    self::setAccessControlPolicy($targetBucket, '', $acp);
                }
            }

            $dom = new DOMDocument;
            $bucketLoggingStatus = $dom->createElement('BucketLoggingStatus');
            $bucketLoggingStatus->setAttribute('xmlns', 'http://s3.amazonaws.com/doc/2006-03-01/');
            if ($targetBucket !== null) {
                if ($targetPrefix == null) {
                    $targetPrefix = $bucket . '-';
                }
                $loggingEnabled = $dom->createElement('LoggingEnabled');
                $loggingEnabled->appendChild($dom->createElement('TargetBucket', $targetBucket));
                $loggingEnabled->appendChild($dom->createElement('TargetPrefix', $targetPrefix));
                // TODO: Add TargetGrants?
                $bucketLoggingStatus->appendChild($loggingEnabled);
            }
            $dom->appendChild($bucketLoggingStatus);

            $req = new s3_request('PUT', $bucket);
            $req->set_parameter('logging', null);
            $req->set_data($dom->saveXML());
            $req->set_header('Content-Type', 'application/xml');
            $req->execute(array(200));

            return true;
        }

        /**
        * Get logging status for a bucket
        *
        * This will return false if logging is not enabled.
        * Note: To enable logging, you also need to grant write access to the log group
        *
        * @param string $bucket Bucket name
        * @return array | false
        */
        public static function get_bucket_logging($bucket) {
            $req = new s3_request('GET', $bucket);
            $req->set_parameter('logging', null);
            $req->execute(array(200));

            if (! isset($req->body()->LoggingEnabled)) {
                return false; // No logging
            }
            return array(
                'targetBucket' => isset($req->body()->LoggingEnabled->TargetBucket) ? (string) $req->body()->LoggingEnabled->TargetBucket : null,
                'targetPrefix' => isset($req->body()->LoggingEnabled->TargetPrefix) ? (string) $req->body()->LoggingEnabled->TargetPrefix : null,
            );
        }

        /**
        * Disable bucket logging
        *
        * @param string $bucket Bucket name
        * @return boolean
        */
        public static function disable_bucket_logging($bucket) {
            return self::set_bucket_logging($bucket, null);
        }

        /**
        * Get a bucket's location
        *
        * @param string $bucket Bucket name
        * @return string | false
        */
        public static function get_bucket_location($bucket) {
            $req = new s3_request('GET', $bucket);
            $req->set_parameter('location', null);
            $req->execute(array(200));

            $body = $req->body();

            return isset($body[0]) && (string) $body[0] !== '' ? (string) $body[0] : 'US';
        }
    }
