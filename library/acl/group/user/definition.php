<?php

    /**
     * Entity definition interface
     */
    interface acl_group_user_definition {

        const NAME         = 'acl group user link';
        const TABLE        = 'acl_group_user';
        const ENTITY_NAME  = 'acl_group_user';
        const ENTITY_POOL  = 'entities';
        const CACHE_ENGINE = 'redis';
    }
