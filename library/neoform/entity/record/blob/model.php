<?php

    namespace neoform\entity\record\blob;

    use neoform\entity\record;

    /**
     * Record Blob Model
     */
    class model extends record\model {

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

