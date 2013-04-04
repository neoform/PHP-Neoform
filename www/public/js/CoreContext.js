CoreContext = function(opts) {
    opts = $.extend({
        activeZone: null,
        items:      []
    }, opts);
    
    var self     = {},
        elements = {},
        menu,
        activeZoneTop;
    
    /**
    * Public
    */
    
    self.show = function() {
        if (! menu.is(":visible")) {
            menu.fadeIn(35);
        }
    };
    
    self.hide = function() {
        if (menu.is(":visible")) {
            menu.fadeOut(35);
        }
    };
    
    /**
    * Private
    */    
    var _init = function() {
    
        menu = $("<ul/>")
            .addClass("context-menu")
            .appendTo("body");
    
        var len = opts.items.length;
        for (var i=0; i < len; i++) {
            var item = opts.items[i];
            var li = $("<li/>")
                .text(item.label)                
                .on("click", { action: item.action }, function(e){
                    self.hide();
                    if (typeof e.data.action === "function") {
                        e.data.action.apply(this, e);
                    }
                });
                
            menu.append(li);
        }
        
        activeZoneTop = opts.activeZone.offset().top;
        
        // Context menu
        opts.activeZone
            .on("contextmenu", function(e) {
                e.preventDefault();
                self.show();
                
                menu.css({
                    "top":  (e.pageY - activeZoneTop) + "px", 
                    "left": e.pageX + "px"
                });
            })
            .on("click", function(e) {
                self.hide();
            });
            
        $(window).scroll(self.hide);
    
    };
    
    _init();
    
    return self;
};