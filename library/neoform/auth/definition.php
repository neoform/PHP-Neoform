<?php

    namespace neoform\auth;

    /**
     * Entity definition interface
     */
    interface definition {

        const NAME          = 'auth';
        const TABLE         = 'auth';
        const AUTOINCREMENT = false;
        const PRIMARY_KEY   = 'hash';
        const ENTITY_NAME   = 'auth';
        const CACHE_KEY     = 'auth';
    }
