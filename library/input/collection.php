<?php

	class input_collection extends ArrayObject {

		protected $data = [];
		protected $error; //only to be used if an array was supplied when a non-array was expected

		public function __construct(array $arr) {
			foreach ($arr as $k => $v) {
				if (is_array($v)) {
					$this[$k] = new input_collection($v);
				} else {
					$this[$k] = new input_model($v);
				}
			}
		}

		public function __set($k, $v) {
			//$this[$k] = $v;
		}

		public function __get($k) {
			if (! isset($this[$k])) {
				$this[$k] = new input_model();
			}

			return $this[$k];
		}

		//this function being called is considered an error (happens when a non-existent function is called)
		//this happens when a collection is being used as if it were a model…  (because an array was passed in place a string or int.. etc)
		public function __call($name, array $args) {
			$this->error = 'Invalid type';
			return $this;
		}

		// Get one or more entries (model, not value)
		public function get() {
			$args = func_get_args();
			if (count($args)) {
				if (count($args) === 1) {
					if (! is_array($args[0])) {
						if (! isset($this[$args[0]])) {
							$this[$args[0]] = new input_model();
						}

						return $this[$args[0]];
					}
				}

				return array_intersect_key($this, array_flip($args));

			} else {
				return $this;
			}
		}

		public function val() {
			$args = func_get_args();
			if (count($args)) {
				if (count($args) === 1) {
					if (! is_array($args[0])) {
						return isset($this[$args[0]]) ? $this[$args[0]]->val() : null;
					}
				}

				$entries = array_intersect_key((array) $this, array_flip($args));
			} else {
				$entries = (array) $this;
			}

			$vals = [];
			foreach ($entries as $k => $entry) {
				$vals[$k] = $entry->val();
			}
			return $vals;
		}

		public function vals(array $keys, $empty_optional_fields=true) {
		    $vals = [];
		    foreach ($keys as $key) {
		        if (isset($this[$key])) {
		            if ($empty_optional_fields || ! $this[$key]->is_empty()) {
    		            $vals[$key] = $this[$key]->val();
    		        }
		        }
		    }
		    return $vals;
		}

		public function data($k=null, $v=null) {
			if ($v !== null) {
				$this->data[$k] = $v;
				return $this;
			} else if (isset($this->data[$k])) {
				return $this->data[$k];
			}
		}

		public function is_valid() {
			if ($this->error) {
				return false;
			}
			foreach ($this as $entry) {
				if (! $entry->is_valid()) {
					return false;
				}
			}
			return true;
		}

		public function reset_errors() {
			$this->error = null;
			return $this;
		}

		public function errors($set=null) {
			if ($set) {
				$this->error = $set;
				return $this;
			} else {
				if ($this->error === null) {
					$errors = new input_error_collection();
				} else {
					$errors = new input_error_collection([
						$this->error,
					]);
				}

				foreach ($this as $k => $entry) {
					$err = $entry->errors();
					if ($err !== null) {
						if (! $err instanceof input_error_collection || $err->count()) {
							$errors->$k = $err;
						}
					}
				}
				return $errors;
			}
		}

		public function exception() {
			return new input_exception($this->errors());
		}

		//public function exception() {
		//	return new input_exception($this->errors());
		//}

		public function count($min=null, $max=null) {
			$count = count($this);
			if ($min || $max) {
				if ($min === $max && $count !== $min) {
					$this->error = $min . " required";
				} else if ($min && $count < $min) {
					$this->error = $min . " minimum";
				} else if ($max && $count > $max) {
					$this->error =$min . " maximum";
				}
				return $this;
			} else {
				return $count;
			}
		}

		public function each($func) {
			foreach ($this as $k => $entry) {
				$func($entry, $k, $this);
			}
			return $this;
		}

		//get rid of duplicates
		public function unique() {
			if (count($this)) {
				$keys = [];
				$remove = [];
				foreach ($this as $k => $entry) {
					if (isset($keys[$entry->val()])) {
						$remove[] = $k;
					} else {
						$keys[$entry->val()] = true;
					}
				}

				if (count($remove)) {
					foreach ($remove as $k) {
						unset($this[$k]);
					}
				}
			}

			return $this;
		}

		//this is a collection, not the model
		public function callback($func) {
			$this->error ='invalid type (array given)';
			return $this;
		}

		public function optional() {
			//empty function
			return $this;
		}
	}
