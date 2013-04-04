<?php

	class generate_record_api extends generate_api {

	    public function code() {

			$this->code .= '<?php'."\n\n";
			$this->code .= "\tclass " . $this->table->name . "_api {\n\n";

			$this->create();
			$this->update();
			$this->delete();
			//$this->validate_lookup();
			$this->validate_insert();
			$this->validate_update();

            $this->code = substr($this->code, 0, -1);
			$this->code .= "\t}\n";
		}

	    public function delete() {
		    $this->code .= "\t\tpublic static function delete(" . $this->table->name . "_model $" . $this->table->name . ") {\n";
		    $this->code .= "\t\t\treturn " . $this->table->name . "_dao::delete($" . $this->table->name . ");\n";
		    $this->code .= "\t\t}\n\n";
		}
	}