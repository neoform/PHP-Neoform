<?php

    namespace Neoform\Acl\Group\User;

    /**
     * Entity definition interface
     */
    interface Definition {

        const NAME        = 'acl group user link';
        const TABLE       = 'acl_group_user';
        const ENTITY_NAME = 'Neoform\Acl\Group\User';
        const CACHE_KEY   = 'acl_group_user';
    }
