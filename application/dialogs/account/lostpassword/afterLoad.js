function() {
    var messageEl = $("div#message", elements.body);
    var originalMessage = messageEl.text();
    
    $("form", elements.dialog).on("submit", function(e) {
    	e.preventDefault();
	
		Core.ajax({ 
			"url": "/account/ajax/password_lost", 
			"data": {
				"email": $("input[name='email']", elements.dialog).val()
			},
			"success": function(response) {			
				if (response.status === 'good') {
			        CoreDialog.done();
			        alert("Please check your email. You have been sent a link to reset your password.");
				} else {
					if (response.message) {
						var message = response.message;
					} else {
						var message = "There was a problem requesting a new password, please make sure your email address is correct.";
					}
					
					messageEl
					   .text(message)
					   .addClass("error")
					   .removeClass("info");
					   
				    setTimeout(function(){
    				    messageEl
    				       .text(originalMessage)
    					   .addClass("info")
    					   .removeClass("error");
				    }, 3500);
				}
	    	}
	    });
	});
	
	$("input[name='email']", elements.body).focus();
	
	$("a[name='login']", elements.dialog).on("click", function(e) {
	   e.preventDefault();
	   CoreDialog.done();
	   CoreDialog.showUrl("/account/ajax/dialog/login");
	});
}