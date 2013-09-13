<?php

    namespace neoform\locale\nspace;

    /**
     * Entity definition interface
     */
    interface definition {

        const NAME          = 'locale namespace';
        const TABLE         = 'locale_namespace';
        const AUTOINCREMENT = true;
        const PRIMARY_KEY   = 'id';
        const ENTITY_NAME   = 'locale\nspace';
        const CACHE_KEY     = 'locale_namespace';
    }
