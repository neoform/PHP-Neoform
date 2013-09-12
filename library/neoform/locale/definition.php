<?php

    namespace neoform\locale;

    /**
     * Entity definition interface
     */
    interface definition {

        const NAME          = 'locale';
        const TABLE         = 'locale';
        const AUTOINCREMENT = false;
        const PRIMARY_KEY   = 'iso2';
        const ENTITY_NAME   = 'locale';
    }
