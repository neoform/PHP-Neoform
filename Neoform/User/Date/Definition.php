<?php

    namespace Neoform\User\Date;

    /**
     * Entity definition interface
     */
    interface Definition {

        const NAME          = 'user date';
        const TABLE         = 'user_date';
        const AUTOINCREMENT = false;
        const PRIMARY_KEY   = 'user_id';
        const ENTITY_NAME   = 'Neoform\User\Date';
        const CACHE_KEY     = 'user_date';
    }
