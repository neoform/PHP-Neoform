var reader = new FileReader();

reader.onloadend = function(e) {            	
	if (e.target.readyState == FileReader.DONE) {
		
		var content = e.target.result;
		var chunkHash = b64_md5(content);
		
	}            	
};
reader.onerror = function(e) {
	console.log("error");
};

var getChunk = function() {

    var start = currentChunkNumber * opts.chunkSize;
    
    if (start >= opts.file.size) {
        opts.onComplete(opts.file.size);
        return false;
    }
    
    var end = start + Math.min(opts.file.size - start, opts.chunkSize);
    
    if ($.browser.mozilla && opts.file.mozSlice) {
        var blob = opts.file.mozSlice(start, len);
    } else {
        var blob = opts.file.slice(start, end); 
    }
    
    reader.readAsBinaryString(blob);    
    
    return true; 
};