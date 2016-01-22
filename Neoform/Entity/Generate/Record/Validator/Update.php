<?php

    namespace Neoform\Entity\Generate\Record\Validator;

    use Neoform\Entity\Generate;

    class Update extends Generate\Validator\Update {

        public function code() {

            $this->code .= '<?php'."\n\n";
            $this->code .= "\tnamespace {$this->namespace}\\{$this->table->getNameAsClass()}\\Validator;\n\n";
            $this->code .= "\tuse Neoform;\n";
            if ($this->namespace !== 'Neoform') {
                $this->code .= "\tuse {$this->namespace};\n";
            }
            $this->code .= "\n";
            $this->code .= "\tclass Update implements Neoform\\Input\\Validator {\n\n";

            $this->validationVars();
            $this->constructor();
            $this->validateUpdate();

            $this->code = substr($this->code, 0, -1);
            $this->code .= "\t}\n";
        }

        public function constructor() {
            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * @param {$this->namespace}\\{$this->table->getNameAsClass()}\\Model \${$this->table->getNameCamelCase()}\n";
            $this->code .= "\t\t * @param bool \$includeEmpty\n";
            $this->code .= "\t\t */\n";
            $this->code .= "\t\tpublic function __construct({$this->namespace}\\{$this->table->getNameAsClass()}\\Model \${$this->table->getNameCamelCase()}, \$includeEmpty=false) {\n";
            $this->code .= "\t\t\t\$this->{$this->table->getNameCamelCase()} = \${$this->table->getNameCamelCase()};\n";
            $this->code .= "\t\t\t\$this->includeEmpty = \$includeEmpty;\n";
            $this->code .= "\t\t}\n\n";
        }
    }