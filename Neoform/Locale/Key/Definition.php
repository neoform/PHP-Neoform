<?php

    namespace Neoform\Locale\Key;

    /**
     * Entity definition interface
     */
    interface Definition {

        const NAME          = 'locale key';
        const TABLE         = 'locale_key';
        const AUTOINCREMENT = true;
        const PRIMARY_KEY   = 'id';
        const ENTITY_NAME   = 'Neoform\Locale\Key';
        const CACHE_KEY     = 'locale_key';
    }
