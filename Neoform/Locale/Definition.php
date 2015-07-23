<?php

    namespace Neoform\Locale;

    /**
     * Entity definition interface
     */
    interface Definition {

        const NAME          = 'locale';
        const TABLE         = 'locale';
        const AUTOINCREMENT = false;
        const PRIMARY_KEY   = 'iso2';
        const ENTITY_NAME   = 'Neoform\Locale';
        const CACHE_KEY     = 'locale';
    }
