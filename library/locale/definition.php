<?php

    interface locale_definition {
        const NAME                = 'locale';
        const TABLE               = 'locale';
        const AUTOINCREMENT       = false;
        const PRIMARY_KEY         = 'iso2';
        const BINARY_PK           = false;
        const ENTITY_NAME         = 'locale';
        const CACHE_ENGINE        = 'redis';
        const CACHE_ENGINE_READ   = 'master';
        const CACHE_ENGINE_WRITE  = 'master';
        const SOURCE_ENGINE       = null;
        const SOURCE_ENGINE_READ  = null;
        const SOURCE_ENGINE_WRITE = null;
        const USING_LIMIT         = true;
        const USING_COUNT         = true;
    }