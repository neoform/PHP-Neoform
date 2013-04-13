<?php

    class sql_pdo extends pdo {

        /**
         * Get the name of the current driver being used by PDO (eg, mysql, pgsql)
         *
         * @return mixed
         */
        public function driver() {
            return $this->getAttribute(PDO::ATTR_DRIVER_NAME);
        }
    }