<?php

    namespace neoform;

    class site_api {

        public static function insert(array $info) {

            $input = new input_collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return entity::dao('site')->insert([
                    'id'   => $input->id->val(),
                    'name' => $input->name->val(),
                ]);
            }
            throw $input->exception();
        }

        public static function update(site_model $site, array $info, $crush=false) {

            $input = new input_collection($info);

            self::_validate_update($site, $input);

            if ($input->is_valid()) {
                return entity::dao('site')->update(
                    $site,
                    $input->vals(
                        [
                            'id',
                            'name',
                        ],
                        $crush
                    )
                );
            }
            throw $input->exception();
        }

        public static function _validate_insert(input_collection $input) {

            // id
            $input->id->cast('int')->digit(0, 65535);

            // name
            $input->name->cast('string')->length(1, 64)->callback(function($name) {
                $id_arr = entity::dao('site')->by_name($name->val());
                if (\is_array($id_arr) && $id_arr) {
                    $name->errors('already in use');
                }
            });
        }

        public static function _validate_update(site_model $site, input_collection $input) {

            // id
            $input->id->cast('int')->optional()->digit(0, 65535);

            // name
            $input->name->cast('string')->optional()->length(1, 64)->callback(function($name) use ($site) {
                $id_arr = entity::dao('site')->by_name($name->val());
                if (\is_array($id_arr) && $id_arr && (int) \current($id_arr) !== $site->id) {
                    $name->errors('already in use');
                }
            });
        }

    }
