<?php

    /**
     * Entity definition interface
     */
    interface user_date_definition {

        const NAME                = 'user date';
        const TABLE               = 'user_date';
        const AUTOINCREMENT       = false;
        const PRIMARY_KEY         = 'user_id';
        const BINARY_PK           = false;
        const ENTITY_NAME         = 'user_date';
        const CACHE_ENGINE        = 'redis';
        const CACHE_ENGINE_READ   = 'master';
        const CACHE_ENGINE_WRITE  = 'master';
        const SOURCE_ENGINE       = null;
        const SOURCE_ENGINE_READ  = null;
        const SOURCE_ENGINE_WRITE = null;
        const USING_LIMIT         = false;
        const USING_COUNT         = false;
    }
