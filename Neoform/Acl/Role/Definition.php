<?php

    namespace Neoform\Acl\Role;

    /**
     * Entity definition interface
     */
    interface Definition {

        const NAME          = 'acl role';
        const TABLE         = 'acl_role';
        const AUTOINCREMENT = true;
        const PRIMARY_KEY   = 'id';
        const ENTITY_NAME   = 'Neoform\Acl\Role';
        const CACHE_KEY     = 'acl_role';
    }
