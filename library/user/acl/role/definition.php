<?php

    /**
     * Entity definition interface
     */
    interface user_acl_role_definition {

        const NAME                = 'user acl role link';
        const TABLE               = 'user_acl_role';
        const ENTITY_NAME         = 'user_acl_role';
        const CACHE_ENGINE        = 'redis';
        const CACHE_ENGINE_READ   = 'master';
        const CACHE_ENGINE_WRITE  = 'master';
        const SOURCE_ENGINE       = null;
        const SOURCE_ENGINE_READ  = null;
        const SOURCE_ENGINE_WRITE = null;
    }
