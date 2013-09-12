<?php

    namespace neoform\user\hashmethod;

    /**
     * Entity definition interface
     */
    interface definition {

        const NAME          = 'user hashmethod';
        const TABLE         = 'user_hashmethod';
        const AUTOINCREMENT = false;
        const PRIMARY_KEY   = 'id';
        const ENTITY_NAME   = 'user\hashmethod';
    }
