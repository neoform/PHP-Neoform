<?php

    /**
     * Creates an instance of a config
     */
    class config_factory implements core_factory {
		
		public static function init(array $args) {
			return new config_instance(count($args) ? current($args) : null);
		}
	}