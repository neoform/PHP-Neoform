<?php

	class locale_key_api {

		public static function insert(array $info) {

			$input = new input_collection($info);

			self::_validate_insert($input);

			if ($input->is_valid()) {
                $locale_key = locale_key_dao::insert([
					'body'         => $input->body->val(),
					'locale'       => $input->locale->val(),
					'namespace_id' => $input->namespace_id->val(),
				]);
				locale_lib::flush_by_locale_namespace($locale_key->locale, $locale_key->locale_namespace());
				return $locale_key;
			}
			throw $input->exception();
		}

		public static function update(locale_key_model $locale_key, array $info, $crush=false) {

			$input = new input_collection($info);

			self::_validate_update($locale_key, $input);

			if ($input->is_valid()) {
				$updated_locale_key = locale_key_dao::update(
					$locale_key,
					$input->vals(
						[
							'body',
							'locale',
							'namespace_id',
						],
						$crush
					)
				);

				locale_lib::flush_by_locale_namespace($locale_key->locale, $locale_key->locale_namespace());
				locale_lib::flush_by_locale_namespace($updated_locale_key->locale, $updated_locale_key->locale_namespace());

				return $updated_locale_key;
			}
			throw $input->exception();
		}

		public static function delete(locale_key_model $locale_key) {
			$return = locale_key_dao::delete($locale_key);
			locale_lib::flush_by_locale_namespace($locale_key->locale, $locale_key->locale_namespace());
			return $return;
		}

		public static function _validate_insert(input_collection $input) {

			// body
			$input->body->cast('string')->length(1, 255);

			// locale
			$input->locale->cast('string')->length(1, 2)->callback(function($locale){
				try {
					$locale->data('model', new locale_model($locale->val()));
				} catch (locale_exception $e) {
					$locale->errors($e->getMessage());
				}
			});

			// namespace_id
			$input->namespace_id->cast('int')->digit(0, 4294967295)->callback(function($namespace_id){
				try {
					$namespace_id->data('model', new locale_namespace_model($namespace_id->val()));
				} catch (locale_namespace_exception $e) {
					$namespace_id->errors($e->getMessage());
				}
			});
		}

		public static function _validate_update(locale_key_model $locale_key, input_collection $input) {

			// body
			$input->body->cast('string')->optional()->length(1, 255);

			// locale
			$input->locale->cast('string')->optional()->length(1, 2)->callback(function($locale){
				try {
					$locale->data('model', new locale_model($locale->val()));
				} catch (locale_exception $e) {
					$locale->errors($e->getMessage());
				}
			});

			// namespace_id
			$input->namespace_id->cast('int')->optional()->digit(0, 4294967295)->callback(function($namespace_id){
				try {
					$namespace_id->data('model', new locale_namespace_model($namespace_id->val()));
				} catch (locale_namespace_exception $e) {
					$namespace_id->errors($e->getMessage());
				}
			});
		}

	}
