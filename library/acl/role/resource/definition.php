<?php

    /**
     * Entity definition interface
     */
    interface acl_role_resource_definition {

        const NAME         = 'acl role resource link';
        const TABLE        = 'acl_role_resource';
        const ENTITY_NAME  = 'acl_role_resource';
        const ENTITY_POOL  = 'entities';
        const CACHE_ENGINE = 'redis';
    }
