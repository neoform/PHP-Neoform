<?php

    namespace neoform\user\acl\role;

    /**
     * Entity definition interface
     */
    interface definition {

        const NAME        = 'user acl role link';
        const TABLE       = 'user_acl_role';
        const ENTITY_NAME = 'user\acl\role';
        const CACHE_KEY   = 'user_acl_role';
    }
