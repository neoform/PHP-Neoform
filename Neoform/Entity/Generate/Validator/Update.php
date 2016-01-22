<?php

    namespace Neoform\Entity\Generate\Validator;

    use Neoform\Entity\Generate;
    use Neoform\Sql\Parser;

    abstract class Update extends Generate {

        public function validationVars() {
            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * @var {$this->namespace}\\{$this->table->getNameAsClass()}\\Model\n";
            $this->code .= "\t\t */\n";
            $this->code .= "\t\tprotected \${$this->table->getNameCamelCase()};\n\n";

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * @var bool\n";
            $this->code .= "\t\t */\n";
            $this->code .= "\t\tprotected \$includeEmpty;\n\n";
        }

        public function validateUpdate() {

            $pk = $this->table->getPrimaryKey();

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Validate for update\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param Neoform\\Input\\Collection \$input\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function validate(Neoform\\Input\\Collection \$input) {\n\n";

            foreach ($this->table->getFields() as $field) {
                if ($field->isAutoIncrement()) {
                    // if its auto increment, there's no reason to be setting the field.
                    continue;
                }

                $this->code .= "\t\t\t/**\n";
                $this->code .= "\t\t\t * {$field->getNameLabel()} [{$field->getCasting()}]\n";
                $this->code .= "\t\t\t */\n";

                if ($field->allowsNull()) {
                    $this->code .= "\t\t\t\$input->validate('{$field->getName()}', '{$field->getCasting()}', true)";
                } else {
                    $this->code .= "\t\t\t\$input->validate('{$field->getName()}', '{$field->getCasting()}', !\$this->includeEmpty)";
                }

                if ($parserSpecificValidation = Parser::driverSpecificApiValidation($field)) {
                    $this->code .= "\n\t\t\t\t{$parserSpecificValidation}";
                }

                //unique
                if ($field->isUnique()) {
                    $this->code .= "\n\t\t\t\t->callback(function(Neoform\\Input\\Input \${$field->getNameCamelCase()}) {\n";
                    // record() is a different function, it returns an array with entity info, not it's id only
                    if ($field->isPrimaryKey()) {
                        $this->code .= "\t\t\t\t\t\${$this->table->getNameCamelCase()}Info = {$this->namespace}\\{$this->table->getNameAsClass()}\\Dao::get()->record(\${$field->getNameCamelCase()}->getVal());\n";
                        $this->code .= "\t\t\t\t\tif (\${$this->table->getNameCamelCase()}Info && ({$pk->getCasting()}) \${$this->table->getNameCamelCase()}Info['{$pk->getName()}'] !== \$this->{$this->table->getNameCamelCase()}->get{$pk->getNameTitleCase()}()) {\n";
                    } else {
                        $this->code .= "\t\t\t\t\t\${$pk->getNameCamelCase()}Arr = {$this->namespace}\\{$this->table->getNameAsClass()}\\Dao::get()->by{$field->getNameTitleCaseWithoutId()}(\${$field->getNameCamelCase()}->getVal());\n";
                        $this->code .= "\t\t\t\t\tif (\${$pk->getNameCamelCase()}Arr && ({$pk->getCasting()}) current(\${$pk->getNameCamelCase()}Arr) !== \$this->{$this->table->getNameCamelCase()}->get{$pk->getNameTitleCase()}()) {\n";
                    }
                    $this->code .= "\t\t\t\t\t\t\${$field->getNameCamelCase()}->setErrors('Already in use');\n";
                    $this->code .= "\t\t\t\t\t}\n";
                    $this->code .= "\t\t\t\t})";
                }

                // references = check if object exists
                if ($field->getReferencedField()) {
                    $this->fk($field);
                }

                $this->code .= ";\n\n";
            }
            $this->code = substr($this->code, 0, -1);
            $this->code .= "\t\t}\n\n";
        }

        protected function fk(Parser\Field $field) {
            $this->code .= "\n\t\t\t\t->callback(function(Neoform\\Input\\Input \${$field->getNameCamelCase()}) {\n";
            if ($field->allowsNull()) {
                $this->code .= "\t\t\t\t\tif (\${$field->getNameCamelCase()}->getVal()) {\n";
                $this->code .= "\t\t\t\t\t\ttry {\n";
                $this->code .= "\t\t\t\t\t\t\t\${$field->getNameCamelCase()}->setData('model', {$this->namespace}\\{$field->getReferencedField()->getTable()->getNameAsClass()}\\Model::fromPk(\${$field->getNameCamelCase()}->getVal()));\n";
                $this->code .= "\t\t\t\t\t\t} catch ({$this->namespace}\\{$field->getReferencedField()->getTable()->getNameAsClass()}\\Exception \$e) {\n";
                $this->code .= "\t\t\t\t\t\t\t\${$field->getNameCamelCase()}->setErrors(\$e->getMessage());\n";
                $this->code .= "\t\t\t\t\t\t}\n";
            } else {
                $this->code .= "\t\t\t\t\ttry {\n";
                $this->code .= "\t\t\t\t\t\t\${$field->getNameCamelCase()}->setData('model', {$this->namespace}\\{$field->getReferencedField()->getTable()->getNameAsClass()}\\Model::fromPk(\${$field->getNameCamelCase()}->getVal()));\n";
                $this->code .= "\t\t\t\t\t} catch ({$this->namespace}\\{$field->getReferencedField()->getTable()->getNameAsClass()}\\Exception \$e) {\n";
                $this->code .= "\t\t\t\t\t\t\${$field->getNameCamelCase()}->setErrors(\$e->getMessage());\n";
            }
            $this->code .= "\t\t\t\t\t}\n";
            $this->code .= "\t\t\t\t})";
        }
    }
