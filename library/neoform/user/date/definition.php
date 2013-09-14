<?php

    namespace neoform\user\date;

    /**
     * Entity definition interface
     */
    interface definition {

        const NAME          = 'user date';
        const TABLE         = 'user_date';
        const AUTOINCREMENT = false;
        const PRIMARY_KEY   = 'user_id';
        const ENTITY_NAME   = 'user\date';
        const CACHE_KEY     = 'user_date';
    }
