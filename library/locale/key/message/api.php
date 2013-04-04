<?php

    class locale_key_message_api {

        public static function update(array $info) {

            $input = new input_collection($info);

            self::_validate($input);

            if ($input->is_valid()) {

                try {
                    $message = new locale_key_message_model(
                        current(locale_key_message_dao::by_locale_key(
                            $input->locale->val(),
                            $input->key_id->val()
                        ))
                    );

                    $locale_key_message = locale_key_message_dao::update(
                        $message,
                        array(
                            'key_id'     => $input->key_id->val(),
                            'body'         => $input->body->val(),
                            'locale'     => $input->locale->val(),
                        )
                    );
                } catch (locale_key_message_exception $e) {

                    $locale_key_message = locale_key_message_dao::insert(array(
                        'key_id'     => $input->key_id->val(),
                        'body'         => $input->body->val(),
                        'locale'     => $input->locale->val(),
                    ));

                    $message = null;
                }

                // Not sure why i hard coded namespace_id: 1 here...
                locale_lib::flush_by_locale_namespace(
                    $locale_key_message->locale,
                    new locale_namespace_model(1)
                );

                return $message;
            }

            throw $input->exception();
        }

        public static function delete(locale_key_message_model $message) {
            return locale_key_message_dao::delete($message);
        }

        public static function _validate(input_collection $input) {
            $locales = [];
            foreach (locale_dao::all() as $locale) {
                $locales[] = $locale['iso2'];
            }

            $input->body->cast('string')->length(1, 255);
            $input->locale->cast('string')->tolower()->trim()->length(2)->in($locales);
            $input->key_id->cast('integer')->digit(0, 4294967295)->callback(function($key_id) {
                try {
                    $key_id->data('model', new locale_key_model($key_id->val()));
                } catch (locale_key_exception $e) {
                    $key_id->errors('invalid');
                }
            });
        }
    }
