<?php

    /**
     * Entity definition interface
     */
    interface auth_definition {

        const NAME          = 'auth';
        const TABLE         = 'auth';
        const AUTOINCREMENT = false;
        const PRIMARY_KEY   = 'hash';
        const ENTITY_NAME   = 'auth';
    }
