<?php

    /**
     * Entity definition interface
     */
    interface acl_group_role_definition {

        const NAME         = 'acl group role link';
        const TABLE        = 'acl_group_role';
        const ENTITY_NAME  = 'acl_group_role';
        const ENTITY_POOL  = 'entities';
        const CACHE_ENGINE = 'redis';
    }
