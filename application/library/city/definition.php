<?php

    /**
     * Entity definition interface
     */
    interface city_definition {

        const NAME          = 'city';
        const TABLE         = 'city';
        const AUTOINCREMENT = true;
        const PRIMARY_KEY   = 'id';
        const BINARY_PK     = false;
        const ENTITY_NAME   = 'city';
        const ENTITY_POOL   = 'entities';
        const CACHE_ENGINE  = 'redis';
        const SOURCE_ENGINE = null;
        const USING_LIMIT   = false;
        const USING_COUNT   = false;
    }
