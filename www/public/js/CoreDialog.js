(function($, undefined){

    Keys = {
        ALT: 18,
        BACKSPACE: 8,
        CAPS_LOCK: 20,
        COMMA: 188,
        COMMAND: 91,
        COMMAND_LEFT: 91, // COMMAND
        COMMAND_RIGHT: 93,
        CONTROL: 17,
        DELETE: 46,
        DOWN: 40,
        END: 35,
        ENTER: 13,
        ESCAPE: 27,
        HOME: 36,
        INSERT: 45,
        LEFT: 37,
        MENU: 93, // COMMAND_RIGHT
        NUMPAD_ADD: 107,
        NUMPAD_DECIMAL: 110,
        NUMPAD_DIVIDE: 111,
        NUMPAD_ENTER: 108,
        NUMPAD_MULTIPLY: 106,
        NUMPAD_SUBTRACT: 109,
        PAGE_DOWN: 34,
        PAGE_UP: 33,
        PERIOD: 190,
        RIGHT: 39,
        SHIFT: 16,
        SPACE: 32,
        TAB: 9,
        UP: 38,
        WINDOWS: 91 // COMMAND
    };

    CoreDialog = (function() {
        var self     = {},
            elements = {},
            done,
            options,
            defaultOptions = {
                "callbacks": {
                    "beforeLoad":     undefined,
                    "afterLoad":      undefined,
                    "beforeShow":     undefined,
                    "afterShow":      undefined,
                    "beforeClose":    undefined,
                    "afterClose":     undefined,
                    "beforeCloseAll": undefined,
                    "afterCloseAll":  undefined,
                    "beforeCancel":   undefined,
                    "afterCancel":    undefined,
                    "beforeAjax":     undefined
                },
                "css": {
                    "width": "500px",
                    "height": undefined
                },
                "content": {
                    "title": "",
                    "body":  "",
                    "foot":  ""
                },
                "closekeys": [ Keys.ESCAPE ],
                "autoclose": undefined
            },
            active,
            $window,

            // for callbacks, contains additional local vars
            _callbackSelf = self;

        // Callbacks need access to the elements var
        _callbackSelf.elements = elements;

        // Public - showUrl()
        self.showUrl = function(url, opts) {
            if (typeof opts !== "object") {
                opts = {};
            }
            opts.url = url;
            _show(opts);
            return self;
        };

        // Public - show()
        self.show = function(opts) {
            _show(opts);
            return self;
        };

        // Public - close()
        self.close = function(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }

            var opts = _trail.get();
            done = true;
            if (typeof opts !== "undefined") {
                _show(opts);
            } else {
                _close();
            }

            return self;
        };

        // Public - closeAll()
        self.closeAll = function() {
            _closeAll();
            return self;
        };

        // Done
        self.done = function() {
            done = true;
        };

        self.activeRemaining = function() {
            return _trail.peek() ? true : false;
        };

        // Public - alert()
        self.alert = function(message, title, cssClass) {
            var okay = $("<button/>")
                .addClass("okay")
                .html("OK")
                .on("click", self.close);

            var body = $("<div/>")
                .addClass((cssClass || "alert") + " padded info")
                .html(message);

            _show({
                "content": {
                    "body":  body,
                    "title": title || "Alert",
                    "foot":  $("<div>").addClass("center").append(okay)
                },
                "callbacks": {
                    "afterShow": function() {
                        _focus($("button", elements.foot));
                    }
                },
                "closekeys": [ Keys.ESCAPE ]
            });

            return self;
        };

        // Public - error()
        self.error = function(message, title) {
            _error(title, message);
            return self;
        };

        // Public - confirm()
        self.confirm = function(message, title, buttons, options) {

            var footerDiv = $("<div/>").addClass("center");

            $.each(buttons, function(k, button) {
                var buttonEl = $("<button/>")
                    .attr("value", button.value)
                    .html(button.label);

                if (typeof button.action === "function") {
                    buttonEl
                        .on("click", function(e) {
                            e.preventDefault();
                            button.action.call(_callbackSelf);
                        });
                }

                if (typeof button.cssClass !== "undefined") {
                    buttonEl.addClass(button.cssClass);
                }

                if (button.focused) {
                    buttonEl.addClass("focus");
                }

                footerDiv.append(buttonEl);
            });

            var body = $("<div/>")
                .addClass("confirm")
                .html(message);

            if (options.cssClass) {
                body.addClass(options.cssClass);
            } else {
                body.addClass("padded info");
            }

            _show({
                "content": {
                    "body":  body,
                    "title": title || "Confirm",
                    "foot":  footerDiv
                },
                "callbacks": {
                    "afterShow": function(e) {
                        var focusedButton = $("button.focus", elements.foot);
                        if (focusedButton) {
                            _focus(focusedButton);
                        }
                    }
                }
            });
            return self;
        };

        // Public - prompt()
        self.prompt = function(message, title, fields, buttons) {

            // Defaults
            if (typeof fields === "undefined") {
                fields = [];
            }

            if (typeof buttons === "undefined") {
                buttons = [];
            }

            var bodyDiv   = $("<div/>"),
                footerDiv = $("<div/>").addClass("center");

            $.each(buttons, function(k, button) {
                var buttonEl = $("<button/>")
                    .attr("value", button.value)
                    .html(button.label);

                if (typeof button.action === "function") {
                    buttonEl
                        .on(
                            "click",
                            function(e) {
                                e.preventDefault();
                                button.action.call(_callbackSelf);
                            }
                        );
                }

                if (typeof button.cssClass !== "undefined") {
                    buttonEl.addClass(button.cssClass);
                }

                footerDiv.append(buttonEl);
            });

            var body = $("<div/>")
                .addClass("prompt");

            if (message) {
                body.append(
                    $("<div>")
                        .addClass("padded info")
                        .html(message)
                );
            } else {
                body.addClass("no-message");
            }

            var firstFieldName;

            if (fields && fields.length) {
                var fieldEl;

                $.each(fields, function(k, field){

                    if (! firstFieldName) {
                        firstFieldName = field.name;
                    }

                    var table = $("<table/>")
                        .addClass("field");

                    var tr = $("<tr/>")
                        .appendTo(table);

                    switch (field.type) {

                        case "text":
                        case "checkbox":
                        case "radio":
                            fieldEl = $("<input/>")
                                .attr({
                                    "type":  field.type,
                                    "name":  field.name,
                                    "value": ""
                                });
                            break;

                        case "textarea":
                            fieldEl = $("<textarea/>")
                                .attr({
                                    "name": field.name
                                });
                            break;

                        case "select":
                            fieldEl = $("<select/>")
                                .attr({
                                    "name": field.name
                                });

                            if (typeof field.options === "object") {
                                $.each(field.options, function(k, option){
                                    fieldEl.append(
                                        $("<option/>")
                                            .attr("value", k)
                                            .html(option)
                                    )
                                });
                            }

                            break;

                        default:
                            return;
                    }

                    if (fields[k].value) {
                        fieldEl.val(fields[k].value);
                    }

                    if (fields[k].css) {
                        fieldEl.css(fields[k].css);
                    }

                    if (fields[k].label) {
                        tr.append(
                            $("<td/>")
                                .addClass("label")
                                .append(
                                    $("<label/>")
                                        .html(fields[k].label)
                                )
                                .append(
                                    $("<div/>")
                                        .addClass("tiny error")
                                        .css("display", "none")
                                )
                        );
                    }

                    var fieldTd = $("<td/>")
                        .addClass("field")
                        .append(fieldEl)
                        .appendTo(tr);

                    if (fields[k].units) {
                        fieldTd.append(
                            $("<span/>")
                                .addClass("units")
                                .html(fields[k].units)
                        );
                    }

                    bodyDiv.append(table);
                });


                body.append(bodyDiv);
            }

            _show({
                "content": {
                    "body": body,
                    "title": title || "Confirm",
                    "foot": footerDiv
                },
                "callbacks": {
                    "afterShow": function() {
                        $("[name='" + firstFieldName + "']", this.elements.body).focus();
                    }
                }
            });
            return self;
        };

        self.showLoading = function() {
            if (elements && elements.loading) {
                elements.loading.fadeIn(120);
            }
        };

        self.hideLoading = function() {
            if (elements && elements.loading) {
                elements.loading.fadeOut(40);
            }
        };

        // Private
        var _init = function() {
            $window = $(window).resize(_center).focus();
            options = $.extend({}, defaultOptions);
        };

        // creates new set of options based on defaults + opts param
        var _setOptions = function(opts) {
            options = $.extend({}, defaultOptions, typeof opts === "object" ? opts: {} );
        };

        var _stashOpts = function() {
            if (active === true && done !== true && typeof options !== "undefined") {
                _trail.add(options);
            }
        };

        var _focus = function(el) {
            $window.focus();
            el.focus();
        };

        // Show the dialog
        var _show = function(opts) {

            _stashOpts();
            done = false;
            _setOptions(opts);

            // if url, it's an ajax dialog
            if (options.url) {
                _callback("beforeAjax");
                Core.ajax({
                    "url": options.url,
                    "cache": false,
                    "success": function(resp) {
                        done = true;
                        if (resp.callbacks) {
                            for (var k in resp.callbacks) {
                                if (typeof resp.callbacks[k] === "string") {
                                    try {
                                        resp.callbacks[k] = eval("(function(){" + resp.callbacks[k] + "})");
                                    } catch (e) {
                                        if (console && console.log) {
                                            console.log(e);
                                        }
                                    }
                                }
                            }
                        }
                        _show(resp);
                    },
                    "error": function(resp) {
                        _error("Error", "There was a problem loading this dialog box");
                    },
                    "dataType": "json"
                });

            // else, it's static
            } else {
                _callback("beforeShow");
                _keypress_bind();
                _load();

                if (! active) {
                    elements.dialog.fadeIn(105, function(){
                        _focus(elements.dialog);
                        _callback("afterShow");
                    });

                    elements.dim.fadeIn(100);

                    setTimeout(_center, 10);
                    active = true;
                } else {
                    _focus(elements.dialog);
                    _center();
                    _callback("afterShow");
                }
            }
        };

        // Close the dialog
        var _close = function(e) {

            _callback("beforeClose");
            _keypressUnbind();
            _hide();

            if (e) {
                e.stopPropagation();
                e.preventDefault();
            }

            _callback("afterClose");

            return false;
        };

        // Close dialog - ignore trail
        var _closeAll = function(e) {

            _callback("beforeCloseAll");
            _trail.clear();
            _close(e)
            _callback("afterCloseAll");

            return false;
        };

        // Hide
        var _hide = function() {
            if (active) {
                elements.dialog.fadeOut(50);
                elements.dim.fadeOut(85);
                active = false;
            }
        };

        // Error
        var _error = function(title, message) {
            var okay = $("<button/>")
                .on("click", self.close)
                .html("OK");

            var form = $("<form/>")
                .on("submit", function(e){
                    e.stopPropagation();
                    return false;
                })
                .addClass("center")
                .append($("<center/>").append(okay));

            var body = $("<div/>")
                .addClass("padded error")
                .html(message);

            _show({
                "css": {
                    "width": "500px"
                },
                "content": {
                    "title": title || "Error",
                    "body": body,
                    "foot": form
                },
                "callbacks": {
                    "afterShow": function() {
                        _focus($("button:first", elements.foot));
                    }
                },
                "closekeys": [ Keys.ESCAPE ]
            });
        };

        // Load
        var _load = function() {

            _callback("beforeLoad");

            if (typeof elements.dialog === "undefined") {

                elements.title = $("<div/>").addClass("title");
                elements.close = $("<div/>").addClass("close").click(_closeAll);

                // Dim
                elements.dim = $("<div/>")
                    .addClass("dim")
                    .appendTo("body")
                    .on("click", function(e){
                        elements.dialog.focus();
                    })
                    .on("focus", function(e){
                        elements.dialog.focus();
                    });

                // CoreDialog
                elements.dialog = $("<div/>")
                    .addClass("CoreDialog")
                    .appendTo("body");

                // Form
                elements.form = $("<form/>")
                    .addClass("inner")
                    .on("submit", function(e){
                        e.preventDefault();
                    })
                    .appendTo(elements.dialog);

                // Head
                elements.head = $("<div/>")
                    .addClass("head")
                    .append(elements.title)
                    .append(elements.close)
                    .appendTo(elements.form);

                // Body
                elements.body = $("<div/>")
                    .addClass("body")
                    .append(
                        $("<div/>")
                            .addClass("loading")
                            .html("Loading...")
                    )
                    .appendTo(elements.form);

                // Foot
                elements.foot = $("<div/>")
                    .addClass("foot")
                    .appendTo(elements.form);

                // Spinner
                elements.spinner = _spinner();

                // Loading Inner
                elements.loadingInner = $("<div/>")
                    .addClass("loading-inner")
                    .append(
                        $("<div/>").addClass("loading-message").text("Loading...")
                    )
                    .append(elements.spinner);

                // Loading
                elements.loading = $("<div/>")
                    .addClass("loading")
                    .append(
                        $("<div/>")
                            .addClass("loading-outer")
                            .append(elements.loadingInner)
                    )
                    .appendTo(elements.form);
            } else {
                // Remove any lingering event handlers that might have been applied to the dialog
                elements.form.off();
                elements.head.off();
                elements.body.off();
                elements.foot.off();
                elements.dialog.off();
            }

            // CSS
            if (options && options.css) {
                elements.dialog
                    .css(options.css)
            }

            // Body
            if (options.url) {
                _setBody(options.url);
            } else {
                _setBody(options.content.body || "");
            }

            // Title
            if (options.content.title) {
                _setTitle(options.content.title);
            }

            // Foot
            if (options.content.foot) {
                _setFoot(options.content.foot);
            } else {
                _hideFoot();
            }

            _callback("afterLoad");
        };

        // Center dialog
        var _center = function() {
            if (elements && active) {

                // Resize dialog (if needed)
                elements.body.css("max-height", Math.max($window.height() - 150, 200));

                // Dialog
                elements.dialog
                    .css({
                        "position": "fixed",
                        "top":  (($window.height() - elements.dialog.outerHeight()) / 2) + "px",
                        "left": (($window.width() - elements.dialog.outerWidth()) / 2) + "px"
                    });

                // Loading
                elements.loading
                    .css({
                        "height": elements.form.innerHeight() + "px",
                        "width":  elements.form.innerWidth() + "px"
                    });
            }
        };

        // Sets
        var _setTitle = function(el) {
            elements.title.empty();
            if (typeof el === "string") {
                elements.title.html(el);
            } else {
                el.clone(true, true)
                    .appendTo(elements.title);
            }
        };
        var _setBody = function(el) {
            elements.body.empty();
            if (typeof el === "string") {
                elements.body.html(el);
            } else {
                el.clone(true, true)
                    .appendTo(elements.body);
            }
        };
        var _setFoot = function(el) {
            elements.foot.empty();
            if (typeof el === "string") {
                elements.foot.html(el);
            } else {
                el.clone(true, true)
                    .appendTo(elements.foot);
            }
            elements.foot.show();
        };

        // Hides
        var _hideFoot = function(el) {
            elements.foot.hide();
        };

        // Keypress listeners
        var _keypressSet;

        var _keypress_bind = function() {
            //_keypressUnbind();
            if (! _keypressSet) {
                // don't bind the key listener instantly
                // certain keys (eg. return) could be in up/down state while this dialog is loading
                // creating a thread avoids this problem
                setTimeout(function(){
                    $window.on("keyup", _keypressHandler);
                }, 50);
                _keypressSet = true; //don't set it twice
            }
        };

        var _keypressUnbind = function() {
            if (_keypressSet) {
                $window.off("keyup", _keypressHandler);
                _keypressSet = false;
            }
        };

        var _keypressHandler = function(e) {
            var code = e.keyCode || e.which;
            if ($.inArray(code, options.closekeys) !== -1) {
                setTimeout(function(){ _close(e); }, 0);
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        };

        var _callback = function(fnName, context) {
            if (typeof options.callbacks[fnName] === "function") {
                options.callbacks[fnName].call(context || _callbackSelf);
            }
        };

        var _spinner = function() {
            return new Spinner({
                lines:     13,          // The number of lines to draw
                length:    2,           // The length of each line
                width:     4,           // The line thickness
                radius:    11,          // The radius of the inner circle
                corners:   1,           // Corner roundness (0..1)
                rotate:    57,          // The rotation offset
                color:     '#000',      // #rgb or #rrggbb
                speed:     1,           // Rounds per second
                trail:     44,          // Afterglow percentage
                shadow:    true,        // Whether to render a shadow
                hwaccel:   false,       // Whether to use hardware acceleration
                className: 'spinner',   // The CSS class to assign to the spinner
                zIndex:    2e9,         // The z-index (defaults to 2000000000)
                top:       'auto',      // Top position relative to parent in px
                left:      'auto'       // Left position relative to parent in px
            }).spin().el;
        };

        // Trail/History
        var _trail = (function() {
            var self = {},
                arr  = [];

            self.add = function(args) {
                arr.push(args);
            };
            self.get = function() {
                while (arr.length > 0) {
                    var last = arr.pop();
                    if (typeof last !== "undefined") {
                        return last;
                    }
                }
            };

            self.peek = function() {
                if (arr.length) {
                    return arr[arr.length - 1];
                }
            };

            self.pop = function() {
                self.get();
            };

            self.count = function() {
                return arr.length;
            };

            self.clear = function() {
                arr = [];
            };

            return self;
        })();

        _init();

        return self;

    })();
})(jQuery);

