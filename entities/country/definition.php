<?php

    /**
     * Entity definition interface
     */
    interface country_definition {

        const NAME          = 'country';
        const TABLE         = 'country';
        const AUTOINCREMENT = true;
        const PRIMARY_KEY   = 'id';
        const BINARY_PK     = false;
        const ENTITY_NAME   = 'country';
        const ENTITY_POOL   = 'entities';
        const CACHE_ENGINE  = 'redis';
    }
