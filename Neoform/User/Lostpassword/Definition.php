<?php

    namespace Neoform\User\Lostpassword;

    /**
     * Entity definition interface
     */
    interface Definition {

        const NAME          = 'user lostpassword';
        const TABLE         = 'user_lostpassword';
        const AUTOINCREMENT = false;
        const PRIMARY_KEY   = 'hash';
        const ENTITY_NAME   = 'Neoform\User\Lostpassword';
        const CACHE_KEY     = 'user_lostpassword';
    }
