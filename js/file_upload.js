var current_search_line = 0;
var current_search_array = [];

function upload_csv(){

    //Reset auto search parameters
    current_search_line = 0;
    current_search_array = [];

    update_status('Uploading Files...');
    $('#progress-wrp').css('display','block');

    var file = $('#file_upload_input')[0].files[0];
    var upload = new Upload(file);

    // maby check size or type here with upload.getSize() and upload.getType()
    var file_type=upload.getType();
    if (file_type!='text/csv'){
        update_status('ERROR!: Please upload only csv files ...');
    }else{
        // execute upload
        upload.doUpload();
    }

    $('#records_table_body').html('');
    
}



var Upload = function (file) {
    this.file = file;
};

Upload.prototype.getType = function() {
    return this.file.type;
};
Upload.prototype.getSize = function() {
    return this.file.size;
};
Upload.prototype.getName = function() {
    return this.file.name;
};
Upload.prototype.doUpload = function () {
    var that = this;
    var formData = new FormData();

    // add assoc key values, this will be posts values
    formData.append("file", $('#file_upload_input')[0].files[0], this.getName());
    formData.append("upload_file", true);

    $.ajax({
        type: "POST",
        url: "handle_file_upload.php",
        xhr: function () {
            var myXhr = $.ajaxSettings.xhr();
            if (myXhr.upload) {
                myXhr.upload.addEventListener('progress', that.progressHandling, false);
            }
            return myXhr;
        },
        success: function (data) {
            // your callback here

            data = JSON.parse(data);
            current_search_array = data;

            console.log(current_search_array);

            update_status("File upload successful !");
            $('#progress-wrp').css('display','none');

            //Search for csv search list
            automatic_update_status();
        },
        error: function (error) {
            // handle error
        },
        async: true,
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        timeout: 60000
    });
};

Upload.prototype.progressHandling = function (event) {
    var percent = 0;
    var position = event.loaded || event.position;
    var total = event.total;
    var progress_bar_id = "#progress-wrp";
    if (event.lengthComputable) {
        percent = Math.ceil(position / total * 100);
    }
    // update progressbars classes so it fits your code
    $(progress_bar_id + " .progress-bar").css("width", +percent + "%");
    $(progress_bar_id + " .status").text(percent + "%");
};


function download_csv(){
    $("#records_table").tableToCSV();
}