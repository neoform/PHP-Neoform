<?php

    /**
     * Entity definition interface
     */
    interface user_date_definition {

        const NAME          = 'user date';
        const TABLE         = 'user_date';
        const AUTOINCREMENT = false;
        const PRIMARY_KEY   = 'user_id';
        const BINARY_PK     = false;
        const ENTITY_NAME   = 'user_date';
        const ENTITY_POOL   = 'entities';
        const CACHE_ENGINE  = 'memcache';
    }
