<?php

    /**
     * Entity definition interface
     */
    interface acl_role_definition {

        const NAME          = 'acl role';
        const TABLE         = 'acl_role';
        const AUTOINCREMENT = true;
        const PRIMARY_KEY   = 'id';
        const BINARY_PK     = false;
        const ENTITY_NAME   = 'acl_role';
        const USING_LIMIT   = true;
        const USING_PAGINATED = true;
        const USING_COUNT   = true;
    }
