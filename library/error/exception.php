<?php
	class error_exception extends exception {
		
		protected $description;
		
		public function __construct($message=null, $description=null) {
			$this->message 		= $message;
			$this->description 	= $description;
		}
		
		public function message() {
			return $this->message;
		}
		
		public function description() {
			return $this->description;
		}
	}