<?php

	class generate_definition extends generate {

		public function code() {

			$this->code .= '<?php'."\n\n";

            $this->code .= "\t/**\n";
            $this->code .= "\t * Entity definition interface\n";
            $this->code .= "\t */\n";

			$this->code .= "\tinterface " . $this->table->name . "_definition {\n\n";

            $this->constants();

            $this->code = substr($this->code, 0, -1);
			$this->code .= "\t}\n";
		}

	}
