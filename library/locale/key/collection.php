<?php

	class locale_key_collection extends record_collection implements locale_key_definition {

		public function locale_key_message_collection() {
			return $this->_preload_one_to_many('locale_key_message', 'by_key');
		}

		public function locale_message_collection() {
			return $this->_preload_one_to_many('locale_message', 'by_parent');
		}

		public function locale_collection() {
			return $this->_preload_one_to_one('locale', 'locale');
		}

		public function locale_namespace_collection() {
			return $this->_preload_one_to_one('locale_namespace', 'namespace_id');
		}
	}
