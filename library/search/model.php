<?php

	class search_model {

		protected $sphinx;
		protected $results;

		public function __construct() {

			require_once(LIB_ROOT . 'search/sphinx.php');

			$this->sphinx = new SphinxClient();
			$this->sphinx->SetServer('localhost', 9312);
			$this->sphinx->setRetries(1, 200);
			$this->sphinx->setConnectTimeout(4);
		}

		public function search($search, $index, $start, $length, array $weights=null) {

			$this->sphinx->SetLimits($start, $length);

			if ($weights !== null) {
				$this->sphinx->SetFieldWeights($weights);
			}

			$this->results = $this->sphinx->Query($search, $index);

			return isset($this->results['matches']) ? $this->results['matches'] : [];
		}

		public function total_found_count() {
			return isset($this->results['total_found']) ? (int) $this->results['total_found'] : null;
		}

		public function total_count() {
			return isset($this->results['total']) ? (int) $this->results['total'] : null;
		}

		public function time() {
			return isset($this->results['time']) ? number_format((float) $this->results['time'], 4) : null;
		}

		public function debug() {
			return $this->results;
		}
	}