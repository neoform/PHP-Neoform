<?php

    /**
     * Entity definition interface
     */
    interface user_hashmethod_definition {

        const NAME                = 'user hashmethod';
        const TABLE               = 'user_hashmethod';
        const AUTOINCREMENT       = false;
        const PRIMARY_KEY         = 'id';
        const BINARY_PK           = false;
        const ENTITY_NAME         = 'user_hashmethod';
        const CACHE_ENGINE        = 'redis';
        const CACHE_ENGINE_READ   = 'master';
        const CACHE_ENGINE_WRITE  = 'master';
        const SOURCE_ENGINE       = null;
        const SOURCE_ENGINE_READ  = null;
        const SOURCE_ENGINE_WRITE = null;
        const USING_LIMIT         = false;
        const USING_COUNT         = false;
    }
