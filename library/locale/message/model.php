<?php

	/**
	* Locale Message Model
	*
	* @exception locale_message_exception
	* @var int $id
	* @var int $parent_id
	* @var string $body
	* @var string $locale
	* @var int $namespace
	*/
	class locale_message_model extends record_model implements locale_message_definition {

		public function __get($k) {

			if (isset($this->vars[$k])) {
				switch ($k) {
					// integers
					case 'id':
					case 'parent_id':
					case 'namespace':
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

		public function locale(locale_model $locale=null) {
			return $locale !== null ? ($this->_vars['locale'] = $locale) : $this->_model('locale', $this->vars['locale'], 'locale_model');
		}

		public function locale_namespace(locale_namespace_model $locale_namespace=null) {
			return $locale_namespace !== null ? ($this->_vars['locale_namespace'] = $locale_namespace) : $this->_model('locale_namespace', $this->vars['namespace'], 'locale_namespace_model');
		}

		public function locale_key(locale_key_model $locale_key=null) {
			return $locale_key !== null ? ($this->_vars['locale_key'] = $locale_key) : $this->_model('locale_key', $this->vars['parent_id'], 'locale_key_model');
		}
	}
