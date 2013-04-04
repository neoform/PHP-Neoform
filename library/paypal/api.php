<?php
	/**
	  * The script implements the HTTPS protocol, via the PHP cURL extension. 
	  *
	  * The nice thing about this protocol is that if you *don't* get a
	  * $response, you can simply re-submit the transaction *using the same
	  * REQUEST_ID* until you *do* get a response -- every time PayPal gets
	  * a transaction with the same REQUEST_ID, it will not process a new
	  * transactions, but simply return the same results, with a DUPLICATE=1
	  * parameter appended.
	  */

	class paypal_api {
    
		protected $vendor;
		protected $user;
		protected $partner;
		protected $password;
		protected $errors = null;
		//protected $ClientCertificationId = '13fda2433fc2123d8b191d2d011b7fdc'; // deprecated - use a random id
		protected $currencies_allowed = array('CAD');//,, 'USD', 'EUR', 'GBP', 'CAD', 'JPY', 'AUD');
		
		const DEV_MODE 	= true;
    	const URL 		= 'https://payflowpro.paypal.com';
		const URL_DEV 	= 'https://pilot-payflowpro.paypal.com';
        
    	public function __construct($vendor, $user, $partner, $password) {
      
			$this->vendor 	= $vendor;
			$this->user		= $user;
			$this->partner 	= $partner;
			$this->password = $password;
			
			if (! strlen($this->vendor)) {
				throw new paypal_exception('Vendor not found');
			}
			
			if (! strlen($this->user)) {
				throw new paypal_exception('User not found');
			}
			
			if (! strlen($this->partner)) {
				throw new paypal_exception('Partner not found');
			}
			
			if (! strlen($this->password)) {
				throw new paypal_exception('Password not found');
			}          
		}

    	// sale
		public function sale_transaction($card_number, $card_expire, $amount, $currency = 'CAD', $data_array = array()) {
		
			if (! $this->validate_card_number($card_number)) {
				throw new paypal_exception('Card Number not valid');
			}
			
			if (! $this->validate_card_expire($card_expire)) {
				throw new paypal_exception('Card Expiration Date not valid');
			}  
			   
			if (! is_numeric($amount) || $amount <= 0) {
				throw new paypal_exception('Amount is not valid');
			}
			
			if (! in_array($currency, $this->currencies_allowed)) {
				throw new paypal_exception('Currency not allowed');
			} 
			
			// build hash
			$tempstr = $card_number . $amount . date('YmdGis') . "1";
			$request_id = md5($tempstr);
			
			// body
			$post = array(
				'USER' 			=> $this->user,
				'VENDOR' 		=> $this->vendor,
				'PARTNER' 		=> $this->partner,
				'PWD' 			=> $this->password,           
				'TENDER' 		=> 'C', // C = credit card, P = PayPal
				'TRXTYPE' 		=> 'S', //  S = Sale transaction, A = Authorisation, C = Credit, D = Delayed Capture, V = Void                        
				'ACCT' 			=> $card_number, 
				'EXPDATE' 		=> $card_expire,
				'NAME' 			=> $card_name,
				'AMT' 			=> $amount,
				// extra data
				'CURRENCY' 		=> $currency,
				'COMMENT1' 		=> $data_array['comment1'],
				'FIRSTNAME' 	=> $data_array['firstname'],
				'LASTNAME' 		=> $data_array['lastname'],
				'STREET' 		=> $data_array['street'],
				'CITY' 			=> $data_array['city'],    
				'STATE' 		=> $data_array['state'],    
				'ZIP' 			=> $data_array['zip'],    
				'COUNTRY' 		=> 'US' . $data_array['country'],
			);
			
			if (isset($data_array['cvv'])) {
				$post['CVV2'] = $data_array['cvv'];
			}
			
			$post['CLIENTIP'] = $data_array['clientip'];
			
			// verbosity
			$post['VERBOSITY'] = 'MEDIUM';
			
			$headers = $this->get_curl_headers();
			$headers[] = "X-VPS-Request-ID: " . $request_id;
			
			//$user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"; // play as Mozilla
			$ch = curl_init(); 
			curl_setopt($ch, CURLOPT_URL, self::DEV_MODE ? self::URL_DEV : self::URL);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			//curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
			curl_setopt($ch, CURLOPT_HEADER, 1); // tells curl to include headers in response
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
			curl_setopt($ch, CURLOPT_TIMEOUT, 45); // times out after 45 secs
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post)); //adding POST data
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2); //verifies ssl certificate
			curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); //forces closure of connection when done 
			curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST 
			
			$result = curl_exec($ch);
			$headers = curl_getinfo($ch);
			curl_close($ch);
			
			$pfpro = $this->get_curl_result($result); //result arrray
			
			if (isset($pfpro['RESULT']) && $pfpro['RESULT'] == 0) {
				return $pfpro;
			} else {
				$this->set_errors($pfpro['RESPMSG'] . ' ['. $pfpro['RESULT'] . ']');
				return false;     
			}
		}

		// Authorization
		public function authorization($card_number, $card_expire, $amount, $card_holder_name, $currency = 'CAD') {
		
			if (! $this->validate_card_number($card_number)) {
				throw new paypal_exception('Card Number not valid');
			}
			
			if (! $this->validate_card_expire($card_expire)) {
				throw new paypal_exception('Card Expiration Date not valid');
			}     
			
			if (! is_numeric($amount) || $amount <= 0) {
				throw new paypal_exception('Amount is not valid');
			}
			
			if (! in_array($currency, $this->currencies_allowed)) {
				throw new paypal_exception('Currency not allowed');
			} 
			
			// build hash
			$tempstr 	= $card_number . $amount . date('YmdGis') . "1";
			$request_id = md5($tempstr);
			
			// body of the POST
			$post = array(
				'USER' 		=> $this->user,
				'VENDOR' 	=> $this->vendor,
				'PARTNER' 	=> $this->partner,
				'PWD' 		=> $this->password,         
				'TENDER' 	=> 'C', // C = credit card, P = PayPal
				'TRXTYPE' 	=> 'A', //  S = Sale transaction, A = Authorisation, C = Credit, D = Delayed Capture, V = Void                        
				'ACCT' 		=> $card_number,
				'EXPDATE' 	=> $card_expire, 
				'NAME' 		=> $card_holder_name,
				'AMT' 		=> $amount,  // amount
				'CURRENCY' 	=> $currency,
				'VERBOSITY' => 'MEDIUM',
			);
			
			$headers 	= $this->get_curl_headers();
			$headers[] 	= "X-VPS-Request-ID: " . $request_id;
			
			//$user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)"; // play as Mozilla
			$ch = curl_init(); 
			curl_setopt($ch, CURLOPT_URL, self::DEV_MODE ? self::URL_DEV : self::URL);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			//curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
			curl_setopt($ch, CURLOPT_HEADER, 1); // tells curl to include headers in response
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
			curl_setopt($ch, CURLOPT_TIMEOUT, 45); // times out after 45 secs
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post)); //adding POST data
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2); //verifies ssl certificate
			curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); //forces closure of connection when done 
			curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST 
			
			// $rawHeader = curl_exec($ch); // run the whole process
			// $info = curl_getinfo($ch); //grabbing details of curl connection
			$result 	= curl_exec($ch);
			$headers 	= curl_getinfo($ch);
			curl_close($ch);
			
			$pfpro = $this->get_curl_result($result); //result arrray
			
			if (isset($pfpro['RESULT']) && $pfpro['RESULT'] == 0) {
				return $pfpro;
			} else {
				$this->set_errors($pfpro['RESPMSG'] . ' ['. $pfpro['RESULT'] . ']');
				return false;     
			}
		}

		// Delayed Capture
		public function delayed_capture($origid, $card_number = '', $card_expire = '', $amount = '') {
		
			if (strlen($origid) < 3) {
				throw new paypal_exception('OrigID not valid');
			}
			
			// build hash
			$tempstr = $card_number . $amount . date('YmdGis') . "2";
			$request_id = md5($tempstr);
			
			// body
			$post = array(
				'USER' 		=> $this->user,
				'VENDOR' 	=> $this->vendor,
				'PARTNER' 	=> $this->partner,
				'PWD' 		=> $this->password,           
				'TENDER' 	=> 'C', // C = credit card, P = PayPal
				'TRXTYPE' 	=> 'D', //  S = Sale transaction, A = Authorisation, C = Credit, D = Delayed Capture, V = Void                        
				'ORIGID' 	=> $origid, // ORIGID to the PNREF value returned from the original transaction
				'VERBOSITY' => 'MEDIUM',
			};
			
			$headers = $this->get_curl_headers();
			$headers[] = "X-VPS-Request-ID: " . $request_id;
			
			//$user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";
			$ch = curl_init(); 
			curl_setopt($ch, CURLOPT_URL, self::DEV_MODE ? self::URL_DEV : self::URL);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			//curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
			curl_setopt($ch, CURLOPT_HEADER, 1); // tells curl to include headers in response
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
			curl_setopt($ch, CURLOPT_TIMEOUT, 45); // times out after 45 secs
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post)); //adding POST data
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2); //verifies ssl certificate
			curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); //forces closure of connection when done 
			curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST 
			
			$result = curl_exec($ch);
			$headers = curl_getinfo($ch);
			curl_close($ch);
			
			$pfpro = $this->get_curl_result($result); //result arrray
			
			if (isset($pfpro['RESULT']) && $pfpro['RESULT'] == 0) {
				return $pfpro;
			} else {
				$this->set_errors($pfpro['RESPMSG'] . ' ['. $pfpro['RESULT'] . ']');
				return false;     
			}     
		}

		// Authorization followed by Delayed Capture
		public function authorization_delayed_capture($card_number, $card_expire, $amount, $card_holder_name, $currency = 'CAD') {
			// 1. authorization
			$result = $this->authorization($card_number, $card_expire, $amount, $card_holder_name, $currency = 'CAD');
			
			if (!$this->get_errors() && isset($result['PNREF'])) {
				// 2. delayed
				$result_capture = $this->delayed_capture($result['PNREF']);
				
				if (! $this->get_errors()) {
					return $result_capture;
				}       
			}
			
			return false;
		}

		// Credit Transaction
		public function credit_transaction($origid) {
		
			if (strlen($origid) < 3) {
				throw new paypal_exception('OrigID not valid');
			}
			
			// build hash
			$tempstr = $card_number . $amount . date('YmdGis') . "2";
			$request_id = md5($tempstr);
			
			// body
			$post = array(
				'USER' 		=> $this->user,
				'VENDOR' 	=> $this->vendor,
				'PARTNER' 	=> $this->partner,
				'PWD' 		=> $this->password,          
				'TENDER' 	=> 'C', // C = credit card, P = PayPal
				'TRXTYPE' 	=> 'C', //  S = Sale transaction, A = Authorisation, C = Credit, D = Delayed Capture, V = Void
				'ORIGID' 	=> $origid, // ORIGID to the PNREF value returned from the original transaction
				'VERBOSITY' => 'MEDIUM',
			);
			
			$headers = $this->get_curl_headers();
			$headers[] = "X-VPS-Request-ID: " . $request_id;
			
			//$user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";
			$ch = curl_init(); 
			curl_setopt($ch, CURLOPT_URL, self::DEV_MODE ? self::URL_DEV : self::URL);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			//curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
			curl_setopt($ch, CURLOPT_HEADER, 1); // tells curl to include headers in response
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
			curl_setopt($ch, CURLOPT_TIMEOUT, 45); // times out after 45 secs
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post)); //adding POST data
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2); //verifies ssl certificate
			curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); //forces closure of connection when done 
			curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST 
			
			$result = curl_exec($ch);
			$headers = curl_getinfo($ch);
			curl_close($ch);
			
			$pfpro = $this->get_curl_result($result); //result arrray
			
			if (isset($pfpro['RESULT']) && $pfpro['RESULT'] == 0) {
				return $pfpro;
			} else {
				$this->set_errors($pfpro['RESPMSG'] . ' ['. $pfpro['RESULT'] . ']');
				return false;     
			} 
		}
    
		// Void Transaction
		public function void_transaction($origid) {
		
			if (strlen($origid) < 3) {
				throw new paypal_exception('OrigID not valid');
			}
			
			// build hash
			$tempstr = $card_number . $amount . date('YmdGis') . "2";
			$request_id = md5($tempstr);
			
			// body
			$post = array(
				'USER' 		=> $this->user,
				'VENDOR' 	=> $this->vendor,
				'PARTNER' 	=> $this->partner,
				'PWD' 		=> $this->password,        
				'TENDER' 	=> 'C', 	// C = credit card, P = PayPal
				'TRXTYPE' 	=> 'V', 	//  S = Sale transaction, A = Authorisation, C = Credit, D = Delayed Capture, V = Void                        
				'ORIGID' 	=> $origid, // ORIGID to the PNREF value returned from the original transaction
				'VERBOSITY' => 'MEDIUM',
			);
			
			$headers = $this->get_curl_headers();
			$headers[] = "X-VPS-Request-ID: " . $request_id;
			
			//$user_agent = "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";
			$ch = curl_init(); 
			curl_setopt($ch, CURLOPT_URL, self::DEV_MODE ? self::URL_DEV : self::URL);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			//curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
			curl_setopt($ch, CURLOPT_HEADER, 1); // tells curl to include headers in response
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return into a variable
			curl_setopt($ch, CURLOPT_TIMEOUT, 45); // times out after 45 secs
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // this line makes it work under https
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post)); //adding POST data
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2); //verifies ssl certificate
			curl_setopt($ch, CURLOPT_FORBID_REUSE, TRUE); //forces closure of connection when done 
			curl_setopt($ch, CURLOPT_POST, 1); //data sent as POST 
			
			$result = curl_exec($ch);
			$headers = curl_getinfo($ch);
			curl_close($ch);
			
			$pfpro = $this->get_curl_result($result); //result arrray
			
			if (isset($pfpro['RESULT']) && $pfpro['RESULT'] == 0) {
				return $pfpro;
			} else {
				$this->set_errors($pfpro['RESPMSG'] . ' ['. $pfpro['RESULT'] . ']');
				return false;     
			} 
		}

		// Curl custom headers; adjust appropriately for your setup:
		public function get_curl_headers() {
			$headers = array();
			
			$headers[] = "Content-Type: text/namevalue"; //or maybe text/xml
			$headers[] = "X-VPS-Timeout: 30";
			$headers[] = "X-VPS-VIT-OS-Name: ";  							// Name of your OS
			$headers[] = "X-VPS-VIT-OS-Version: RHEL 4";  					// OS Version
			$headers[] = "X-VPS-VIT-Client-Type: PHP/cURL";  				// What you are using
			$headers[] = "X-VPS-VIT-Client-Version: 0.01";  				// For your info
			$headers[] = "X-VPS-VIT-Client-Architecture: x86";  			// For your info
			//$headers[] = "X-VPS-VIT-Client-Certification-Id: " . $this->ClientCertificationId . ""; // get this from payflowintegrator@paypal.com
			$headers[] = "X-VPS-VIT-Integration-Product: MyApplication";  	// For your info, would populate with application name
			$headers[] = "X-VPS-VIT-Integration-Version: 0.01"; 			// Application version    
			
			return $headers;  
		}

		// parse result and return an array
		public function get_curl_result($result) {
			if (empty($result)) {
				return;
			}
			
			$pfpro = array();
			$result = strstr($result, 'RESULT');    
			$valArray = explode('&', $result);
			
			foreach ($valArray as $val) {
				$valArray2 = explode('=', $val);
				$pfpro[$valArray2[0]] = $valArray2[1];
			}
			return $pfpro;    
		}

		public function validate_card_expire($mmyy) {
		
			if (! is_numeric($mmyy) || strlen($mmyy) !== 4) {
				return false;
			}      
			
			$mm = substr($mmyy, 0, 2);
			$yy = substr($mmyy, 2, 2);        
			
			if ($mm < 1 || $mm > 12) {
				return false;
			}
			
			$year 	= (int) date('Y');
			$yy 	= substr($year, 0, 2) . $yy; // eg 2007
			
			if (is_numeric($yy) && $yy >= $year && $yy <= ($year + 10)) {
			
			} else {
				return false;
			}
			
			if ((int) $yy === (int) $year && (int) $mm < date('n')) {
				return false;
			} 
			     
			return true;
		}

	    // luhn algorithm
	    public function validate_card_number($card_number) {
			$card_number = preg_replace('`[^0-9]`', '', $card_number);      
		
			if ($card_number < 9) {
				return false;
			}
			
			$card_number = strrev($card_number);
			$total = 0;
			
			for ($i = 0; $i < strlen($card_number); $i++) {
				$current_number = substr($card_number, $i, 1);
				
				if ($i % 2 === 1) {
					$current_number *= 2;
				}
				
				if ($current_number > 9) {
					$first_number = $current_number % 10;
					$second_number = ($current_number - $first_number) / 10;
					$current_number = $first_number + $second_number;
				}
				$total += $current_number;
			}
			return ($total % 10 === 0);
	    }
	
		public function get_errors() {
			if ($this->errors !== null) {
				return $this->errors;
			}
			return null;
		}
	  
	    public function set_errors($string) {
			$this->errors = $string;
	    }
	
	    public function get_version() {
			return '4.03';
	    }    
	} 
  