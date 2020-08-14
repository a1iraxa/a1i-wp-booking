(function( $ ) {
	'use strict';

	 $(function() {
        $('#tb-booking__slot').on("change", function (e) {
            console.log($(this).val());
            var timeSlot = $(this).val().split('-');
            console.log( timeSlot[0] );
            console.log( timeSlot[1] );
            $('.tb-booking_timepicker').timepicker({
                timeFormat: 'h:mm p',
                interval: 30,
                minTime: timeSlot[0],
                maxTime: timeSlot[1],
                defaultTime: timeSlot[0],
                startTime: timeSlot[0],
                dynamic: false,
                dropdown: true,
                scrollbar: true
            });
        });


	 	// Prevent-from-submit-on-enter-press
	 	$(window).keydown(function(event){
 	        if(event.keyCode == 13) {
 	          event.preventDefault();
 	          return false;
 	        }
 	    });
	 	// Is element exists
 	    jQuery.fn.isExists = function() {
 	        return this.length;
 	    };

 	    // Is value exists
 	    jQuery.fn.isValueExists = function() {
 	        if (!this.isExists()) {
 	            return false;
 	        }
 	        return this.val().length;
 	    };

 	    // Is value empty
 	    jQuery.fn.isEmpty = function() {

 	        this.removeClass('digitsol-error');
 	        this.siblings('.error-message').remove();

 	        if (!this.isValueExists()) {
 	            this.after('<span class="error-message"> * Required</span>');
 	            this.addClass('digitsol-error');
 	            return true;
 	        }
 	        return false;
 	    };

 	    // Is select option select
 	    jQuery.fn.isSelected = function() {

 	        if (!this.isExists()) {
 	            return false;
 	        }

 	        this.find(":selected").parent().removeClass('digitsol-error');
 	        this.find(":selected").parent().siblings('.error-message').remove();

 	        if (this.val() === "" || this.val() === null) {
 	            this.find(":selected").parent().after('<span class="error-message"> * Required</span>');
 	            this.find(":selected").parent().addClass('digitsol-error');
 	            return false;
 	        }

 	        return true;
 	    };

 	    // Is radion checked
 	    jQuery.fn.isRadioChecked = function() {

 	        if( ! this.isExists()) {
 	            return false;
 	        }

 	        this.removeClass('digitsol-error');
 	        this.siblings('.error-message').remove();

 	        if( ! this.is(':checked') ){
 	            this.after('<span class="error-message"> * Please Select</span>');
 	            this.addClass('digitsol-error');
 	            return false;
 	        }

 	        return true;
 	    };

 	    // Is valid email address
 	    jQuery.fn.isEmail = function() {

 	        this.removeClass('digitsol-error');
 	        this.siblings('.error-message').remove();

 	        var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;

 	        if( ! regex.test($.trim(this.val())) ) { // not valid email
 	            this.after('<span class="error-message"> Not Valid Email!</span>');
 	            this.addClass('digitsol-error');
 	            return false;
 	        }
 	        return true;
 	    };

 	    // Is valid phone number
 	    jQuery.fn.isPhone = function() {
 	        this.removeClass('digitsol-error');
 	        this.siblings('.error-message').remove();

 	        var regex = /^\(?(\d{3})\)?[- ](\d{3})[- ](\d{4})$/;

 	        if( ! regex.test($.trim(this.val())) ) { // not valid email
 	            this.after('<span class="error-message"> Valid Phone: (800)-640-0599</span>');
 	            this.addClass('digitsol-error');
 	            return false;
 	        }
 	        return true;
 	    };

 	    // Get time slots
 	    var $booking_date_el = $('.tb-booking__date');
 	    $booking_date_el.on("change", function (e) {
 	    	$.ajax({
	            type:       'POST',
	            url:        TB_AJAX.ajax_url,
	            data:       {
	                action: 'tb_ajax_get_time_slots',
	                date:   this.value,
	                day:   $(this).find(':selected').attr('data-day')
	            },
	            success: function( result ) {

	            	// console.log(result.slots); // for debugging

	                if ( result.success ) {

	                	// update options in select slot element
	                	var $slot_el = $('.tb-booking__slot');

	                	// remove old slots options
	                   $slot_el.empty();

	                   // Add default empty valued option
	                   var default_option = new Option( 'Select Available Slot', '');
	                   default_option.selected = true;
	                   $slot_el.append(default_option);

	                   var option_disabled = false;
	                   var option_selected = false;
	                   var option_text = '';
	                   var option_value = '';

	                   $.each(result.slots.all, function(key,value) {
	                   		option_disabled = false;
	                   		option_selected = false;
	                   		option_value = value;
	                   		option_text = value;

	                   		if ( $.inArray( value, result.slots.booked ) !== -1 ) {
	                   			option_disabled = true;
	                   			option_text = `${value} Not Available`;
	                   		}

	                   		var option = new Option( option_text, option_value);
	                   		option.selected = option_selected;
	                   		if ( option_disabled ) {
	                   			option.disabled = option_disabled;
	                   		}
	                   		$slot_el.append(option);

	                   	});

	                } else {
	                    console.log( 'Error!' );

	                }

	            },
	            error: function() {

	            },

	        });
 	    });

 	    // Form Validator
        function __validateData(fields) {
            var validated = false;
            $.each(fields, function(key, value) {
                if( this.attr('type') == 'radio' ){
                    if( ! this.isRadioChecked() ) {
                        validated = false;
                        return false;
                    }
                }
                if ( this.is('input') ) {
                    if(this.isEmpty()){
                        validated = false;
                        return false;
                    }
                    if ( this.hasClass('validate-email') ) {
                        if ( !this.isEmail() ) {
                            validated = false;
                            return false;
                        }
                    }
                    if ( this.hasClass('validate-phone') ) {
                        if ( !this.isPhone() ) {
                            validated = false;
                            return false;
                        }
                    }
                    if ( this.hasClass('validate-ssn') ) {
                        if ( !this.isSSN() ) {
                            validated = false;
                            return false;
                        }
                    }
                }else if( this.is('select')){
                    if( ! this.isSelected() ) {
                        validated = false;
                        return false;
                    }
                }
                validated = true;
            });
            return validated;
        }

        var required_fields = {
            first_name: $('#tb-booking__first-name'),
            last_name: $('#tb-booking__last-name'),
            last_name: $('#tb-booking__last-name'),
            gender: $('#tb-booking__gender'),
            email: $('#tb-booking__email'),
            phone: $('#tb-booking__phone'),
            aaddress: $('#tb-booking__aaddress'),
            slot: $('#tb-booking__slot'),
        	arrival: $('#tb-booking__arrival-time'),
        };
        // on click submit
        var _form_submit_btn = $("#tb-booking__submit-btn");
        var _form_submit_btn_html = _form_submit_btn.val();
        _form_submit_btn.on( 'click', function (event) {
            event.preventDefault();

            if( __validateData(required_fields) ){
                var $form = $(this).closest('form');
                console.log($('#tb-booking__date').find(':selected').attr('data-day'));
                $.ajax({
                    type:       'POST',
                    url:        TB_AJAX.ajax_url,
                    data:       {
                        action: $('input[name="action"]').val(),
                        day: $('#tb-booking__date').find(':selected').attr('data-day'),
                        form:   $form.serialize()
                    },
                    beforeSend:function(){
                        _form_submit_btn.prop("disabled",true);
                        _form_submit_btn.val('Saving...!');
                    },
                    success: function( result ) {

                        if ( result.success ) {
                            if ( result.redirect ) {
                                window.location.href = result.redirect_to;
                            }
                            $('#tb-booking-form').html(`<h2>${result.msg}</h2>`);
                        } else {
                            alert(result.msg);
                            _form_submit_btn.prop("disabled",false);
                            _form_submit_btn.val(_form_submit_btn_html);
                        }
                    },
                    error: function() {
                        alert('Something went wrong. Try after page reload.');
                        _form_submit_btn.prop("disabled",false);
                        _form_submit_btn.val(_form_submit_btn_html);

                    },

                });
            }

        });
	 });

})( jQuery );
