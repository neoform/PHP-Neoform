<?php

    /**
     * Entity definition interface
     */
    interface acl_resource_definition {

        const NAME          = 'acl resource';
        const TABLE         = 'acl_resource';
        const AUTOINCREMENT = true;
        const PRIMARY_KEY   = 'id';
        const BINARY_PK     = false;
        const ENTITY_NAME   = 'acl_resource';
        const ENTITY_POOL   = 'entities';
        const CACHE_ENGINE  = 'redis';
        const SOURCE_ENGINE = null;
        const USING_LIMIT   = true;
        const USING_COUNT   = true;
    }
