<?php

    interface locale_namespace_definition {
        const NAME          = 'locale namespace';
        const TABLE         = 'locale_namespace';
        const AUTOINCREMENT = true;
        const PRIMARY_KEY   = 'id';
        const BINARY_PK     = false;
        const ENTITY_NAME   = 'locale_namespace';
        const ENTITY_POOL   = 'entities';
        const CACHE_ENGINE  = 'redis';
    }