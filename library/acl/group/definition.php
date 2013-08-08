<?php

    /**
     * Entity definition interface
     */
    interface acl_group_definition {

        const NAME          = 'acl group';
        const TABLE         = 'acl_group';
        const AUTOINCREMENT = false;
        const PRIMARY_KEY   = 'id';
        const BINARY_PK     = false;
        const ENTITY_NAME   = 'acl_group';
        const USING_LIMIT   = false;
        const USING_PAGINATED = true;
        const USING_COUNT   = false;
    }
