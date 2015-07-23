<?php

    namespace Neoform\Auth;

    /**
     * Entity definition interface
     */
    interface Definition {

        const NAME          = 'auth';
        const TABLE         = 'auth';
        const AUTOINCREMENT = false;
        const PRIMARY_KEY   = 'hash';
        const ENTITY_NAME   = 'Neoform\Auth';
        const CACHE_KEY     = 'auth';
    }
