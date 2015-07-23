<?php

    namespace Neoform\Acl\Resource;

    /**
     * Entity definition interface
     */
    interface Definition {

        const NAME          = 'acl resource';
        const TABLE         = 'acl_resource';
        const AUTOINCREMENT = true;
        const PRIMARY_KEY   = 'id';
        const ENTITY_NAME   = 'Neoform\Acl\Resource';
        const CACHE_KEY     = 'acl_resource';
    }
