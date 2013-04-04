<?php

	class locale_namespace_collection extends record_collection implements locale_namespace_definition {

		public function locale_key_collection() {
			return $this->_preload_one_to_many('locale_key', 'by_namespace');
		}

		public function locale_message_collection() {
			return $this->_preload_one_to_many('locale_message', 'by_namespace');
		}
	}
