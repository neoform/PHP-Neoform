<?php

    namespace neoform\acl\resource;

    /**
     * Entity definition interface
     */
    interface definition {

        const NAME          = 'acl resource';
        const TABLE         = 'acl_resource';
        const AUTOINCREMENT = true;
        const PRIMARY_KEY   = 'id';
        const ENTITY_NAME   = 'acl\resource';
        const CACHE_KEY     = 'acl_resource';
    }
