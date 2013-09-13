<?php

    namespace neoform\site;

    use neoform\input;
    use neoform\entity;

    class api {

        public static function insert(array $info) {

            $input = new input\collection($info);

            self::_validate_insert($input);

            if ($input->is_valid()) {
                return entity::dao('site')->insert([
                    'id'   => $input->id->val(),
                    'name' => $input->name->val(),
                ]);
            }
            throw $input->exception();
        }

        public static function update(model $site, array $info, $crush=false) {

            $input = new input\collection($info);

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

        public static function delete(model $site) {
            return entity::dao('site')->delete($site);
        }

        public static function _validate_insert(input\collection $input) {

            // id
            $input->id->cast('int')->digit(0, 65535)->callback(function($id) {
                if (entity::dao('site')->record($id->val())) {
                    $id->errors('already in use');
                }
            });

            // name
            $input->name->cast('string')->length(1, 64)->callback(function($name) {
                if (entity::dao('site')->by_name($name->val())) {
                    $name->errors('already in use');
                }
            });
        }

        public static function _validate_update(model $site, input\collection $input) {

            // id
            $input->id->cast('int')->optional()->digit(0, 65535)->callback(function($id) use ($site) {
                $site_info = entity::dao('site')->record($id->val());
                if ($site_info && (int) $site_info['id'] !== $site->id) {
                    $id->errors('already in use');
                }
            });

            // name
            $input->name->cast('string')->optional()->length(1, 64)->callback(function($name) use ($site) {
                $id_arr = entity::dao('site')->by_name($name->val());
                if (is_array($id_arr) && $id_arr && (int) current($id_arr) !== $site->id) {
                    $name->errors('already in use');
                }
            });
        }
    }
