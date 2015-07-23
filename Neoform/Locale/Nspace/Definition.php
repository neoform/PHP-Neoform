<?php

    namespace Neoform\Locale\Nspace;

    /**
     * Entity definition interface
     */
    interface Definition {

        const NAME          = 'locale namespace';
        const TABLE         = 'locale_namespace';
        const AUTOINCREMENT = true;
        const PRIMARY_KEY   = 'id';
        const ENTITY_NAME   = 'Neoform\Locale\Nspace';
        const CACHE_KEY     = 'locale_namespace';
    }
