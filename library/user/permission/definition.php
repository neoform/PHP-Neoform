<?php

    /**
     * Entity definition interface
     */
    interface user_permission_definition {

        const NAME         = 'user permission link';
        const TABLE        = 'user_permission';
        const ENTITY_NAME  = 'user_permission';
        const ENTITY_POOL  = 'entities';
        const CACHE_ENGINE = 'memcache';
    }
