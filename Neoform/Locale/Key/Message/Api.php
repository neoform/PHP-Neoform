<?php

    namespace Neoform\Locale\Key\Message;

    use Neoform\Input;
    use Neoform\Entity;
    use Neoform\Locale;

    class Api {

        public static function update(array $info) {

            $input = new Input\Collection($info);

            self::_validate($input);

            if ($input->is_valid()) {

                try {
                    $message = new Model(
                        current(Entity::dao('Neoform\Locale\Key\Message')->by_locale_key(
                            $input->locale->val(),
                            $input->key_id->val()
                        ))
                    );
                    $locale_key_message = Entity::dao('Neoform\Locale\Key\Message')->update(
                        $message,
                        array(
                             'key_id' => $input->key_id->val(),
                             'body'   => $input->body->val(),
                             'locale' => $input->locale->val(),
                        )
                    );
                } catch (Exception $e) {

                    $locale_key_message = Entity::dao('Neoform\Locale\Key\Message')->insert(array(
                        'key_id' => $input->key_id->val(),
                        'body'   => $input->body->val(),
                        'locale' => $input->locale->val(),
                    ));

                    $message = null;
                }

                Locale\Lib::flushByLocaleNamespace(
                    $locale_key_message->locale,
                    $locale_key_message->locale_key()->locale_namespace()
                );

                return $message;
            }

            throw $input->exception();
        }

        public static function delete(Model $locale_key_message) {
            return Entity::dao('Neoform\Locale\Key\Message')->delete($locale_key_message);
        }

        public static function _validate(Input\Collection $input) {
            $locales = [];
            foreach (Entity::dao('Neoform\Locale')->all() as $locale) {
                $locales[] = $locale['iso2'];
            }

            $input->body->cast('string')->length(1, 255);
            $input->locale->cast('string')->tolower()->trim()->length(2)->in($locales);
            $input->key_id->cast('integer')->digit(0, 4294967295)->callback(function($key_id) {
                try {
                    $key_id->data('model', new Locale\Key\Model($key_id->val()));
                } catch (Locale\Key\Exception $e) {
                    $key_id->errors('invalid');
                }
            });
        }
    }
