<?php

    class locale_key_message_collection extends record_collection implements locale_key_message_definition {

        public function locale_key_collection() {
            return $this->_preload_one_to_one('locale_key', 'key_id');
        }

        public function locale_collection() {
            return $this->_preload_one_to_one('locale', 'locale');
        }
    }
