<?php

    class locale_message_collection extends record_collection implements locale_message_definition {

        public function locale() {
            return $this->_preload_one_to_one('locale', 'locale');
        }

        public function locale_namespace() {
            return $this->_preload_one_to_one('locale_namespace', 'namespace');
        }

        public function locale_key() {
            return $this->_preload_one_to_one('locale_key', 'parent_id');
        }

    }
