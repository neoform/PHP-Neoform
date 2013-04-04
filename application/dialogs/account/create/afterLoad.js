function() {

    var _validate_email = function(e) {
        var email = $("input[name='email']", elements.body).val();

        if (email && email.length) {
            $.ajax({
                "url": "/account/ajax/check/email",
                "data": {
                    "email": email
                },
                "success": function(response) {
                    if (response.status === 'good') {
                        $("span[name='email_status']", elements.body).html("<span class='good'>Available</span>");
                    } else {
                        $("span[name='email_status']", elements.body).html("<span class='error'>" + response.message + "</span>");
                    }
                }
            });
        } else {
            $("span[name='email_status']", elements.body).html('');
        }
    };

    var messageEl = $("div#message", elements.body);
    var originalMessage = messageEl.text();

    $("form", elements.dialog).on("submit", function(e) {
        e.preventDefault();

        Core.ajax({
            "url": "/account/ajax/insert",
            "data": {
                "email":     $("input[name='email']", elements.dialog).val(),
                "password1": $("input[name='password1']", elements.dialog).val(),
                "password2": $("input[name='password2']", elements.dialog).val()
            },
            "success": function(response) {
                if (response.status === 'good') {
                    if (CoreDialog.activeRemaining()) {
                        CoreDialog.close();
                    } else {
                        CoreDialog.closeAll();
                        location.href = response.bounce || location.href;
                    }
                } else {
                    if (response.message) {
                        var message = response.message;
                    } else {
                        var message = "There was a problem creating your account";
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

                    $("div.tiny.error", elements.body)
                        .css("display", "none");

                    $("input", elements.body)
                        .removeClass("error");

                    if (response.errors) {
                        for (var k in response.errors) {
                            $("div[name='" + k + "'].error", elements.dialog)
                                .html(response.errors[k])
                                .css("display", "block");

                            $("input[name='" + k + "']", elements.dialog)
                                .addClass("error");
                        }
                    }
                }
            }
        });
    });

    $("input[name='email']", elements.body)
       .focus()
       .blur(_validate_email);

    $("a[name='login']", elements.dialog).on("click", function(e) {
       e.preventDefault();
       CoreDialog.done();
       CoreDialog.showUrl("/account/ajax/dialog/login");
    });

    _validate_email();
}