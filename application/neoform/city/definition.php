<?php

    namespace neoform\city;

    /**
     * Entity definition interface
     */
    interface definition {

        const NAME          = 'city';
        const TABLE         = 'city';
        const AUTOINCREMENT = true;
        const PRIMARY_KEY   = 'id';
        const ENTITY_NAME   = 'city';
        const CACHE_KEY     = 'city';
    }
