<?php

    /**
     * Entity definition interface
     */
    interface user_status_definition {

        const NAME          = 'user status';
        const TABLE         = 'user_status';
        const AUTOINCREMENT = false;
        const PRIMARY_KEY   = 'id';
        const BINARY_PK     = false;
        const ENTITY_NAME   = 'user_status';
        const ENTITY_POOL   = 'entities';
        const CACHE_ENGINE  = 'redis';
        const USING_LIMIT   = false;
    }
