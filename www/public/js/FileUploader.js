FileUploader = function(opts) {
    opts = $.extend({
        holderDiv:  null
    }, opts);

    var self               = {},
        currentlyUploading = false,         // is the uploader currently operating
        currentFolderId,
        breadcrumbs        = [],
        queue              = new Queue(),
        elements           = {},            // all core elements on the page
        dropZoneActive     = false,         // is the drop zone active
        headerHeight,                       // the height of the page header
        selectedFiles      = [],            // files currently selected
        shiftDown          = false,         // is the shift key pressed
        metaDown           = false,         // is the command/control key pressed
        lastSelected;

    /**
    * Public Functions
    */
    self.addFile = function(info) {

        if (elements.browserEmpty.css("display") === "block") {
            elements.browserEmpty.hide();
            elements.ulFiles.show();
        }

        var row = _newFileRow(info);
        elements.ulFiles.prepend(row);
        row.trigger("update", info); //this makes FF happy
    };

    self.loadFolder = function(id) {
        _loadFolder(id);
    };

    /**
    * Private Functions
    */
    var _uploadNextFile = function() {
        var item = queue.dequeue();

        if (item && item.file) {

            var uploader = CoreUploader({
                file:      item.file,
                url:       '/account/files/ajax/upload',
                chunkSize: 1048576,
                onGetFileInfo: function(info) {
                    item.row = _getRow(info);
                    if (! item.row) {
                        item.row = _newFileRow(info, $("li", elements.ulFiles).length + 1);
                    }
                    _addRow(item.row);
                    item.row.trigger("update", [ info ]);
                },
                onChunkStart: function(offset, length){

                },
                onChunkComplete: function(offset, length, response){
                    item.row.trigger("update", [ response.file ]);
                },
                onComplete: function(size){
                    _uploadNextFile();
                },
                onGetFileInfoFailed: function(response){
                    _uploadNextFile();
                },
                onChunkFailed: function(offset, length, response){
                    item.row.trigger("update", [ { failed: true } ] );
                }
            });

            uploader.start();
        }
    };

    var _showBrowserList = function() {
        elements.ulFiles.css("display", "block");
        elements.browserEmpty.css("display", "none");
        _resizeWindow();
    };

    var _hideBrowserList = function() {
        elements.ulFiles.css("display", "none");
        elements.browserEmpty.css("display", "block");
        _resizeWindow();
    };

    var _updateRowClasses = function() {
        $("li:odd", elements.ulFiles).addClass("on");
        $("li:even", elements.ulFiles).removeClass("on");
    };

    var _addRow = function(li) {
        elements.ulFiles.prepend(li);
        _updateRowClasses();
    };

    var _loadFolder = function(id) {
        Core.ajax({
            url: "/account/files/ajax/folder/children" + (id ? "/id:" + parseInt(id) : ""),
            success: function(response) {
                currentFolderId = parseInt(id) || null;
                Core.changeUrl("/account/files" + (id ? "/id:" + parseInt(id) : ""), "File Uploads " + id);
                elements.ulFiles.empty();
                if (response.folders) {
                    _showBrowserList();
                    var empty = true;
                    for (var k in response.folders) {
                        elements.ulFiles.prepend(_newFolderRow(response.folders[k]));
                        empty = false;
                    }

                    if (empty) {
                        _hideBrowserList();
                    }
                } else {
                    _hideBrowserList();
                }

                breadcrumbs = response.breadcrumbs;
                _refreshBreadcrumbs();

                elements.h2.text(response.breadcrumbs[response.breadcrumbs.length - 1].name);
            },
            error: function() {
                error("Could not load folder contents");
            }
        });
    };

    var _newFolderRow = function(info) {
        _showBrowserList();

        return $("<li>")
            .data("info", info)
            .data("selected", false)
            .attr("id", "folder-" + info.id)
            .text(
                info.name
            )
            .append(
                $("<button>")
                    .text("Delete")
                    .addClass("delete error")
                    .on("click", function(e){
                        e.preventDefault();

                        var li = $(this).parents("li");
                        CoreDialog.confirm(
                            "Are you sure you want to delete \"" + li.data("info").name + "\"?",
                            "Delete Folder?",
                            [
                                {
                                    "value": "yes",
                                    "label": "Delete",
                                    "action": function(){

                                        Core.ajax({
                                            url: "/account/files/ajax/folder/delete/id:" + li.data("info").id,
                                            success: function(response) {
                                                li.slideUp(200, function(){
                                                    $(this).remove();
                                                });
                                            },
                                            error: function() {
                                                error("Could not delete folder");
                                            }
                                        });

                                        this.close();
                                    },
                                    "cssClass": "error",
                                    "focused": true
                                },
                                {
                                    "label": "Cancel",
                                    "action": function(){
                                        this.close();
                                    }
                                }
                            ],
                            {
                                cssClass: "padded error"
                            }
                        );
                    })
            )
            .on("dblclick", function(e) {
                _loadFolder(
                    $(this).data("info").id
                );
            })
            .on("mousedown", function(e) {

                var li = $(this);

                if (shiftDown || metaDown) {
                    if (shiftDown) {

                        // is before
                        if (lastSelected.nextAll("li#folder-" + li.data("info").id, elements.ulFiles).length !== 0) {
                            lastSelected.nextUntil(li, "li").each(function(){
                                _selectFile($(this));
                            });
                            // is after
                        } else {
                            li.nextUntil(lastSelected, "li").each(function(){
                                _selectFile($(this));
                            });
                        }
                        _selectFile(li);
                    } else {
                        if (li.data("selected")) {
                            _deselectFile(li);
                        } else {
                            _selectFile(li);
                        }
                    }
                } else {

                    if (selectedFiles.length > 1 && li.data("selected")) {

                    } else {
                        _deselectAll();

                        if (li.data("selected")) {
                            _deselectFile(li);
                        } else {
                            _selectFile(li);
                        }
                    }
                }
            });
    };

    var _newFileRow = function(info, i) {
        _showBrowserList();

        return $("<li>")
            .data("info", info)
            .data("selected", false)
            .attr("id", "file-" + info.id)
            .append(
                $("<table>")
                    .css("width", "100%")
                    .append(
                        $("<tr>")
                            .append(
                                $("<td>")
                                    .addClass("label")
                                    .text(info.label)
                            )
                            .append(
                                $("<td>")
                                    .addClass("size")
                                    .text(info.sizeCurrent.filesize())
                            )
                            .append(
                                $("<td>")
                                    .addClass("updated-on")
                                    .text(info.updatedOn || "--")
                            )
                            .append(
                                $("<td>")
                                    .addClass("status")
                                    .html(info.isComplete ? '<span class="complete">Complete</span>' : '<span class="incomplete">Incomplete</span>')
                            )
                    )
            )
            .on("update", function(e, info) {
                e.preventDefault();

                var li = $(this);

                // Size
                if (typeof info.sizeCurrent !== "undefined") {
                    $("td.size", li).text(info.sizeCurrent ? info.sizeCurrent.filesize() : "--");
                }

                // Updated On
                if (typeof info.updatedOn !== "undefined") {
                    $("td.updated-on", li).text(info.updatedOn || "--");
                }

                // Label
                if (typeof info.label !== "undefined") {
                    $("td.label", li).text(info.label);
                }

                // Is Complete
                if (typeof info.isComplete !== "undefined") {
                    $("td.status", li).html(info.isComplete ? '<span class="complete">Complete</span>' : '<span class="incomplete">Incomplete</span>');
                }
            })
            .on("mousedown", function(e) {

                var li = $(this);

                if (shiftDown || metaDown) {
                    if (shiftDown) {

                        // is before
                        if (lastSelected.nextAll("li#file-" + li.data("info").id, elements.ulFiles).length !== 0) {
                            lastSelected.nextUntil(li, "li").each(function(){
                                _selectFile($(this));
                            });
                        // is after
                        } else {
                            li.nextUntil(lastSelected, "li").each(function(){
                                _selectFile($(this));
                            });
                        }
                        _selectFile(li);
                    } else {
                        if (li.data("selected")) {
                            _deselectFile(li);
                        } else {
                            _selectFile(li);
                        }
                    }
                } else {

                    if (selectedFiles.length > 1 && li.data("selected")) {

                    } else {
                         _deselectAll();

                        if (li.data("selected")) {
                            _deselectFile(li);
                        } else {
                            _selectFile(li);
                        }
                    }
                }
            });
    };

    var _getRow = function(info) {
        var li = $("li#file-" + info.id, elements.ulFiles);
        return li.length ? li : null;
    };

    var _activateDropZone = function() {
        if (! dropZoneActive) {
            dropZoneActive = true;
            elements.dropZone.show();
            elements.dim.fadeIn(300);
        }
    };

    var _deactivateDropZone = function() {
        if (dropZoneActive) {
            dropZoneActive = false;
            elements.dropZone.hide();
            elements.dim.fadeOut(300);
        }
    };

    var _refreshBreadcrumbs = function() {
        elements.breadcrumb.empty();
        for (var k in breadcrumbs) {
            elements.breadcrumb
                .append(
                    $("<li/>")
                        .append(
                            $("<a/>")
                                .data("id", breadcrumbs[k].id)
                                .text(breadcrumbs[k].name)
                                .on("click", function(e) {
                                    _loadFolder($(this).data("id"));
                                })
                        )
                );
        }
    };

    var _renameFile = function(li) {
        if (li) {
            var info = li.data("info");
            var blur = function() {
                td.text(info.label);
            };
            var input = $("<input/>")
                .val(info.label)
                .blur(blur);

            var td = $("td.label", li)
                .empty()
                .append(
                    $("<form/>")
                        .append(input)
                        .on("submit", function(e){
                            e.preventDefault();
                            console.log("save");
                            td.text(info.label);
                        })
                        .on("keydown", function(e){
                            if (e.which === 27) {
                                blur();
                            }
                        })
                );

            input.focus();
        }
    };

    // Select a file
    var _selectFile = function(li) {
        var id = li.data("info").id;
        if ($.inArray(id, selectedFiles) === -1) {
            selectedFiles.push(id);
        }
        li.data("selected", true);
        li.addClass("selected");

        lastSelected = li;
    };

    // Deselect a file
    var _deselectFile = function(li) {
        var id = li.data("info").id;
        var k = $.inArray(id, selectedFiles);
        if (k !== -1) {
            selectedFiles.splice(k, 1);
            li.data("selected", false);
            li.removeClass("selected");

            if (lastSelected === li) {
                lastSelected = null;
            }
        }
    };

    // New Folder
    var _newFolder = function() {
        prompt(
            null,
            "New Folder",
            [
                {
                    "type": "text",
                    "label": "Folder Name",
                    "name": "name",
                    "css": {
                        "width": "300px"
                    }
                }
            ],
            [
                {
                    "label": "Create folder",
                    "action": function(){

                        var nameInput = $("input[name='name']", this.elements.form);
                        var self = this;

                        Core.ajax({
                            url: "/account/files/ajax/folder/insert",
                            data: {
                                parent_id: currentFolderId,
                                name: nameInput.val()
                            },
                            success: function(response) {
                                if (response.status === "good") {
                                    self.close();
                                    _showBrowserList();
                                    elements.ulFiles.prepend(_newFolderRow(response.info))
                                } else {
                                    // Errors
                                    if (response.errors) {
                                        $("div.error", self.elements.form).hide();

                                        for (var k in response.errors) {
                                            $("[name='" + k + "']", self.elements.form)
                                                .addClass("error")
                                                .parents("tr").find("div.error")
                                                .text(response.errors[k])
                                                .show();
                                        }
                                    }

                                    // Message
                                    $("div.padded:first", self.elements.body)
                                        .removeClass("info")
                                        .addClass("error")
                                        .text(response.message || "Could not create your folder, please try again")
                                        .show();
                                }
                            },
                            error: function() {
                                error("Could not create your folder, please try again");
                            }
                        });
                    },
                    "cssClass": "good"
                }
            ]
        );
    };

    var _resizeWindow = function(e) {
        if (typeof $window !== "undefined") {
            var $this = $(this);

            elements.browser
                .height($this.height() - headerHeight - elements.bowserHeader.outerHeight())
                .width($window.width() - elements.sideBar.outerWidth());
            elements.breadcrumb.width($window.width() - elements.sideBar.outerWidth());
            elements.bowserHeader.width($window.width() - elements.sideBar.outerWidth());
        }
    };

    /**
     * Deselect all files
     * @private
     */
    var _deselectAll = function() {
        lastSelected = null;
        var selected = selectedFiles.slice();
        var len = selected.length;
        for (var i=0; i < len; i++) {
            var li = $("li#file-" + selected[i], elements.ulFiles);
            if (li.length) {
                _deselectFile(li);
            }
        }
    };

    /**
     * Init
     * @private
     */
    var _init = function() {

        if (! window.File || ! window.FileReader || ! window.FileList || ! window.Blob) {
            error('The FileUploader is not fully supported in this browser.');
            throw { name: "unsupported browser", message: "Your browser does not have the required libraries available to run FileUploader." };
        }

        headerHeight = $("div.head").outerHeight();
        headerHeight = $("div.header").outerHeight();

        var $window = $(window)
            .resize(_resizeWindow);

        // Init Page Elements

        // Sidebar
        elements.sideBar = $("<div/>")
            .addClass("sidebar")
            .appendTo(opts.holderDiv)
            .height($window.height() - headerHeight);

        elements.sideBar
            .append(
                $("<div/>")
                    .addClass("sidebar-files")
                    .text("Files")
                    .on("click", function(e){
                        e.preventDefault();

                    })
            )
            .append(
                $("<div/>")
                    .addClass("sidebar-files")
                    .text("Products")
                    .on("click", function(e){
                        e.preventDefault();

                    })
            )
            .append(
                $("<div/>")
                    .addClass("sidebar-files")
                    .text("Add Folder")
                    .on("click", function(e){
                        e.preventDefault();
                        _newFolder();
                    })
            );

        // File list header
        elements.bowserHeader = $("<div/>")
            .addClass("browser-header")
            .appendTo(opts.holderDiv)
            .width($window.width() - elements.sideBar.outerWidth());

        // Folder breadcrumb
        elements.breadcrumb = $("<ul/>")
            .addClass("browser-breadcrumb")
            .appendTo(elements.bowserHeader);

        _refreshBreadcrumbs();

        // Toolbar New Folder
        elements.toolbarNewFolder = $("<button/>")
            .text("New Folder")
            .on("click", function(e){
                e.preventDefault();
                _newFolder();
            });

        // H2 Header
        elements.h2 = $("<h2/>")
            .text("Trance")
            .appendTo(elements.bowserHeader);

        // Toolbar Upload
        elements.toolbarUpload = $("<button/>")
            .text("Upload File")
            .on("click", function(e){
                e.preventDefault();
            });

        // Toolbar Delete
        elements.toolbarDelete = $("<button/>")
            .text("Delete")
            .addClass("right error")
            .on("click", function(e){
                e.preventDefault();
            });

        // Toolbar
        elements.toolbar = $("<div/>")
            .addClass("toolbar")
            .append(elements.toolbarNewFolder)
            .append(elements.toolbarUpload)
            .append(elements.toolbarDelete)
            .appendTo(elements.bowserHeader);

        // File list header
        elements.bowserFileHeader = $("<div/>")
            .addClass("file-header")
            .append(
                $("<table>")
                    .css("width", "100%")
                    .append(
                        $("<tr>")
                            .append(
                                $("<td>")
                                    .addClass("label")
                                    .text("File Name")
                            )
                            .append(
                                $("<td>")
                                    .addClass("size")
                                    .text("Size")
                            )
                            .append(
                                $("<td>")
                                    .addClass("updated-on")
                                    .text("Updated On")
                            )
                            .append(
                                $("<td>")
                                    .addClass("status")
                                    .html("Status")
                            )
                    )
            )
            .appendTo(elements.bowserHeader);

        // File browser
        elements.browser = $("<div/>")
            .addClass("browser")
            .appendTo(opts.holderDiv)
            .height($window.height() - headerHeight - elements.bowserHeader.outerHeight())
            .width($window.width() - elements.sideBar.outerWidth());

        // Files list
        elements.ulFiles = $("<ul/>")
            .css("display", "none")
            .appendTo(elements.browser);

        // Empty
        elements.browserEmpty = $("<div/>")
            .addClass("empty")
            .append(
                $("<div/>")
                    .addClass("big")
                    .text("Folder is empty")
            )
            .append(
                $("<div/>")
                    .addClass("small")
                    .text("Drag files here to upload")
            )
            .appendTo(elements.browser);

        CoreContext({
            activeZone: elements.ulFiles,
            items: [
                {
                    label: "New Folder",
                    action: function(e) {
                        _newFolder();
                    }
                },
                {
                    label: "Rename",
                    action: function(e) {
                        if (lastSelected) {
                            _renameFile(lastSelected);
                        }
                    }
                },
                {
                    label: "Move",
                    action: function(e) {
                        console.log("Move");
                    }
                },
                {
                    label: "Delete",
                    action: function(e) {
                        console.log("Delete");
                    }
                },
                {
                    label: "Reload",
                    action: function(e) {
                        console.log("Reload");
                    }
                }
            ]
        });

        // Screen dimmer
        elements.dim = $("<div/>")
            .addClass("dim")
            .appendTo("body")
            .append(
                $("<label/>")
                    .text("Drop files here to upload")
            );

        // Drop zone
        elements.dropZone = $("<div/>")
            .css({
                "position": "fixed",
                "top":      0,
                "left":     0,
                "width":   "100%",
                "height":  "100%",
                "z-index": 99999,
                "display": "none"
            })
            .appendTo("body")
            .on("dragleave", function(e){
                e.preventDefault();
                e.stopPropagation();
                if (dropZoneActive) {
                    _deactivateDropZone();
                }
            });

        // Drop listeners
        $("body")
            .on("keydown", function(e){
                switch (e.which) {
                    case 16: shiftDown = true; break;
                }
                metaDown = e.metaKey || e.ctrlKey;
            })
            .on("keyup", function(e){
                switch (e.which) {
                    case 16: shiftDown = false; break;
                }
                metaDown = e.metaKey || e.ctrlKey;
            })
            .on("dragenter", function(e){
                if (! dropZoneActive) {
                    _activateDropZone();
                }
            })
            .on("dragover", false)
            .on("drop", function(e){
                e.preventDefault();
                e.stopPropagation();
                _deactivateDropZone();

                var files = e.originalEvent.dataTransfer.files;

                for (var i=0; i < files.length; i++) {
                    queue.enqueue({
                       file: files[i],
                       row:  null
                    });
                }

                _uploadNextFile();
            });

        _hideBrowserList();
    }

    _init();

    return self;
};