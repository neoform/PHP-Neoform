<?php

    class generate_record_collection extends generate_collection {

        public function code() {

            $this->code .= '<?php'."\n\n";

            $this->code .= "\t/**\n";
            $this->code .= "\t * " . ucwords(str_replace('_', ' ', $this->table->name)) . " collection\n";
            $this->code .= "\t */\n";

            $this->code .= "\tclass " . $this->table->name . "_collection extends record_collection implements " . $this->table->name . "_definition {\n\n";

            $this->preloaders();

            $this->code = substr($this->code, 0, -1);
            $this->code .= "\t}\n";
        }

        public function preloaders() {

            // many to one relationship (other tables referencing this one as a constraint)
            foreach ($this->table->referencing_fields as $referencing_field) {

                /**
                *   User (*id*, name, email) --> User_info (*user_id*, address, birthday)
                */
                if ($referencing_field->is_unique()) {
                    // one to one relationship on inbound references
                    $this->one_to_one($referencing_field->referenced_field, $referencing_field);
                } else {

                     /**
                     *   User (*id*, blah, blah) --> User_comments (id, *user_id*, body, posted_on)
                     */
                    if ($referencing_field->table->is_record()) {

                        // one to many relationship (linking table implicating this one, tying it to another)
                        $this->one_to_many($referencing_field);
                    }

                    // if the referencing field is part of a 2-key unique key, it's a many-to-many
                    if ($referencing_field->is_link_index()) {
                        // many to many relationship (linking table implicating this one, tying it to another)
                        $this->many_to_many($referencing_field, $referencing_field->get_other_link_index_field());
                    }
                }
            }

            /**
            *   User (*id*, name, email) <-- User_info (*user_id*, address, birthday)
            */
            // one to one relationships on outbound references
            foreach ($this->table->foreign_keys as $foreign_key) {
                if ($foreign_key->table->is_record()) {

                    $this->one_to_one($foreign_key, $foreign_key->referenced_field);
                }
            }
        }

        // these are all labelled as _collections because that's what they return as a value. :P

        protected function one_to_one(sql_parser_field $field, sql_parser_field $referenced_field) {

            $self_reference = $referenced_field->table === $this->table;

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Preload the " . ucwords(str_replace('_', ' ', $referenced_field->table->name)) . " models in this collection\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return " . $referenced_field->table->name . "_collection\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function " . ($self_reference ? 'parent_' : '') . $referenced_field->table->name . "_collection() {\n";
            if ($self_reference) {
                $this->code .= "\t\t\treturn \$this->_preload_one_to_one('" . $referenced_field->table->name . "', '" . $field->name . "', 'parent_" . $referenced_field->table->name . "');\n";
            } else {
                $this->code .= "\t\t\treturn \$this->_preload_one_to_one('" . $referenced_field->table->name . "', '" . $field->name . "');\n";
            }
            $this->code .= "\t\t}\n\n";
        }

        protected function one_to_many(sql_parser_field $field) {

            $self_reference = $field->table === $this->table;

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Preload the " . ($self_reference ? 'child ' : '') . ucwords(str_replace('_', ' ', $field->table->name)) . " models in this collection\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return " . $field->table->name . "_collection\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function " . ($self_reference ? 'child_' : '') . $field->table->name . "_collection() {\n";
            if ($self_reference) {
                $this->code .= "\t\t\treturn \$this->_preload_one_to_many('" . $field->table->name . "', 'by_" . $field->name_idless . "', 'child_" . $field->table->name . "_collection');\n";
            } else {
                $this->code .= "\t\t\treturn \$this->_preload_one_to_many('" . $field->table->name . "', 'by_" . $field->name_idless . "');\n";
            }
            $this->code .= "\t\t}\n\n";
        }

        protected function many_to_many(sql_parser_field $field, sql_parser_field $referenced_field) {

            $this->code .= "\t\t/**\n";
            $this->code .= "\t\t * Preload the " . ucwords(str_replace('_', ' ', $referenced_field->referenced_field->table->name)) . " models in this collection\n";
            $this->code .= "\t\t *\n";
            $this->code .= "\t\t * @return " . $referenced_field->referenced_field->table->name . "_collection\n";
            $this->code .= "\t\t */\n";

            $this->code .= "\t\tpublic function " . $referenced_field->referenced_field->table->name . "_collection() {\n";
            $this->code .= "\t\t\treturn \$this->_preload_many_to_many('" . $field->table->name . "', 'by_" . $field->name_idless . "', '" . $referenced_field->referenced_field->table->name . "');\n";
             $this->code .= "\t\t}\n\n";
        }
    }