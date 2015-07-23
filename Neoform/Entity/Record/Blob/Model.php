<?php

    namespace Neoform\Entity\Record\Blob;

    use Neoform\Entity\Record;

    /**
     * Record Blob Model
     */
    class Model extends Record\Model {

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

