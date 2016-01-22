<?php

    namespace Neoform\Locale\Key\Message;

    use Neoform\Input;
    use Neoform\Entity;
    use Neoform\Locale;

    class Api {

        public static function update(array $info) {

            $input = new Input\Collection($info);

            self::_validate($input);

            if ($input->isValid()) {

                try {
                    $message = Model::fromPk(
                        current(Dao::get()->by_locale_key(
                            $input->locale->getVal(),
                            $input->key_id->getVal()
                        ))
                    );
                    $locale_key_message = Dao::get()->update(
                        $message,
                        array(
                             'key_id' => $input->key_id->getVal(),
                             'body'   => $input->body->getVal(),
                             'locale' => $input->locale->getVal(),
                        )
                    );
                } catch (Exception $e) {

                    $locale_key_message = Dao::get()->insert(array(
                        'key_id' => $input->key_id->getVal(),
                        'body'   => $input->body->getVal(),
                        'locale' => $input->locale->getVal(),
                    ));

                    $message = null;
                }

                Locale\Lib::flushByLocaleNamespace(
                    $locale_key_message->locale,
                    $locale_key_message->locale_key()->locale_namespace()
                );

                return $message;
            }

            throw $input->getException();
        }

        public static function delete(Model $locale_key_message) {
            return Dao::get()->delete($locale_key_message);
        }

        public static function _validate(Input\Collection $input) {
            $locales = [];
            foreach (Locale\Dao::get()->all() as $locale) {
                $locales[] = $locale['iso2'];
            }

            // Body
            $input->validate('body', 'string')
                ->requireLength(1, 255);

            // Locale
            $input->validate('locale', 'string')
                ->toLower()
                ->trim()
                ->requireLength(2, 2)
                ->isIn($locales);

            // Key ID
            $input->validate('key_id', 'integer')
                ->requireDigit(0, 4294967295)
                ->callback(function(Input\Input $keyId) {
                    try {
                        $keyId->setData('model', Locale\Key\Model::fromPk($keyId->getVal()));
                    } catch (Locale\Key\Exception $e) {
                        $keyId->setErrors('invalid');
                    }
                });
        }
    }
