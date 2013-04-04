ProgressBar = function(opts) {
    opts = $.extend({
        percent:   0
    }, opts);

    var self = {},
        bar,
        inner,
        label_below,
        label_above;
    
    self.update = function(percent) {
        var val = Math.min(Math.round(percent, 1), 100) + "%";
        label_below.text(val);
        label_above.text(val);        
        inner.css("width", Math.min(percent, 100) + "%");
        if (bar.innerWidth()) {
            label_above.css("width", bar.innerWidth());
        }
    };
    
    self.html = function() {
        return bar;
    };
    
    var init = function() {
        label_below = $("<label/>").on("load", function(e) {
            self.update(Math.min(opts.percent, 100));
        });
        label_above = $("<label/>").on("load", function(e) {
            self.update(Math.min(opts.percent, 100));
        });
        inner = $("<div/>").append(label_above);
        bar   = $("<div/>").append(label_below).addClass("progressbar").append(inner);
        
        // Init    
        self.update(Math.min(opts.percent, 100));
    }
    
    init();
    
    return self;
};