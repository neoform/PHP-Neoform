<?php

    namespace Neoform\Acl\Group;

    /**
     * Entity definition interface
     */
    interface Definition {

        const NAME          = 'acl group';
        const TABLE         = 'acl_group';
        const AUTOINCREMENT = true;
        const PRIMARY_KEY   = 'id';
        const ENTITY_NAME   = 'Neoform\Acl\Group';
        const CACHE_KEY     = 'acl_group';
    }
