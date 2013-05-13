<?php

    /**
     * Entity definition interface
     */
    interface user_site_definition {

        const NAME          = 'user site link';
        const TABLE         = 'user_site';
        const ENTITY_NAME   = 'user_site';
        const ENTITY_POOL   = 'entities';
        const CACHE_ENGINE  = 'redis';
        const SOURCE_ENGINE = null;
    }
