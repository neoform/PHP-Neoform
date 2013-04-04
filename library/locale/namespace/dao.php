<?php

	class locale_namespace_dao extends record_dao implements locale_namespace_definition {

		const BY_NAME = 'by_name';

		// READS

		public static function by_name($name) {
			return self::_by_fields(
				self::BY_NAME,
				array(
					'name' => (string) $name,
				)
			);
		}

		// WRITES

		public static function insert(array $info) {
			$return = parent::_insert($info);

			// Delete Cache
			// BY_NAME
			if (array_key_exists('name', $info)) {
				parent::_cache_delete(
					parent::_build_key(
						self::BY_NAME,
						array(
							'name' => (string) $info['name'],
						)
					)
				);
			}

			return $return;
		}

		public static function inserts(array $infos) {
			$return = parent::_inserts($infos);

			// Delete Cache
			foreach ($infos as $info) {
				// BY_NAME
				if (array_key_exists('name', $info)) {
					parent::_cache_delete(
						parent::_build_key(
							self::BY_NAME,
							array(
								'name' => (string) $info['name'],
							)
						)
					);
				}

			}

			return $return;
		}

		public static function update(locale_namespace_model $locale_namespace, array $info) {
			$updated_model = parent::_update($locale_namespace, $info);

			// Delete Cache
			// BY_NAME
			if (array_key_exists('name', $info)) {
				parent::_cache_delete(
					parent::_build_key(
						self::BY_NAME,
						array(
							'name' => (string) $locale_namespace->name,
						)
					)
				);
				parent::_cache_delete(
					parent::_build_key(
						self::BY_NAME,
						array(
							'name' => (string) $info['name'],
						)
					)
				);
			}

			return $updated_model;
		}

		public static function delete(locale_namespace_model $locale_namespace) {
			$return = parent::_delete($locale_namespace);

			// Delete Cache
			// BY_NAME
			parent::_cache_delete(
				parent::_build_key(
					self::BY_NAME,
					array(
						'name' => (string) $locale_namespace->name,
					)
				)
			);

			return $return;
		}

		public static function deletes(locale_namespace_collection $locale_namespaces) {
			$return = parent::_deletes($locale_namespaces);

			// Delete Cache
			foreach ($locale_namespaces as $locale_namespace) {
				// BY_NAME
				parent::_cache_delete(
					parent::_build_key(
						self::BY_NAME,
						array(
							'name' => (string) $locale_namespace->name,
						)
					)
				);

			}

			return $return;
		}

	}
