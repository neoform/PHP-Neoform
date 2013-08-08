<?php

    /**
     * Record Blob Model
     */
    class entity_record_blob_model extends entity_record_model {

        public function __get($k) {
            if (isset($this->vars[$k])) {
                return $this->vars[$k];
            } else {
                if (isset($this->vars[static::BLOB][$k])) {
                    return $this->vars[static::BLOB][$k];
                }
            }
        }
    }
