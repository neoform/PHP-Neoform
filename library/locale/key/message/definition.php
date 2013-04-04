<?php

    interface locale_key_message_definition {
        const NAME          = 'locale key message';
        const TABLE         = 'locale_key_message';
        const AUTOINCREMENT = true;
        const PRIMARY_KEY   = 'id';
        const BINARY_PK     = false;
        const ENTITY_NAME   = 'locale_key_message';
        const ENTITY_POOL   = 'entities';
        const CACHE_ENGINE  = 'memcache';
    }