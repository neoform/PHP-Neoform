<?php

    interface locale_definition {
        const NAME          = 'locale';
        const TABLE         = 'locale';
        const AUTOINCREMENT = false;
        const PRIMARY_KEY   = 'iso2';
        const BINARY_PK     = false;
        const ENTITY_NAME   = 'locale';
        const ENTITY_POOL   = 'entities';
        const CACHE_ENGINE  = 'redis';
        const SOURCE_ENGINE = null;
        const USING_LIMIT   = true;
        const USING_COUNT   = true;
    }