<?php

    namespace neoform;

    /**
     * Entity definition interface
     */
    interface acl_resource_definition {

        const NAME          = 'acl resource';
        const TABLE         = 'acl_resource';
        const AUTOINCREMENT = true;
        const PRIMARY_KEY   = 'id';
        const ENTITY_NAME   = 'acl_resource';
    }
