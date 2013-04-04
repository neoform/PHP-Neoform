<?php

	class flash_factory implements core_factory {
		
		public static function init(array $args) {
			return new flash_instance();
		}
	}