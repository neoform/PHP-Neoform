<?php

	class generate_lib extends generate {

		public function code() {

			$this->code .= '<?php'."\n\n";
			$this->code .= "\tclass " . $this->table->name . "_lib {\n\n";

			$this->code .= "\t}\n";
		}

	}
