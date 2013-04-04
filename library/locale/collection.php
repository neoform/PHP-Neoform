<?php

    class locale_collection extends record_collection implements locale_definition {

        public function locale_key_collection() {
            return $this->_preload_one_to_many('locale_key', 'by_locale');
        }

        public function locale_key_message_collection() {
            return $this->_preload_one_to_many('locale_key_message', 'by_locale');
        }

        public function locale_message_collection() {
            return $this->_preload_one_to_many('locale_message', 'by_locale');
        }

    }
