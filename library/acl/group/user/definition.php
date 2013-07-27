<?php

    /**
     * Entity definition interface
     */
    interface acl_group_user_definition {

        const NAME                = 'acl group user link';
        const TABLE               = 'acl_group_user';
        const ENTITY_NAME         = 'acl_group_user';
        const CACHE_ENGINE        = 'redis';
        const CACHE_ENGINE_READ   = 'master';
        const CACHE_ENGINE_WRITE  = 'master';
        const SOURCE_ENGINE       = null;
        const SOURCE_ENGINE_READ  = null;
        const SOURCE_ENGINE_WRITE = null;
    }
