<?php

    namespace Neoform\Entity\Generate;

    use Neoform\Entity\Generate;
    use Neoform\Sql\Parser;

    abstract class Api extends Generate {

        public function create() {

            $longest = $this->table->longestFieldLength();

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Creates a {$this->table->getNameLabel()} Model from an Input Collection\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param Neoform\\Input\\Collection \$input\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return Model\n";
            $this->code .= "\t\t * @throws Neoform\\Input\\Exception\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function insert(Neoform\\Input\\Collection \$input) {\n\n";

            $this->code .= "\t\t\t// Make sure validation has been applied\n";
            $this->code .= "\t\t\tif (! \$input->isValidatedEntries([ ";
            foreach ($this->table->getFields() as $field) {
                if ($field->isAutoIncrement()) {
                    // if its auto increment, there's no reason to be setting the field.
                    continue;
                }

                $this->code .= "'{$field->getName()}', ";
            }
            $this->code .= "])) {\n";

            $this->code .= "\t\t\t\t\$input->applyValidation(new Validator\\Insert);\n";
            $this->code .= "\t\t\t}\n\n";

            $this->code .= "\t\t\t// If input did not pass validation\n";
            $this->code .= "\t\t\tif (! \$input->isValid()) {\n";
            $this->code .= "\t\t\t\tthrow \$input->getException();\n";
            $this->code .= "\t\t\t}\n\n";

            $this->code .= "\t\t\treturn Dao::get()->insert(\n";

            $this->code .= "\t\t\t\t\$input->getVals(\n";
            $this->code .= "\t\t\t\t\t[\n";

            foreach ($this->table->getFields() as $field) {
                if ($field->isAutoIncrement()) {
                    // if its auto increment, there's no reason to be setting the field.
                    continue;
                }

                $this->code .= "\t\t\t\t\t\t'{$field->getName()}',\n";
            }

            $this->code .= "\t\t\t\t\t]\n";
            $this->code .= "\t\t\t\t)\n";

            $this->code .= "\t\t\t);\n";
            $this->code .= "\t\t}\n\n";
        }

        public function update() {

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Update a {$this->table->getNameLabel()} Model with an Input Collection\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param Neoform\\Input\\Collection \$input, \n";                          // these are properly aligned, no touch
            $this->code .= "\t\t * @param Model                    \${$this->table->getNameCamelCase()}\n"; // these are properly aligned, no touch
            $this->code .= "\t\t * @param bool                     \$includeEmpty\n";                              // these are properly aligned, no touch
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return Model updated Model\n";
            $this->code .= "\t\t * @throws Neoform\\Input\\Exception\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function update(Neoform\\Input\\Collection \$input, Model \${$this->table->getNameCamelCase()}, \$includeEmpty=false) {\n\n";

            $this->code .= "\t\t\t// Make sure validation has been applied\n";
            $this->code .= "\t\t\tif (! \$input->isValidatedEntries([ ";
            foreach ($this->table->getFields() as $field) {
                if ($field->isAutoIncrement()) {
                    // if its auto increment, there's no reason to be setting the field.
                    continue;
                }

                $this->code .= "'{$field->getName()}', ";
            }
            $this->code .= "])) {\n";

            $this->code .= "\t\t\t\t\$input->applyValidation(new Validator\\Update(\${$this->table->getNameCamelCase()}, \$includeEmpty));\n";
            $this->code .= "\t\t\t}\n\n";

            $this->code .= "\t\t\t// If input did not pass validation\n";
            $this->code .= "\t\t\tif (! \$input->isValid()) {\n";
            $this->code .= "\t\t\t\tthrow \$input->getException();\n";
            $this->code .= "\t\t\t}\n\n";

            $this->code .= "\t\t\treturn Dao::get()->update(\n";
            $this->code .= "\t\t\t\t\${$this->table->getNameCamelCase()},\n";
            $this->code .= "\t\t\t\t\$input->getVals(\n";
            $this->code .= "\t\t\t\t\t[\n";

            foreach ($this->table->getFields() as $field) {
                if ($field->isAutoIncrement()) {
                    // if its auto increment, there's no reason to be setting the field.
                    continue;
                }

                $this->code .= "\t\t\t\t\t\t'{$field->getName()}',\n";
            }

            $this->code .= "\t\t\t\t\t],\n";
            $this->code .= "\t\t\t\t\t\$includeEmpty\n";
            $this->code .= "\t\t\t\t)\n";
            $this->code .= "\t\t\t);\n";
            $this->code .= "\t\t}\n\n";
        }
    }
