<?php

    namespace Neoform\User;

    /**
     * Entity definition interface
     */
    interface Definition {

        const NAME          = 'user';
        const TABLE         = 'user';
        const AUTOINCREMENT = true;
        const PRIMARY_KEY   = 'id';
        const ENTITY_NAME   = 'Neoform\User';
        const CACHE_KEY     = 'user';
    }
