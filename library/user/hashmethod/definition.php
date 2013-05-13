<?php

    /**
     * Entity definition interface
     */
    interface user_hashmethod_definition {

        const NAME          = 'user hashmethod';
        const TABLE         = 'user_hashmethod';
        const AUTOINCREMENT = false;
        const PRIMARY_KEY   = 'id';
        const BINARY_PK     = false;
        const ENTITY_NAME   = 'user_hashmethod';
        const ENTITY_POOL   = 'entities';
        const CACHE_ENGINE  = 'redis';
        const SOURCE_ENGINE = null;
        const USING_LIMIT   = false;
        const USING_COUNT   = false;
    }
