<?php

    /**
     * Entity definition interface
     */
    interface city_definition {

        const NAME            = 'city';
        const TABLE           = 'city';
        const AUTOINCREMENT   = true;
        const PRIMARY_KEY     = 'id';
        const BINARY_PK       = false;
        const ENTITY_NAME     = 'city';
        const USING_LIMIT     = false;
        const USING_PAGINATED = true;
        const USING_COUNT     = false;
    }
