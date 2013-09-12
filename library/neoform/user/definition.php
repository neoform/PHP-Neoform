<?php

    namespace neoform\user;

    /**
     * Entity definition interface
     */
    interface definition {

        const NAME          = 'user';
        const TABLE         = 'user';
        const AUTOINCREMENT = true;
        const PRIMARY_KEY   = 'id';
        const ENTITY_NAME   = 'user';
    }
