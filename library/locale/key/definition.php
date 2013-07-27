<?php

    interface locale_key_definition {
        const NAME                = 'locale key';
        const TABLE               = 'locale_key';
        const AUTOINCREMENT       = true;
        const PRIMARY_KEY         = 'id';
        const BINARY_PK           = false;
        const ENTITY_NAME         = 'locale_key';
        const CACHE_ENGINE        = 'redis';
        const CACHE_ENGINE_READ   = 'master';
        const CACHE_ENGINE_WRITE  = 'master';
        const SOURCE_ENGINE       = null;
        const SOURCE_ENGINE_READ  = null;
        const SOURCE_ENGINE_WRITE = null;
        const USING_LIMIT         = true;
        const USING_COUNT         = true;
    }

