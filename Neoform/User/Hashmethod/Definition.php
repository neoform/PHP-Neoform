<?php

    namespace Neoform\User\Hashmethod;

    /**
     * Entity definition interface
     */
    interface Definition {

        const NAME          = 'user hashmethod';
        const TABLE         = 'user_hashmethod';
        const AUTOINCREMENT = false;
        const PRIMARY_KEY   = 'id';
        const ENTITY_NAME   = 'Neoform\User\Hashmethod';
        const CACHE_KEY     = 'user_hashmethod';
    }
