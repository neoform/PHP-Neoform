<?php

	class auth_factory implements core_factory {

		public static function init(array $args) {
			if (! count($args)) {
				try {
					$auth = new auth_model(core::http()->cookie(core::config()->auth['cookie']));
	    			$now  = new type_date();
	    			if ($now->getTimestamp() > $auth->expires_on->getTimestamp()) {
						auth_dao::delete($auth);
						$auth->reset();
					}
					return $auth;
	            } catch (auth_exception $e) {
	                return new auth_model(null, []);
	            }
	        }
		}
	}
