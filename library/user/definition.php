<?php

    /**
     * Entity definition interface
     */
    interface user_definition {

        const NAME          = 'user';
        const TABLE         = 'user';
        const AUTOINCREMENT = true;
        const PRIMARY_KEY   = 'id';
        const BINARY_PK     = false;
        const ENTITY_NAME   = 'user';
        const ENTITY_POOL   = 'entities';
        const CACHE_ENGINE  = 'redis';
        const USING_LIMIT   = true;
    }
