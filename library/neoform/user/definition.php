<?php

    namespace neoform;

    /**
     * Entity definition interface
     */
    interface user_definition {

        const NAME          = 'user';
        const TABLE         = 'user';
        const AUTOINCREMENT = true;
        const PRIMARY_KEY   = 'id';
        const ENTITY_NAME   = 'user';
    }