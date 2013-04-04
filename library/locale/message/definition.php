<?php

    interface locale_message_definition {
        const NAME          = 'locale message';
        const TABLE         = 'locale_message';
        const AUTOINCREMENT = true;
        const PRIMARY_KEY   = 'id';
        const BINARY_PK     = false;
        const ENTITY_NAME   = 'locale_message';
        const ENTITY_POOL   = 'entities';
        const CACHE_ENGINE  = 'memcache';
    }