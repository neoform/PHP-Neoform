
Core = (function(){

    var self = {},
        locale,
        ref;

    self.ready = function() {

        $.ajaxSetup({
            "type":     "post",
            "cache":    false,
            "dataType": "json",
            "error":    function(response) {
                CoreDialog.error("There was a problem with that request");
            }
        });

        $("div.head div.logo").on("click", function(e){ location.href = self.route("/"); });

        $("div.head a[name='logout']")
            .on("click", function(e){
                e.preventDefault();
                location.href = self.route("/account/logout") + "?" + Core.ref();
            });

        $("div.head a[name='login']")
            .on("click", function(e){
                e.preventDefault();
                console.log((locale ? "/" + locale : "") + "/account/ajax/dialog/login");
                CoreDialog.showUrl("/account/ajax/dialog/login");
            });

        $("div.head a[name='create-account']")
            .on("click", function(e){
                e.preventDefault();
                CoreDialog.showUrl("/account/ajax/dialog/create");
            });
    };

    self.locale = function(set) {
        if (typeof set !== "undefined") {
            if (set) {
                locale = set;
            }
        } else {
            return locale;
        }
    };

    self.ref = function(set) {
        if (typeof set !== "undefined") {
            if (set) {
                ref = set;
            }
        } else {
            return "rc=" + ref;
        }
    };

    self.route = function(url) {
        return (locale ? "/" + locale : "") + url;
    };

    self.changeUrl = function(url, title) {
        window.history.pushState({}, title, url);
    };

    self.ajax = function(options) {

        // Copy, since we modify the options prior to using it
        opts = $.extend(true, {}, options);

        opts.success = function(response) {
            CoreDialog.hideLoading();

            if (typeof response._ref !== "undefined") {
                self.ref(response._ref);
            }

            switch (response.status) {

                case "close":
                    CoreDialog.close();
                    break;

                case "login":
                    CoreDialog.showUrl(self.route("/account/ajax/dialog/login"));
                    break;

                case "captcha":
                    CoreDialog.showUrl(self.route("/account/ajax/dialog/captcha"));
                    break;

                case "fault":
                    CoreDialog.error(response.message, response.title);
                    break;

                default:
                    if (typeof options.success === "function") {
                        options.success.apply(this, arguments);
                    }
            }
        };

        opts.error = function() {
            CoreDialog.hideLoading();
            if (typeof options.error === "function") {
                options.error.apply(this, arguments);
            } else {
                CoreDialog.error("There seems to have been a problem with your request.");
            }
        };

        opts.url = self.route(opts.url);

        if (ref) {
            opts.url += "?" + Core.ref();
        }

        if (opts.get && opts.get.length) {
            for (var i in opts.get) {
                opts.url += (ref ? "&" : "?") + encodeURIComponent(opts.get[i]);
            }
        }

        CoreDialog.showLoading();
        return $.ajax(opts);
    };

    return self;
})();

Number.prototype.format = function(decimals) {
    if (typeof decimals !== "undefined") {
        var nStr = parseFloat(this).toFixed(decimals);
    } else {
        var nStr = this + '';
    }
    x = nStr.split('.');
    x1 = x[0];
    x2 = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }
    return x1 + x2;
};

Number.prototype.filesize = function(decimals) {
    var size = this;
    var units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    var i = 0;
    while(size >= 1024) {
        size /= 1024;
        ++i;
    }
    return size.toFixed(1) + ' ' + units[i];
};

$(Core.ready);
