<?php

    namespace neoform\user\status;

    /**
     * Entity definition interface
     */
    interface definition {

        const NAME          = 'user status';
        const TABLE         = 'user_status';
        const AUTOINCREMENT = false;
        const PRIMARY_KEY   = 'id';
        const ENTITY_NAME   = 'user\status';
    }
