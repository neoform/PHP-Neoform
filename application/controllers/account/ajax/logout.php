<?php

	core::http()->ref();
	
	$json = new render_json();
	
	if (core::auth()->user_id) { 
		try {			
			auth_api::logout(core::auth());
			$json->redirect = true; 
		} catch (input_exception $e) {
		 
		}
	} else {
		$json->redirect = true;
	}
	
	$json->render();