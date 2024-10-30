(function ($) {
    'use strict';



    //in slack post listing enable custom switch button

    $(document).ready(function($){

        $('.chosen').chosen({});

       /* $('#cbxslacktokenurltriger').on('click', function (event) {
            event.preventDefault();

            new Clipboard('#cbxslacktokenurltriger');
        });*/

		//select all text on click of shortcode text
		$('#cbxslacktokenurltriger').on("click", function (event) {
		    event.preventDefault();

			var $this = $(this);

		    var $target = $('#cbxslacktokenurl');

			$target.focus();
			$target.select();
			try {
				document.execCommand("copy");

			} catch (err) {

			}

		});


        //slack edit screen event section tab style
        $('.nav-tab-wrapper a').on('click', function (event) {
            event.preventDefault();

            $(this).siblings().removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            $('section.cbxslackeventsection').hide();
            $('section.cbxslackeventsection').eq($(this).index()).show();
            //return false;
        });


        //send test notification
        $('.cbxwpslack_test').on('click', function (e) {
            e.preventDefault();
            var $this = $(this);



            var serviceurl = $this.data('serviceurl');
            var channel    = $this.data('channel');
            var username   = $this.data('username');
            var iconemoji  = $this.data('iconemoji');
            var busy = parseInt($this.data('busy'));

            if(serviceurl != '' && channel != '' && username != ''){
                if(busy == 0){
					$this.data('busy', 1);
					$('.cbxwpslack_ajax_icon').show();
					//ajax call for sending test notification
					$.ajax({
						type: "post",
						dataType: "json",
						url: cbxwpslack.ajaxurl,
						data: {
							action: "cbxwpslack_test_notification",
							security: cbxwpslack.nonce,
							message: cbxwpslack.message,
							serviceurl: serviceurl,
							channel: channel,
							username: username,
							iconemoji: iconemoji,
						},
						success: function (data, textStatus, XMLHttpRequest) {
							$this.data('busy', 0);
							$('.cbxwpslack_ajax_icon').hide();
							//$('<p>' + cbxwpslack.success + '</p>').insertAfter($this);
                            alert(cbxwpslack.success);
						}// end of success
					});// end of ajax
                }

            }
            else{
				//$('<p>' + cbxwpslack.success + '</p>').insertAfter($this);
                alert(cbxwpslack.test_noti_noparam);
            }

        });


        //for incoming
        var elem = document.querySelector('.cbxslackjs-switch');
        var elems = Array.prototype.slice.call(document.querySelectorAll('.cbxslackjs-switch'));

        elems.forEach(function(changeCheckbox) {
            changeCheckbox.onchange = function() {
                //changeField.innerHTML = changeCheckbox.checked;
                //console.log(changeCheckbox.checked);
                var enable = (changeCheckbox.checked)? 1: 0;
                var postid = $(changeCheckbox).attr('data-postid');
                //ajax call for sending test notification
                jQuery.ajax({
                    type: "post",
                    dataType: "json",
                    url: cbxwpslack.ajaxurl,
                    data: {
                        action: "cbxwpslack_enable_disable",
                        security: cbxwpslack.nonce,
                        enable: enable,
                        postid:postid
                    },
                    success: function (data, textStatus, XMLHttpRequest) {
                        //console.log(data);
                    }// end of success
                });// end of ajax
            };

            var switchery = new Switchery(changeCheckbox);
        });

        //for outgoing
        var elem = document.querySelector('.cbxslackjsout-switch');
        var elems = Array.prototype.slice.call(document.querySelectorAll('.cbxslackjsout-switch'));

        elems.forEach(function(changeCheckbox) {
            changeCheckbox.onchange = function() {
                //changeField.innerHTML = changeCheckbox.checked;
                //console.log(changeCheckbox.checked);
                var enable = (changeCheckbox.checked)? 1: 0;
                var postid = $(changeCheckbox).attr('data-postid');
                //ajax call for sending test notification
                $.ajax({
                    type: "post",
                    dataType: "json",
                    url: cbxwpslack.ajaxurl,
                    data: {
                        action: "cbxwpslackout_enable_disable",
                        security: cbxwpslack.nonce,
                        enableout: enable,
                        postid:postid
                    },
                    success: function (data, textStatus, XMLHttpRequest) {
                        //console.log(data);
                    }// end of success
                });// end of ajax
            };

            var switchery = new Switchery(changeCheckbox);
        });
    });

})(jQuery);
