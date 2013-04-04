<?php

    /**
     * Entity definition interface
     */
    interface permission_definition {

        const NAME          = 'permission';
        const TABLE         = 'permission';
        const AUTOINCREMENT = false;
        const PRIMARY_KEY   = 'id';
        const BINARY_PK     = false;
        const ENTITY_NAME   = 'permission';
        const ENTITY_POOL   = 'entities';
        const CACHE_ENGINE  = 'memcache';
    }
