
//handle color input
document.querySelectorAll('input[name="color_option"]').forEach(option => {
    option.addEventListener('change', function() {
        if (this.value === 'custom') {
            document.getElementById('color').disabled = true;
            const customColorInput = document.getElementById('custom_color');
            customColorInput.style.display = 'block';
            customColorInput.required = true;
        } else {
            document.getElementById('color').disabled = false;
            const customColorInput = document.getElementById('custom_color');
            customColorInput.style.display = 'none';
            customColorInput.required = false;
        }
    });
});


// Handle activation form submission
const formdetails = document.getElementById('form-details');

if(formdetails) {
formdetails.addEventListener('submit', function(e) {
    
  e.preventDefault();

  document.querySelector('#spinner').style.display = 'block';
  document.querySelector('#form-details').classList.add('form-blur');

     const formData = new FormData(formdetails);
    formData.append('action', 'handle_activated_details'); 
    formData.append('activation_nonce', document.querySelector('#activation_nonce').value); 

    const selectedColor = document.querySelector('#color').value;
    const customColor = document.querySelector('#custom_color').value.toLowerCase();

    fetch(AjaxformRequest.ajax_url, {
        method: 'post',
        body: formData
    })
    .then(response => response.json())
    .then(response => {
        document.querySelector('#spinner').style.display = 'none';
         document.querySelector('#form-details').classList.remove('form-blur');
        if(response.success) {
            alert(response.data);
            formdetails.reset();
        } else {
            alert(response.data || 'An unknown error occurred.'); 
        }
    })
    .catch(() => {
        alert('Something went wrong. Please try again.');
    });
});
}

// Handle found report form submission
const foundReport = document.getElementById('found_report_form');

if(foundReport) {

foundReport.addEventListener('submit', function(e) {
    e.preventDefault();

    document.querySelector('#spinner').style.display = 'block';
    document.querySelector('#found_report_form').classList.add('form-blur');

    const formData = new FormData(foundReport);
    formData.append('action', 'handle_found_report'); 
    formData.append('found_report_nonce', document.querySelector('#found_report_nonce').value); 

    fetch(AjaxformRequest.ajax_url, {
        method: 'post',
        body: formData
    })
    .then(response => response.json())
    .then(response => {
        document.querySelector('#spinner').style.display = 'none';
        document.querySelector('#found_report_form').classList.remove('form-blur');
        if(response.success) {
            alert(response.data);
            foundReport.reset();
        }else {
            alert(response.data || 'An unknown error occurred.'); 
        }
    })
    .catch((error) => {
        alert('Something went wrong, Please try again')
    });
});
}

//handle lost report
const lostReportForm = document.getElementById('lost_report_form');

if(lostReportForm) {
lostReportForm.addEventListener('submit', function(e) {
    e.preventDefault();

    document.querySelector('#spinner').style.display = 'block';
    document.querySelector('#lost_report_form').classList.add('form-blur');

    var formData = new FormData(lostReportForm);

    formData.append('action', 'handle_lost_report');
    formData.append('lost_report_nonce', document.querySelector('#lost_report_nonce').value); 

    fetch(AjaxformRequest.ajax_url, {
        method: 'post',
        body: formData
    })
    .then(response => response.json())
    .then(response => {
        document.querySelector('#spinner').style.display = 'none';
        document.querySelector('#lost_report_form').classList.remove('form-blur');
        if(response.success) {
            alert(response.data);
            lostReportForm.reset();
        }else {
            alert(response.data || 'An unknown error occurred.'); 
        }
    })
    .catch((error) => {
        alert('Something went wrong, Please try again');
    });
});
}

const searchForm = document.getElementById('search-form');

if(searchForm) {
    searchForm.addEventListener('click', function() {

        const formData = new FormData();
        formData.append('action', 'handle_search')
        fetch(AjaxformRequest.ajax_url, {
            method: 'post',
            body: formData
        })
        .then(response => response.json())
        .then(response => {
            if(response.success) {
                document.querySelector('#map').style.display = 'block';
            } else {
                alert('No device found');
            }
        })
        .catch((error) => {
            console.error('Error', 'error');
        })
    })
}

const touchArea = document.querySelector('#touchArea');

if(touchArea) {
    touchArea.addEventListener('lick', function() {
        fetch(formRequestajax, {
            method: 'post',
        })
        .then(response => response.text())
        .then(data => {
           console.log(data);
           document.getElementById('alarmMessage').style.display = 'block';
        })
        .catch((error) => {
            console.error('Error', error);
        });
    });
}


