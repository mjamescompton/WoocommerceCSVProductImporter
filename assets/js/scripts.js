(function ($) {

    var callPosition = 0;

    var CH_Upload = {

        init: function() {
            console.log('connected');
            this.bindActions();
        },

        render: function() {
            console.log(chUploadCSV);

            var html = '<div id="ch-progress">';
            html  += '<div id="ch-progress_bar"></div>';
            html  += '</div>';

            html  += "<table class='table' id='move_images' style='margin-top=40px'>";
            html  += "<thead><tr>";

            $.each(chUploadCSV[0], function( index, value ){
                html += '<th>' + index + '</th>';
            });

            html  += "</tr></thead>";
            html  += "<tbody>";

            $.each(chUploadCSV, function( index, value ){

                html  += '<tr>';

                $.each(value, function( i, val ){
                    html += '<td>' + val + '</td>';
                });

                html  += '</tr>';

            });

            html  += "</tbody>";
            html  += "</table>";
            html  += "<button class='btn btn-primary' id='upload_products' value='start'>Start</button>";

            return html;

        },

        uploadProduct: function() {
            if ( $('#upload_products').val() === 'stop' && callPosition  <=  chUploadCSV.length - 1 ) {
                var term = chUploadCSV [ callPosition ];

                if ($('.progress').length == 0 ) {
                    $('.wrap').append(this.renderProgress());
                }

                $.ajax({
                    type : 'POST',
                    url : ch_uploads.ajaxurl,
                    data : {
                        action : "add_product",
                        term : term,
                    },
                    success : function( data ) {
                        data = JSON.parse(data);
                        var progress = ( 100 / chUploadCSV.length ) * ( callPosition + 1 );
                        $('.progress-bar').width( progress + '%');
                        callPosition += 1;
                        console.log(data);
                        CH_Upload.uploadProduct();
                    }
                });


            } else {
                $('#upload_products').val('start');
                $('#upload_products').text('start');
            }
        },

        renderProgress: function() {

            var html = '<div class="progress" style="margin-top: 20px">';
            html  += '<div class="progress-bar bg-success" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>';
            html  += '</div>';

            return html;
        },

        bindActions: function() { 

            if (typeof chUploadCSV !== 'undefined') {
                console.log('exists');
                $('.wrap').append(this.render());
            }

            $('#upload_products').on('click' , function(e) {
                $(this).val($(this).val() == 'stop' ? 'start' : 'stop');
                $(this).text($(this).val());
                CH_Upload.uploadProduct();
            });
        }

    }

    CH_Upload.init();
})(jQuery);