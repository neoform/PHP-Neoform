<?php

    /**
     * Entity definition interface
     */
    interface user_site_definition {

        const NAME                = 'user site link';
        const TABLE               = 'user_site';
        const ENTITY_NAME         = 'user_site';
        const CACHE_ENGINE        = 'redis';
        const CACHE_ENGINE_READ   = 'master';
        const CACHE_ENGINE_WRITE  = 'master';
        const SOURCE_ENGINE       = null;
        const SOURCE_ENGINE_READ  = null;
        const SOURCE_ENGINE_WRITE = null;
    }
