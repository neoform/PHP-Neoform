<?php

    namespace Neoform\Locale\Key\Message;

    /**
     * Entity definition interface
     */
    interface Definition {

        const NAME          = 'locale key message';
        const TABLE         = 'locale_key_message';
        const AUTOINCREMENT = true;
        const PRIMARY_KEY   = 'id';
        const ENTITY_NAME   = 'Neoform\Locale\Key\Message';
        const CACHE_KEY     = 'locale_key_message';
    }
