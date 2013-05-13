<?php

    /**
     * Entity definition interface
     */
    interface region_definition {

        const NAME          = 'region';
        const TABLE         = 'region';
        const AUTOINCREMENT = true;
        const PRIMARY_KEY   = 'id';
        const BINARY_PK     = false;
        const ENTITY_NAME   = 'region';
        const ENTITY_POOL   = 'entities';
        const CACHE_ENGINE  = 'redis';
        const SOURCE_ENGINE = null;
        const USING_LIMIT   = true;
        const USING_COUNT   = true;
    }
