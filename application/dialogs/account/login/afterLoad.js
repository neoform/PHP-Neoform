function() {
    var messageEl = $("div#message", elements.body);
    var originalMessage = messageEl.text();
    var self = this;

    $("form", elements.dialog).on("submit", function(e) {
    	e.preventDefault();

		Core.ajax({
			"url": "/account/ajax/login/verify",
			"data": {
				"email": 	$("input[name='email']", elements.dialog).val(),
				"password": $("input[name='password']", elements.dialog).val(),
				"remember": $("input[name='remember']", elements.dialog).attr('checked') ? 1 : 0
			},
			"success": function(response) {
				if (response.status === 'good') {
				    if (self.activeRemaining()) {
                        self.close();
				    } else {
                        self.closeAll();
    					location.href = response.bounce || location.href;
    				}
				} else {
					if (response.message) {
						var message = response.message;
					} else {
						var message = "There was a problem logging you in.";
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

	$("a[name='create']", elements.dialog).on("click", function(e) {
	   e.preventDefault();
        self.done();
        self.showUrl("/account/ajax/dialog/create");
	});

	$("a[name='lostpassword']", elements.dialog).on("click", function(e) {
	   e.preventDefault();
        self.done();
        self.showUrl("/account/ajax/dialog/lostpassword");
	});

}