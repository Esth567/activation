jQuery(document).ready(function($) {

    $('input[name="color_option"]').on('change', function() {
        if ($(this).val() === 'custom') {
            $('#color').prop('disabled', true); 
            $('#custom_color').show().prop('required', true);
        } else {
            $('#color').prop('disabled', false); 
            $('#custom_color').hide().prop('required', false);
        }
    });
       
 // Handle activation form submission
$('#form-details').on('submit', function(e) {
    e.preventDefault();

    // Show loading spinner
    $('#spinner').show();
    $('#form-details').addClass('form-blur');

    $('#form_error').html(''); // Clear previous error message
    $('#form_success').html(''); // Clear previous success message

    var formData = new FormData(this);
    formData.append('action', 'handle_activated_details'); 
    formData.append('activation_nonce', $('#activation_nonce').val()); 

    // Check the selected color
  var selectedColor = $('#color').val();
  var customColor = $('#custom_color').val().toLowerCase();

  // Handle color name submission
  if ($('#custom_color').is(':visible') && customColor) {
      switch(customColor) {
          case 'red':
          case 'yellow':
          case 'blue':
          case 'green':
              selectedColor = customColor; // Set to the color name if valid
              break;
          default:
              selectedColor = customColor; // Assume customColor is a valid color name
              break;
      }
  } else {
      selectedColor = $('#color option:selected').text().toLowerCase(); // Get the color name from the color input
  }

    $.ajax({
        url: formRequestajax.ajax_url,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                alert(response.data); 
                $('#form-details')[0].reset();
            } else {
                alert(response.data || response.error); 
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log('Error Details:', {
                status: jqXHR.status,
                statusText: jqXHR.statusText,
                responseText: jqXHR.responseText,
                errorThrown: errorThrown,
                textStatus: textStatus
            });
            alert('An error occurred. Please try again.');
        },
        complete: function() {
            // Hide spinner 
            $('#spinner').hide();
            $('#form-details').removeClass('form-blur');
          }
    });
});

    
    // Handle found report form submission
    $('#found_report_form').on('submit', function(e) {
        e.preventDefault();


     // Show loading spinner
     $('#spinner').show();
     $('#found_report_form').addClass('form-blur');
    
        var formData = new FormData(this); 
        formData.append('action', 'handle_found_report'); 
        formData.append('found_report_nonce', $('#found_report_nonce').val()); 
    
        $.ajax({
            type: 'POST',
            processData: false,
            contentType: false,
            url: formRequestajax.ajax_url,
            data: formData,
            success: function(response) {
                if (response.success) {
                    alert(response.data); 
                    $('#found_report_form')[0].reset();
                } else {
                    alert(response.data || response.error); 
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log('Error Details:', {
                    status: jqXHR.status,
                    statusText: jqXHR.statusText,
                    responseText: jqXHR.responseText,
                    errorThrown: errorThrown,
                    textStatus: textStatus
                });
                alert('An error occurred. Please try again.');
            },
            complete: function() {
                // Hide spinner 
                $('#spinner').hide();
                $('#found_report_form').removeClass('form-blur');
              }
        });
   }); 
   
   // Handle device registered form submission
   $('#device-registration-form').submit(function(event) {
    event.preventDefault();

    var track = {
        device: 999,
        delay: 10000,
        timer: null,
        init: function() {
            track.update();
            track.timer = setInterval(track.update, track.delay);
        },
        update: function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        document.getElementById('latitude').value = position.coords.latitude;
                        document.getElementById('longitude').value = position.coords.longitude;
                        submitForm(position.coords.latitude, position.coords.longitude);
                    },
                    function(error) {
                        alert('Geolocation error: ' + error.message);
                    }
                );
            } else {
                alert('Geolocation is not supported by this browser.');
            }
        }
    };

    function submitForm(latitude, longitude) {
        var formData = new FormData($('#device-registration-form')[0]);
        formData.append('latitude', latitude);
        formData.append('longitude', longitude);
        formData.append('action', 'handle_device_info_form_submission');
        formData.append('device_registration_nonce', $('#device_registration_nonce').val()); 

        $.ajax({
            url: formRequestajax.ajax_url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    alert(response.data); 
                    $('#device-registration-form')[0].reset();
                } else {
                    alert(response.data || response.error); 
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log('Error Details:', {
                    status: jqXHR.status,
                    statusText: jqXHR.statusText,
                    responseText: jqXHR.responseText,
                    errorThrown: errorThrown,
                    textStatus: textStatus
                });
                alert('An error occurred. Please try again.');
            }
        });
    }

    track.init();
  });

 // Handle lost report form submission
$('#lost_report_form').on('submit', function(e) {
    e.preventDefault();

   // Show loading spinner
   $('#spinner').show();
   $('#lost_report_form').addClass('form-blur');

    var formData = new FormData(this);

    formData.append('action', 'handle_lost_report');
    formData.append('lost_report_nonce', $('#lost_report_nonce').val()); 

    $.ajax({
        type: 'POST',
        processData: false,
        contentType: false,
        url: formRequestajax.ajax_url,
        data: formData,
        success: function(response) {
            if (response.success) {
                alert(response.data); 
                $('#lost_report_form')[0].reset();
            } else {
                alert(response.data || response.error); 
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log('Error Details:', {
                status: jqXHR.status,
                statusText: jqXHR.statusText,
                responseText: jqXHR.responseText,
                errorThrown: errorThrown,
                textStatus: textStatus
            });
            alert('An error occurred. Please try again.');
        },
        complete: function() {
            // Hide spinner 
            $('#spinner').hide();
            $('#lost_report_form').removeClass('form-blur');
          }
    });
});


  // Handle device search form submission
  $('#device-search-form').submit(function(event) {
    event.preventDefault();

    var formData = {
        'action': 'handle_device_search',
        'device_id': $('#device-id').val()
    };

    $.ajax({
        url: formRequestajax.ajax_url, // WordPress AJAX handler
        type: 'POST',
        data: formData,
        success: function(response) {
            var result = JSON.parse(response);
            if (result.status === 'success') {
                var data = result.data;
                var output = '<p>IMEI: ' + data.imei + '</p>';
                output += '<p>Serial Number: ' + data.serial_number + '</p>';
                output += '<p>Latitude: ' + data.latitude + '</p>';
                output += '<p>Longitude: ' + data.longitude + '</p>';
                output += '<p>Last Tracked Time: ' + data.track_time + '</p>';
                $('#search-result').html(output);

                // Initialize the map
                var map;
                var latLng = {lat: parseFloat(data.latitude), lng: parseFloat(data.longitude)};
                map = new google.maps.Map(document.getElementById('map'), {
                    center: latLng,
                    zoom: 15
                });

                // Add a marker
                var marker = new google.maps.Marker({
                    position: latLng,
                    map: map,
                    title: 'Last Known Location'
                });
            } else {
                $('#search-result').html('<p>' + result.message + '</p>');
            }
        },
        error: function() {
            $('#search-result').html('<p>An error occurred while processing your request.</p>');
        }
    });
});

});
