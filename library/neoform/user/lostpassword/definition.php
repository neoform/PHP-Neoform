<?php

    namespace neoform\user\lostpassword;

    /**
     * Entity definition interface
     */
    interface definition {

        const NAME          = 'user lostpassword';
        const TABLE         = 'user_lostpassword';
        const AUTOINCREMENT = false;
        const PRIMARY_KEY   = 'hash';
        const ENTITY_NAME   = 'user\lostpassword';
    }
