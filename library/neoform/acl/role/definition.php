<?php

    namespace neoform\acl\role;

    /**
     * Entity definition interface
     */
    interface definition {

        const NAME          = 'acl role';
        const TABLE         = 'acl_role';
        const AUTOINCREMENT = true;
        const PRIMARY_KEY   = 'id';
        const ENTITY_NAME   = 'acl\role';
        const CACHE_KEY     = 'acl_role';
    }
