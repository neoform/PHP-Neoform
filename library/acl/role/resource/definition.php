<?php

    /**
     * Entity definition interface
     */
    interface acl_role_resource_definition {

        const NAME                = 'acl role resource link';
        const TABLE               = 'acl_role_resource';
        const ENTITY_NAME         = 'acl_role_resource';
        const CACHE_ENGINE        = 'redis';
        const CACHE_ENGINE_READ   = 'master';
        const CACHE_ENGINE_WRITE  = 'master';
        const SOURCE_ENGINE       = null;
        const SOURCE_ENGINE_READ  = null;
        const SOURCE_ENGINE_WRITE = null;
    }
