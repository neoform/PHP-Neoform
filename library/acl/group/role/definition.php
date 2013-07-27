<?php

    /**
     * Entity definition interface
     */
    interface acl_group_role_definition {

        const NAME                = 'acl group role link';
        const TABLE               = 'acl_group_role';
        const ENTITY_NAME         = 'acl_group_role';
        const CACHE_ENGINE        = 'redis';
        const CACHE_ENGINE_READ   = 'master';
        const CACHE_ENGINE_WRITE  = 'master';
        const SOURCE_ENGINE       = null;
        const SOURCE_ENGINE_READ  = null;
        const SOURCE_ENGINE_WRITE = null;
    }
