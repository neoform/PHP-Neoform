<?php

    /**
     * Entity definition interface
     */
    interface auth_definition {

        const NAME          = 'auth';
        const TABLE         = 'auth';
        const AUTOINCREMENT = false;
        const PRIMARY_KEY   = 'hash';
        const BINARY_PK     = true;
        const ENTITY_NAME   = 'auth';
        const USING_LIMIT   = false;
        const USING_PAGINATED = true;
        const USING_COUNT   = false;
    }
