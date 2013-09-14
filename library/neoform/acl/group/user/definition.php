<?php

    namespace neoform\acl\group\user;

    /**
     * Entity definition interface
     */
    interface definition {

        const NAME        = 'acl group user link';
        const TABLE       = 'acl_group_user';
        const ENTITY_NAME = 'acl\group\user';
        const CACHE_KEY   = 'acl_group_user';
    }
