CoreUploader = function(opts) {
    opts = $.extend({
        file:                null,
        url:                 null,
        chunkSize:           0,
        onGetFileInfo:       function(info){},
        onGetFileInfoFailed: function(info){},
        onChunkStart:        function(offset, length){},
        onChunkComplete:     function(offset, length, response){},
        onChunkFailed:       function(offset, length, response){},
        onComplete:          function(size){}
    }, opts);

    var self           = {},
        currentOffset  = 0,
        blob,
        id             = null,
        uploading      = false,
        active         = false;

    var getChunk = function() {

        if (currentOffset >= opts.file.size) {
            opts.onComplete(opts.file.size);
            return false;
        }
        var end = currentOffset + Math.min(opts.file.size - currentOffset, opts.chunkSize);

        // slice a blob off of the file
        if ($.browser.mozilla && opts.file.mozSlice) {
            return opts.file.mozSlice(currentOffset, end);
        } else {
            return opts.file.slice(currentOffset, end);
        }
    };

    var getFileInfo = function() {
        $.ajax({
            url: opts.url,
            type: "put",
            contentType: 'application/json',
            data: JSON.stringify({
                size: opts.file.size,
                name: opts.file.name
            }),
            success: function(data, textStatus, jqXHR) {
                if (data && data.status === "good") {
                    id            = data.file ? data.file.id : null;
                    currentOffset = data.file ? data.file.sizeCurrent : null;
                    opts.onGetFileInfo(data.file);
                    sendChunk();
                } else {
                    opts.onGetFileInfoFailed(data);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                //console.log(errorThrown);
            }
        });
    };

    var sendChunk = function() {
        if (! active) {
            return;
        }

        if (! id) {
            getFileInfo();
            return;
        }

        var blob = getChunk();

        if (! blob) {
            return;
        }

        opts.onChunkStart(currentOffset, blob.size + currentOffset);

        uploading = true;

		var httpRequest = new XMLHttpRequest();
        httpRequest.open("POST", opts.url + "/" + id + "/" + currentOffset + "/" + blob.size + "?" + Math.random(), true);
		httpRequest.onreadystatechange = function(e) {
    		if (httpRequest.readyState === 4) {
    		    uploading = false;

    		    try {
    		        var response = JSON.parse(httpRequest.responseText);
    		    } catch (err) {
    		        var response = {};
    		    }

    		    if (httpRequest.status === 200) {
                    if (response.status === "good") {
                        opts.onChunkComplete(currentOffset, blob.size, response);
                        currentOffset += blob.size;
                    } else {
                        if (opts.onChunkFailed(currentOffset, blob.size, response)) {
                            return;
                        }
                    }
                } else {
                    if (opts.onChunkFailed(currentOffset, blob.size, response)) {
                        return;
                    }
                }

                // next chunk if good, otherwise repeat this one
                sendChunk();
            }
		};

		// send the blob
        httpRequest.send(blob);
    };

    self.start = function() {
        active = true;
        if (! uploading) {
            sendChunk();
        }
    }

    self.stop = function() {
        active = false;
    }

    return self;
};