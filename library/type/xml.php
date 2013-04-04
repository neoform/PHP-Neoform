<?php

	class type_xml {

		public static function to_array($xml) {
			self::normalize_simple_xml(simplexml_load_string($xml), $result);
        	return $result;
		}

        public static function normalize_simple_xml($obj, &$result) {
            $data = $obj;
            
            if (is_object($data)) {
                $data = get_object_vars($data);
            }
            
            if (is_array($data)) {
                foreach ($data as $key => $value) {
                    $res = null;
                    self::normalize_simple_xml($value, $res);
                    if (($key == '@attributes') && ($key)) {
                        $result = $res;
                    } else {
                        $result[$key] = $res;
                    }
                }
            } else {
                $result = $data;
            }
        }
    }