<?php

    namespace neoform\locale\key;

    /**
     * Entity definition interface
     */
    interface definition {

        const NAME          = 'locale key';
        const TABLE         = 'locale_key';
        const AUTOINCREMENT = true;
        const PRIMARY_KEY   = 'id';
        const ENTITY_NAME   = 'locale\key';
        const CACHE_KEY     = 'locale_key';
    }
