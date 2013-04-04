<?php

	class cache_apc_factory implements core_factory {
		
		public static function init(array $args) {
			return new cache_apc_instance(count($args) ? current($args) : null);
		}
	}