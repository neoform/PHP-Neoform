<?php

    namespace neoform\acl\group;

    /**
     * Entity definition interface
     */
    interface definition {

        const NAME          = 'acl group';
        const TABLE         = 'acl_group';
        const AUTOINCREMENT = true;
        const PRIMARY_KEY   = 'id';
        const ENTITY_NAME   = 'acl\group';
        const CACHE_KEY     = 'acl_group';
    }
