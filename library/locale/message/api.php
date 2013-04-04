<?php

	class locale_message_api {

		public static function insert(array $info) {

			$input = new input_collection($info);

			self::_validate_insert($input);

			if ($input->is_valid()) {
				return locale_message_dao::insert(array(
					'parent_id' => $input->parent_id->val(),
					'body'      => $input->body->val(),
					'locale'    => $input->locale->val(),
					'namespace' => $input->namespace->val(),
				));
			}
			throw $input->exception();
		}

		public static function update(locale_message_model $locale_message, array $info, $crush=false) {

			$input = new input_collection($info);

			self::_validate_update($locale_message, $input);

			if ($input->is_valid()) {
				return locale_message_dao::update(
					$locale_message,
					$input->vals(
						array(
							'parent_id',
							'body',
							'locale',
							'namespace',
						),
						$crush
					)
				);
			}
			throw $input->exception();
		}

		public static function delete(locale_message_model $locale_message) {
			return locale_message_dao::delete($locale_message);
		}

		public static function _validate_insert(input_collection $input) {

			// parent_id
			$input->parent_id->cast('int')->digit(0, 4294967295)->callback(function($parent_id){
				try {
					$parent_id->data('model', new locale_key_model($parent_id->val()));
				} catch (locale_key_exception $e) {
					$parent_id->errors($e->getMessage());
				}
			});

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

			// namespace
			$input->namespace->cast('int')->digit(0, 4294967295)->callback(function($namespace){
				try {
					$namespace->data('model', new locale_namespace_model($namespace->val()));
				} catch (locale_namespace_exception $e) {
					$namespace->errors($e->getMessage());
				}
			});
		}

		public static function _validate_update(locale_message_model $locale_message, input_collection $input) {

			// parent_id
			$input->parent_id->cast('int')->optional()->digit(0, 4294967295)->callback(function($parent_id){
				try {
					$parent_id->data('model', new locale_key_model($parent_id->val()));
				} catch (locale_key_exception $e) {
					$parent_id->errors($e->getMessage());
				}
			});

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

			// namespace
			$input->namespace->cast('int')->optional()->digit(0, 4294967295)->callback(function($namespace){
				try {
					$namespace->data('model', new locale_namespace_model($namespace->val()));
				} catch (locale_namespace_exception $e) {
					$namespace->errors($e->getMessage());
				}
			});
		}

	}
