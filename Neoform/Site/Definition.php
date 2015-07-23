<?php

    namespace Neoform\Site;

    /**
     * Entity definition interface
     */
    interface Definition {

        const NAME          = 'site';
        const TABLE         = 'site';
        const AUTOINCREMENT = false;
        const PRIMARY_KEY   = 'id';
        const ENTITY_NAME   = 'Neoform\Site';
        const CACHE_KEY     = 'site';
    }
