<?php

	class generate_model extends generate {

		public function get() {

			$enum_values = [
				"'yes','no'"	 => 'yes',
				"'no','yes'" 	 => 'yes',
				"'true','false'" => 'true',
				"'false','true'" => 'true',
			];

			$ints 	 = [];
			$floats  = [];
			$dates   = [];
			$bools   = [];
			$strings = [];

			foreach ($this->table->fields as $field) {
				switch ($field->casting_extended) {
					case 'int':
					case 'integer':
						$ints[] = $field;
						break;

					case 'float':
						$floats[] = $field;
						break;

                    case 'date':
                    case 'datetime':
						$dates[] = $field;
						break;

                    case 'bool':
                    case 'boolean':
						$bools[] = $field;

					default:
						$strings[] = $field;
						break;
				}
			}

			$this->code .= "\t\tpublic function __get(\$k) {\n\n";
			$this->code .= "\t\t\tif (isset(\$this->vars[\$k])) {\n";
			$this->code .= "\t\t\t\tswitch (\$k) {\n";

			// INTS
			if (count($ints)) {
				$this->code .= "\t\t\t\t\t// integers\n";
				foreach ($ints as $int) {
					$this->code .= "\t\t\t\t\tcase '" . $int->name . "':\n";
				}
				$this->code .= "\t\t\t\t\t\treturn (int) \$this->vars[\$k];\n\n";
			}

			// FLOATS
			if (count($floats)) {
				$this->code .= "\t\t\t\t\t// floats\n";
				foreach ($floats as $float) {
					$this->code .= "\t\t\t\t\tcase '" . $float->name . "':\n";
				}
				$this->code .= "\t\t\t\t\t\treturn (float) \$this->vars[\$k];\n\n";
			}

			// BOOLS
			if (count($bools)) {
				$this->code .= "\t\t\t\t\t// booleans\n";
				foreach ($bools as $bool) {
					$this->code .= "\t\t\t\t\tcase '" . $bool->name . "':\n";
					$this->code .= "\t\t\t\t\t\treturn \$this->vars[\$k] === '" . $bool->bool_true_value . "';\n\n";
				}
			}

			// DATES
			if (count($dates)) {
				$this->code .= "\t\t\t\t\t// dates\n";
				foreach ($dates as $date) {
					$this->code .= "\t\t\t\t\tcase '" . $date->name . "':\n";
				}
				$this->code .= "\t\t\t\t\t\treturn \$this->_model(\$k, \$this->vars[\$k], 'type_date');\n\n";
			}

			// STRINGS
			if (count($strings)) {
				$this->code .= "\t\t\t\t\t// strings\n";
				foreach ($strings as $string) {
					$this->code .= "\t\t\t\t\tcase '" . $string->name . "':\n";
				}
				$this->code .= "\t\t\t\t\t\treturn (string) \$this->vars[\$k];\n\n";
			}

			$this->code .= "\t\t\t\t\tdefault:\n";
			$this->code .= "\t\t\t\t\t\treturn \$this->vars[\$k];\n";
			$this->code .= "\t\t\t\t}\n";
			$this->code .= "\t\t\t}\n";
			$this->code .= "\t\t}\n\n";
		}



		protected function class_comments() {
			/**
			* The short description
			*
			* As many lines of extendend description as you want {@link element} links to an element
			* {@link http://www.example.com Example hyperlink inline link} links to a website
			* Below this goes the tags to further describe element you are documenting
			*
			* @param  	type	$varname	description
			* @return 	type	description
			* @access 	public or private
			* @author 	author name
			* @copyright	name date
			* @version	version
			* @see		name of another element that can be documented, produces a link to it in the documentation
			* @link		a url
			* @since  	a version or a date
			* @deprecated	description
			* @deprec	alias for deprecated
			* @magic	phpdoc.de compatibility
			* @todo		phpdoc.de compatibility
			* @exception	Javadoc-compatible, use as needed
			* @throws  	Javadoc-compatible, use as needed
			* @var		type	a data type for a class variable
			* @package	package name
			* @subpackage	sub package name, groupings inside of a project
			*/

			$this->code .= "\t/**\n";
			$this->code .= "\t* " . ucwords(str_replace('_', ' ', $this->table->name)) . " Model\n";
			$this->code .= "\t*\n";
			foreach ($this->table->fields as $field) {
				$this->code .= "\t* @var " . $field->casting_extended . ($field->allows_null() ? '|null' : '') . ' $' . $field->name . "\n";
			}
			$this->code .= "\t*/\n";
		}

		public function references() {

			// many to one relationship (other tables referencing this one as a constraint)
            foreach ($this->table->referencing_fields as $referencing_field) {

                // if the reference is to a field that uniquely identifies a single row, the it is a one-to-one
                if ($referencing_field->is_unique()) {
                    // one to one relationship on inbound references
                    $this->one_to_one($referencing_field->referenced_field, $referencing_field);
                } else {

                    if ($referencing_field->table->is_record()) {
                        // one to many relationship (linking table implicating this one, tying it to another)
                        $this->one_to_many(
                            $referencing_field->table->name . '_collection',
                            $referencing_field->table->name . '_dao',
                            $referencing_field,
                            $referencing_field->referenced_field
                        );
                    }

                    // if the referencing field is part of a 2-key unique key, it's a many-to-many
                    if ($referencing_field->is_link_index()) {
                        $other_key = $referencing_field->get_other_link_index_field();

                        // the many to many becomes one to many since this is a model and not a collection
                        $this->one_to_many(
                            $other_key->table->name . '_collection',
                            $referencing_field->table->name . '_dao',
                            $referencing_field,
                            $referencing_field->referenced_field
                        );
                    }
                }
            }

            // one to one relationships on outbound references
			foreach ($this->table->foreign_keys as $foreign_key) {
                if ($foreign_key->table->is_record()) {

                    $this->one_to_one($foreign_key, $foreign_key->referenced_field);
                }
			}
		}

		protected function one_to_one(sql_parser_field $field, sql_parser_field $referenced_field) {

            $self_reference = $referenced_field->table === $this->table;

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * " . ($self_reference ? 'Parent ' : '') . ucwords(str_replace('_', ' ', $referenced_field->table->name)) . " Model based on '" . $field->name . "'\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param " . $referenced_field->table->name . "_model \$" . $referenced_field->table->name . " preload model\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return " . $referenced_field->table->name . "_model\n";
            $this->code .= "\t\t */\n";

			$this->code .= "\t\tpublic function " . ($self_reference ? 'parent_' : '') . $referenced_field->table->name . "(" . $referenced_field->table->name . "_model $" . $referenced_field->table->name . "=null) {\n";
			$this->code .= "\t\t\treturn $" . $referenced_field->table->name . " !== null ? (\$this->_vars['" . ($self_reference ? 'parent_' : '') . $referenced_field->table->name . "'] = $" . $referenced_field->table->name . ") : \$this->_model('" . ($self_reference ? 'parent_' : '') . $referenced_field->table->name . "', \$this->vars['" . $field->name . "'], '" . $referenced_field->table->name . "_model');\n";
			$this->code .= "\t\t}\n\n";
		}

		protected function one_to_many($collection_name, $dao_name, sql_parser_field $field, sql_parser_field $referenced_field) {

            $self_reference = $field->table === $this->table;

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * " . ($self_reference ? 'Child ' : '') . ucwords(str_replace('_', ' ', $collection_name)) . "\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @param " . $collection_name . " \$" . $collection_name . " preload collection\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return " . $collection_name . "\n";
            $this->code .= "\t\t */\n";

			$this->code .= "\t\tpublic function " . ($self_reference ? 'child_' : '') . $collection_name . "(" . $collection_name . " $" . $collection_name . "=null) {\n";
			$this->code .= "\t\t\tif (! array_key_exists('" . ($self_reference ? 'child_' : '') . $collection_name . "', \$this->_vars)) {\n";

			$this->code .= "\t\t\t\tif ($" . $collection_name . " !== null) {\n";
			$this->code .= "\t\t\t\t\t\$this->_vars['" . ($self_reference ? 'child_' : '') . $collection_name . "'] = $" . $collection_name . ";\n";
			$this->code .= "\t\t\t\t} else {\n";
			$this->code .= "\t\t\t\t\t\$this->_vars['" . ($self_reference ? 'child_' : '') . $collection_name . "'] = new " . $collection_name . "(\n";
			$this->code .= "\t\t\t\t\t\t" . $dao_name . "::by_" . $field->referenced_field->name . "(\$this->vars['" . $referenced_field->name . "'])\n";
			$this->code .= "\t\t\t\t\t);\n";
			$this->code .= "\t\t\t\t}\n";
			$this->code .= "\t\t\t}\n";
			$this->code .= "\t\t\treturn \$this->_vars['" . ($self_reference ? 'child_' : '') . $collection_name . "'];\n";
			$this->code .= "\t\t}\n\n";
		}
	}
