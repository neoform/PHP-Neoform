<?php

    namespace Neoform\User\Status;

    /**
     * Entity definition interface
     */
    interface Definition {

        const NAME          = 'user status';
        const TABLE         = 'user_status';
        const AUTOINCREMENT = false;
        const PRIMARY_KEY   = 'id';
        const ENTITY_NAME   = 'Neoform\User\Status';
        const CACHE_KEY     = 'user_status';
    }
