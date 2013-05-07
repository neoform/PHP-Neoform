<?php

    interface locale_key_definition {
        const NAME          = 'locale key';
        const TABLE         = 'locale_key';
        const AUTOINCREMENT = true;
        const PRIMARY_KEY   = 'id';
        const BINARY_PK     = false;
        const ENTITY_NAME   = 'locale_key';
        const ENTITY_POOL   = 'entities';
        const CACHE_ENGINE  = 'redis';
    }

