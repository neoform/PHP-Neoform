<?php

    /**
     * Entity definition interface
     */
    interface user_lostpassword_definition {

        const NAME                = 'user lostpassword';
        const TABLE               = 'user_lostpassword';
        const AUTOINCREMENT       = false;
        const PRIMARY_KEY         = 'hash';
        const BINARY_PK           = false;
        const ENTITY_NAME         = 'user_lostpassword';
        const CACHE_ENGINE        = 'redis';
        const CACHE_ENGINE_READ   = 'master';
        const CACHE_ENGINE_WRITE  = 'master';
        const SOURCE_ENGINE       = null;
        const SOURCE_ENGINE_READ  = null;
        const SOURCE_ENGINE_WRITE = null;
        const USING_LIMIT         = false;
        const USING_COUNT         = false;
    }
