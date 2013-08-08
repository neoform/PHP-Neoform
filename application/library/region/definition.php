<?php

    /**
     * Entity definition interface
     */
    interface region_definition {

        const NAME          = 'region';
        const TABLE         = 'region';
        const AUTOINCREMENT = true;
        const PRIMARY_KEY   = 'id';
        const BINARY_PK     = false;
        const ENTITY_NAME   = 'region';
        const USING_LIMIT   = false;
        const USING_PAGINATED = true;
        const USING_COUNT   = false;
    }
