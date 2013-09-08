<?php

    namespace neoform;

    /**
     * Entity definition interface
     */
    interface user_lostpassword_definition {

        const NAME          = 'user lostpassword';
        const TABLE         = 'user_lostpassword';
        const AUTOINCREMENT = false;
        const PRIMARY_KEY   = 'hash';
        const ENTITY_NAME   = 'user_lostpassword';
    }
