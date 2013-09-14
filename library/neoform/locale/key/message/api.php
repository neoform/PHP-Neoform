<?php

    namespace neoform\locale\key\message;

    use neoform\input;
    use neoform\entity;
    use neoform\locale;

    class api {

        public static function update(array $info) {

            $input = new input\collection($info);

            self::_validate($input);

            if ($input->is_valid()) {

                try {
                    $message = new model(
                        current(entity::dao('locale\key\message')->by_locale_key(
                            $input->locale->val(),
                            $input->key_id->val()
                        ))
                    );
                    $locale_key_message = entity::dao('locale\key\message')->update(
                        $message,
                        array(
                             'key_id' => $input->key_id->val(),
                             'body'   => $input->body->val(),
                             'locale' => $input->locale->val(),
                        )
                    );
                } catch (exception $e) {

                    $locale_key_message = entity::dao('locale\key\message')->insert(array(
                        'key_id' => $input->key_id->val(),
                        'body'   => $input->body->val(),
                        'locale' => $input->locale->val(),
                    ));

                    $message = null;
                }

                locale\lib::flush_by_locale_namespace(
                    $locale_key_message->locale,
                    $locale_key_message->locale_key()->locale_namespace()
                );

                return $message;
            }

            throw $input->exception();
        }

        public static function delete(model $locale_key_message) {
            return entity::dao('locale\key\message')->delete($locale_key_message);
        }

        public static function _validate(input\collection $input) {
            $locales = [];
            foreach (entity::dao('locale')->all() as $locale) {
                $locales[] = $locale['iso2'];
            }

            $input->body->cast('string')->length(1, 255);
            $input->locale->cast('string')->tolower()->trim()->length(2)->in($locales);
            $input->key_id->cast('integer')->digit(0, 4294967295)->callback(function($key_id) {
                try {
                    $key_id->data('model', new locale\key\model($key_id->val()));
                } catch (locale\key\exception $e) {
                    $key_id->errors('invalid');
                }
            });
        }
    }
