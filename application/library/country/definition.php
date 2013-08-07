<?php

    /**
     * Entity definition interface
     */
    interface country_definition {

        const NAME          = 'country';
        const TABLE         = 'country';
        const AUTOINCREMENT = true;
        const PRIMARY_KEY   = 'id';
        const BINARY_PK     = false;
        const ENTITY_NAME   = 'country';
        const USING_LIMIT   = false;
        const USING_PAGINATED = true;
        const USING_COUNT   = false;
    }
