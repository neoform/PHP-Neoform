<?php

	/**
	* Locale Key Message Model
	*
	* @exception locale_key_message_exception
	* @var int $id
	* @var int $key_id
	* @var string $body
	* @var string $locale
	*/
	class locale_key_message_model extends record_model implements locale_key_message_definition {

		public function __get($k) {

			if (isset($this->vars[$k])) {
				switch ($k) {
					// integers
					case 'id':
					case 'key_id':
						return (int) $this->vars[$k];

					// strings
					case 'body':
					case 'locale':
						return (string) $this->vars[$k];

					default:
						return $this->vars[$k];
				}
			}

		}

		public function locale_key(locale_key_model $locale_key=null) {
			return $locale_key !== null ? ($this->_vars['locale_key'] = $locale_key) : $this->_model('locale_key', $this->vars['key_id'], 'locale_key_model');
		}

		public function locale(locale_model $locale=null) {
			return $locale !== null ? ($this->_vars['locale'] = $locale) : $this->_model('locale', $this->vars['locale'], 'locale_model');
		}
	}
