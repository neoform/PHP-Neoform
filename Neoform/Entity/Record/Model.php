<?php

    namespace Neoform\Entity\Record;

    use ArrayAccess;
    use Neoform\Entity\Exception;
    use Neoform;

    abstract class Model extends Neoform\Entity\Model implements Neoform\Entity\Record\Entity {

        /**
         * Generate a model from primary key
         *
         * @param integer|float|string|binary $pk
         *
         * @return static
         */
        public static function fromPk($pk) {
            if ($vars = Dao::dao(static::getNamespace())->record($pk)) {
                $self = new static;
                $self->vars = $vars;
                return $self;
            }

            $exceptionClass = '\\' . static::getNamespace() . '\\Exception';
            throw new $exceptionClass('That ' . static::getLabel() . ' does not exist');
        }

        /**
         * Reload the current model's data from the repo
         */
        public function reload() {
            $info = Dao::dao(static::getNamespace())->record(static::getPrimaryKeyName());
            if (! $info) {
                $exceptionClass = '\\' . static::getNamespace() . '\\Exception';
                throw new $exceptionClass('Reload ' . static::getLabel() . ' failed');
            }
            $this->_vars = [];
            $this->vars = $info;
        }

        /**
         * Get a model by a given field or fields
         * folder_model::by_md5($hash) will return a folder model.
         * this is just a shortcut for new folderModel(reset(folderDao::byMd5($hash)));
         *
         * @param string $name
         * @param array  $args
         *
         * @return Model
         */
        public static function __callStatic($name, array $args) {
            $model = '\\' . static::getNamespace() . '\\Model';
            return $model::fromPk(current(
                call_user_func_array([ Dao::dao(static::getNamespace()), $name], $args)
            ));
        }
    }
