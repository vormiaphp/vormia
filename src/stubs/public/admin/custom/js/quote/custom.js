// ? Get base url elbase#base_url
//base URL as found in config-base URL
const base = document.querySelector('#base_url');
// Base URL
const base_url = base.getAttribute('url'); //'http://127.0.0.1:8000';

// create a function to be called by onchange
function getModels(make, selectClass = true) {
    // Create a data object with the key "id" and the selected value
    var postData = { find: make };
    var url = base_url+"/api/v1/get/make-model";

    // Send a POST request using jQuery AJAX
    $.ajax({
        type: "POST",
        url: url,
        contentType: "application/json",
        data: JSON.stringify(postData),
        success: function (data) {
            // Handle the successful response here
            console.log("POST request successful");
            // console.log("Response: " + data);
            if(data.status === 200){
                // console.log(data.results);
                carModels = data.response.results;
                var selectElement = $("#model");

                // Initialize Select2
                if(selectClass){
                    selectElement.select2();
                }

                // Clear previous options
                selectElement.empty();

                // Iterate through the JSON object and create options
                $.each(carModels, function(key, value) {
                    var option = new Option(value["value"], value["id"]);
                    selectElement.append(option);
                });
            }
        },error: function (error) {
            // Handle errors here
            console.error("Error in POST request: " + error);
        }
    });
}

/* -----------------MATCH OPTIONS------------------------ */
const findMatches = (search, options) => {
	return options.filter(option => {
		let parent = option.getAttribute('parent');

        if(parent == 0) return option;

		const regex = new RegExp(search, "gi");
		return parent.match(regex);
	});
}

const filterOptions = (parentElement, childElement, options) => {
	options.forEach(option => {
		option.remove();
		option.selected = false;
	});
	// Parent Element attribute meta-url
	let parent_url = parentElement.querySelector('option:checked').getAttribute('meta-url');
	const matchArray = findMatches(parent_url, options);
	if (matchArray.length > 0) {
		options[0].text = "---- Select ----";
		options[0].value = '';
	} else {
		options[0].text = "--- ANY ---";
		options[0].value = 0;
	}
	matchArray.unshift(options[0]);
	childElement.append(...matchArray);
}

/**
 * Todo: VERIFY CORRECT ID NUMBER
 */
// ? Limit ID number
let passport_or_id = document.querySelector('#pId');
let type_of_id = document.querySelector('#iT');

// ? pass status
let pass_status = false;

if(passport_or_id && type_of_id){
    // event listener
    passport_or_id.addEventListener('input', function() {
        console.log(this.value);
        verifyId(this);

    });
}

const resetIdPassportInputs = () => {
    passport_or_id = document.querySelector('#pId');
    type_of_id = document.querySelector('#iT');

    // ? pass status
    let pass_status = false;

    if(passport_or_id && type_of_id){
        // event listener
        passport_or_id.addEventListener('input', function() {
            console.log(this.value);
            verifyId(this);

        });
    }
}

const validIdNo = () => verifyId(passport_or_id);

const verifyId = (input) =>{
    // ? default error
    let error_sms = '';
    let this_pid = input.value.trim();
    // if is passport
    let this_it = type_of_id.value;
    let this_it_text = ''; //type_of_id.text;

    if(this_it == 'passport'){
        // ? must be string and max of 10
        pass_status = true;
    }else {
        // ? integer
        if(!/^\d+$/.test(this_pid)){
            error_sms = `Please enter valid ${this_it_text} input`;
        }else if(this_pid.length < 7 || this_pid.length > 8){
            error_sms = `Minimum ${this_it_text} length is 7 to 8 characters`;
        }else if(this_pid.length == 0){
            error_sms = `Enter valid ${this_it_text} number`;
        }else {
            pass_status = true;
        }
    }
    // ? show error
    showError(input,pass_status,error_sms);
}

const showError = (input,status,sms) => {
    // ? get parent element
    let box = input.parentElement;

    // ? check if span.error exist
    if(!box.querySelector('span.error')){
        // ? append at the end
        let span = document.createElement('span');
        span.classList.add('error');
        span.innerHTML = sms;
        box.appendChild(span);
    }else{
        box.querySelector('span.error').innerHTML = sms;
    }

}
