<?php

    class s3_lib {
        
        public static function load_auth() {
            return s3_api::set_auth(
                core::config('app')->s3['accessKeyId'], 
                core::config('app')->s3['secretAccessKey']
            );
        }
        
        public static function authenticated_url($bucket, $filepath, $ttl) {
            return s3_api::get_authenticated_url($bucket, $filepath, $ttl, false, true);
        }
        
        // dst_filename should not start with a slash
        public static function upload_file($bucket, $dst_filename, $src_filepath, $chunk_complete_callback=null) {
            $filesize = filesize($src_filepath);            
            
            // Bigger than 10MB do multi part upload
            if ($filesize > 10485760) {
                $upload = s3_file_multipart_api::put_object($bucket, $dst_filename);
        	
            	$parts = array();
            	
            	$chunksize   = s3_file_multipart_api::MIN_PART_SIZE;
            	$chunk_count = ceil($filesize / $chunksize);
            	            	
            	for ($chunk_number=1; $chunk_number <= $chunk_count; $chunk_number++) {
            		$parts[$chunk_number] = s3_file_multipart_api::put_object_part(
                        $bucket,
                        $dst_filename,
                        $upload['upload_id'],
                        $chunk_number,
                        disk_lib::read($src_filepath, ($chunk_number - 1) * $chunksize, $chunksize)
                    );
                    
                    if ($chunk_complete_callback) {
                        $chunk_complete_callback($chunk_number, ($chunk_number - 1) * $chunksize, $chunksize);
                    }
            	}
            	
            	$return = (bool) s3_file_multipart_api::complete_object(
                	$bucket, 
                	$dst_filename, 
                	$upload['upload_id'],
                	$filesize, 
                	$parts
            	);
            	
                return $return;
            
            // Regular file upload
            } else {
                
                return (bool) s3_file_api::put_object_file($bucket, $dst_filename, $src_filepath);
            }
        }
    }