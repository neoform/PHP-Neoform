<?php

    namespace neoform;

    class type_mapping {
        protected $id;
        protected $name;

        public function __construct($id, $name) {
            $this->id     = $id;
            $this->name = $name;
        }

        public function __get($k) {
            switch ($k) {
                case 'id':
                    return (int) $this->$k;

                case 'name':
                    return (string) $this->$k;
            }
        }

        public function __tostring() {
            return (string) $this->name;
        }
    }