$(function(){
    alert  = CoreDialog.alert;
    error  = CoreDialog.error;
    prompt = CoreDialog.prompt;
});

/*

$(function(){
    //CoreDialog.showUrl("dialog.json");

    CoreDialog.alert("heya1", "Alert1");
    CoreDialog.alert("heya2", "Alert2");


    CoreDialog.prompt(
        "How long should this IP be banned for?",
        "Ban Length",
        [
            {
                "type": "textbox",
                "label": "Ban Length",
                "name": "ban_length",
                "value": 10,
                "units": "Hours",
                "css": {
                    "width": "60px"
                }
            },
            {
                "type": "textarea",
                "label": "Ban Length",
                "name": "ban_length",
                "css": {
                    "width": "80%",
                    "height": "60px"
                }
            },
            {
                "type": "select",
                "label": "Do what",
                "name": "blop",
                "value": 10,
                "options": {
                    "yes": "Yes",
                    "no": "No",
                    "maybe": "Maybe"
                }
            }
        ],
        [
            {
                "label": "Ban",
                "action": function(form){ alert("Banned! " + $("input", form).val() ); this.close(); },
                "cssClass": "error"
            }
        ]
    );


    CoreDialog.confirm("Are you sure you want to?", "Are You Sure?", [
        {
            "value": "yes",
            "label": "Definitely",
            "action": function(){ alert("Yes"); this.close(); },
            "cssClass": "good"
        },
        {
            "value": "no",
            "label": "Cancel",
            "action": function(){ alert("No"); this.close(); },
            "cssClass": "error"
        }
    ]);

});

*/
