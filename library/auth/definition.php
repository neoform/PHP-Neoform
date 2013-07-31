<?php

    /**
     * Entity definition interface
     */
    interface auth_definition {

        const NAME                = 'auth';
        const TABLE               = 'auth';
        const AUTOINCREMENT       = false;
        const PRIMARY_KEY         = 'hash';
        const BINARY_PK           = true;
        const ENTITY_NAME         = 'auth';
        const CACHE_ENGINE        = 'redis';
        const CACHE_ENGINE_READ   = 'master';
        const CACHE_ENGINE_WRITE  = 'master';
        const SOURCE_ENGINE       = 'redis';
        const SOURCE_ENGINE_READ  = null;
        const SOURCE_ENGINE_WRITE = null;
        const USING_LIMIT         = false;
        const USING_COUNT         = false;
    }
