<?php

    /**
     * Entity definition interface
     */
    interface acl_role_definition {

        const NAME          = 'acl role';
        const TABLE         = 'acl_role';
        const AUTOINCREMENT = true;
        const PRIMARY_KEY   = 'id';
        const BINARY_PK     = false;
        const ENTITY_NAME   = 'acl_role';
        const ENTITY_POOL   = 'entities';
        const CACHE_ENGINE  = 'redis';
        const SOURCE_ENGINE = null;
        const USING_LIMIT   = true;
        const USING_COUNT   = true;
    }
