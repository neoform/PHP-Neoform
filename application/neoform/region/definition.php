<?php

    namespace neoform\region;

    /**
     * Entity definition interface
     */
    interface definition {

        const NAME          = 'region';
        const TABLE         = 'region';
        const AUTOINCREMENT = true;
        const PRIMARY_KEY   = 'id';
        const ENTITY_NAME   = 'region';
        const CACHE_KEY     = 'region';
    }
