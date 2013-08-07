<?php

    /**
     * Entity definition interface
     */
    interface user_lostpassword_definition {

        const NAME          = 'user lostpassword';
        const TABLE         = 'user_lostpassword';
        const AUTOINCREMENT = false;
        const PRIMARY_KEY   = 'hash';
        const BINARY_PK     = false;
        const ENTITY_NAME   = 'user_lostpassword';
        const USING_LIMIT   = false;
        const USING_COUNT   = false;
    }
