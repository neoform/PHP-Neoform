<?php

    namespace neoform\acl\group;

    /**
     * Entity definition interface
     */
    interface definition {

        const NAME          = 'acl group';
        const TABLE         = 'acl_group';
        const AUTOINCREMENT = false;
        const PRIMARY_KEY   = 'id';
        const ENTITY_NAME   = 'acl\group';
    }
