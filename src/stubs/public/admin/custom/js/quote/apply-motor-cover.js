// ? Variables for vehicle use
let vehicleuseOptions = null;
let vehicleuseurl = null;

// ? Check If we are on motor cover page
const motorCoverPage = document.querySelector('#motor-results');
if(motorCoverPage){
    // ? Get base url elbase#base_url
    //base URL as found in config-base URL
    let this_server_url = document.querySelector('#base_url').getAttribute('url');

    // ? Retrive checked radio button
    let this_coverfor = document.querySelector('#coverfor-list input[type="radio"]:checked');
    // ? Get slug of checked radio button
    let this_coverfor_url = this_coverfor.getAttribute('slug');

    // ? Get covering select element
    let this_covering = document.querySelector('#youarecovering');
    let this_covering_url = this_covering.querySelector('option:checked').getAttribute('slug');

    // ? Get select element
    let this_vehicleuse = document.querySelector('#vehicleuse');
    vehicleuseurl = this_vehicleuse.querySelector('option:checked').getAttribute('slug');

    // ? Get all options
    vehicleuseOptions = Array.from(this_vehicleuse.options);

    // ? Remove all options
    vehicleuseOptions.forEach(option => {
        let coverfor = option.getAttribute('coverfor');
        let covering = option.getAttribute('covering');

        // ? covering can have vehicle motor-bike or vehicle (many or single), so we need to check if it contains this_covering_url
        if (coverfor == this_coverfor_url && covering.includes(this_covering_url) && this_covering_url == null || this_covering_url == undefined) {
            return;
        } else if (option.value == "0") {
            return;
        } else {
            option.remove();
            option.selected = false;
        }
    });
}

// ? COVER FOR RADIO BUTTONS
const coverFor_radioButtons = (btn) => {
    // ? get all labels inside #coverfor-list
    let labels = document.querySelectorAll('#coverfor-list label');
    // ? remove active from all labels
    labels.forEach(label => {
        label.classList.remove('active');
    });

    // ? Get Parent label
    let parentLabel = btn.parentElement;

    // ? add active to current parent
    parentLabel.classList.add('active');

    // ? Get slug of current parent
    let slug = btn.getAttribute('slug');

    // ? Vehicle use
    vehicle_use_options(slug);
}

// ? VEHICLE OWNER RADIO BUTTONS
const vOwned_radioButtons = (btn) => {
    resetIdPassportInputs();
    // ? get all labels inside #coverfor-list
    let div_sel = document.querySelector('#'+btn.value);

    // ? remove active from all labels
    if(btn.value == 'business'){
        let personal_elements = document.querySelector('#personal');
        personal_elements.classList.add('d-none');
        div_sel.classList.remove('d-none');

        // loop all elements in div and rename name attribute
        var elements1 = personal_elements.querySelectorAll('input, select');
        elements1.forEach(function(element) {
            // Rename the name attribute to "un-name"
            // element.setAttribute('name', 'un-name');
            // element.setAttribute('un-name', element.name);
            // element.removeAttribute('name');
            element.setAttribute('disabled', '');
        });


        // loop all elements in div and rename name attribute
        var elements2 = div_sel.querySelectorAll('input, select');
        elements2.forEach(function(element) {
            // Rename the name attribute to "un-name"
            // let attr_name = element.getAttribute('un-name');
            // element.setAttribute('name', attr_name);
            element.removeAttribute('disabled');
        });
    }else{
        let business_elements = document.querySelector('#business');
        business_elements.classList.add('d-none');

        // loop all elements in div and rename name attribute
        var elements1 = business_elements.querySelectorAll('input, select');
        elements1.forEach(function(element) {
            // Remove the name attribute
            // element.removeAttribute('name');
            element.setAttribute('disabled', '');
        });

        // loop all elements in div and rename name attribute
        var elements2 = div_sel.querySelectorAll('input, select');
        elements2.forEach(function(element) {
            // Set the name attribute
            // let attr_name = element.getAttribute('un-name');
            // element.setAttribute('name', attr_name);
            element.removeAttribute('disabled');
        });


        div_sel.classList.remove('d-none');
    }
    // // ? remove active from all labels
    // labels.forEach(label => {
    //     label.classList.remove('active');
    // });

    // // ? Get Parent label
    // let parentLabel = btn.parentElement;

    // // ? add active to current parent
    // parentLabel.classList.add('active');

    // // ? Get slug of current parent
    // let slug = btn.getAttribute('slug');

    // // ? Vehicle use
    // vehicle_use_options(slug);
}
// ? VEHICLE OWNER RADIO BUTTONS
const coverDelivery_radioButtons = (btn) => {
    // ? get all labels inside #coverfor-list
    let div_sel = document.querySelector('#'+btn.value);
    console.log(div_sel);
    // // ? remove active from all labels
    if(btn.value == 'yes_delivery'){
        let personal_elements = document.querySelector('#no_delivery');
        personal_elements.classList.add('d-none');
        div_sel.classList.remove('d-none');

        // loop all elements in div and rename name attribute
        var elements1 = personal_elements.querySelectorAll('input, select');
        elements1.forEach(function(element) {
            element.setAttribute('disabled', '');
        });


        // loop all elements in div and rename name attribute
        var elements2 = div_sel.querySelectorAll('input, select');
        elements2.forEach(function(element) {
            // Rename the name attribute to "un-name"
            // let attr_name = element.getAttribute('un-name');
            // element.setAttribute('name', attr_name);
            element.removeAttribute('disabled');
        });
    }else{
        let business_elements = document.querySelector('#yes_delivery');
        business_elements.classList.add('d-none');

        // loop all elements in div and rename name attribute
        var elements1 = business_elements.querySelectorAll('input, select');
        elements1.forEach(function(element) {
            // Remove the name attribute
            // element.removeAttribute('name');
            element.setAttribute('disabled', '');
        });

        // loop all elements in div and rename name attribute
        var elements2 = div_sel.querySelectorAll('input, select');
        elements2.forEach(function(element) {
            // Set the name attribute
            // let attr_name = element.getAttribute('un-name');
            // element.setAttribute('name', attr_name);
            element.removeAttribute('disabled');
        });


        div_sel.classList.remove('d-none');
    }
}

// ? Get - youreCovering_select
const youreCovering_select = (select_this) => {
    // get slug of selected from select
	let slug = select_this.querySelector('option:checked').getAttribute('slug');
    // ? Vehicle use
    vehicle_use_options(null,slug);
}

// ? Vehicle use
const vehicle_use_options = (cover_slug = null, covering_slug = null) => {

    // ? if cover_slug is null or undefined, get slug
    if (cover_slug == null || cover_slug == undefined) {
        // ? Retrive checked radio button
        let this_coverfor = document.querySelector('#coverfor-list input[type="radio"]:checked');
        // ? Get slug of checked radio button
        cover_slug = this_coverfor.getAttribute('slug');
    }

    // ? if covering_slug is null or undefined, get slug
    if (covering_slug == null || covering_slug == undefined) {
        // ? Get covering select element
        let this_covering = document.querySelector('#youarecovering');
        covering_slug = this_covering.querySelector('option:checked').getAttribute('slug');
    }

    let this_passenger_no = document.getElementById('psv');
    let this_body_type = document.getElementById('commercial');
    this_passenger_no.classList.add('d-none');
    this_body_type.classList.add('d-none');
    var npassField = this_passenger_no.querySelector('input[name="nPas"]');
    var btypeField = this_body_type.querySelector('select[name="vBdy"]');
    npassField.required = false;
    btypeField.required = false;

    if(cover_slug == "psv"){
        this_passenger_no.classList.remove('d-none');
        var inputField = this_passenger_no.querySelector('input[name="nPas"]');
        inputField.required = true;
    }else if(cover_slug == "commercial" && covering_slug == "vehicle"){
        this_body_type.classList.remove('d-none');
        var inputField = this_body_type.querySelector('select[name="vBdy"]');
        inputField.required = true;
    }else{
    }

    // Optionns
	let options = vehicleuseOptions;

	vehicleuseOptions.forEach(option => {
		option.remove();
		option.selected = false;
	});

    // console.log(parent_url,options);
	const matchArray = VehicleMatches(cover_slug, covering_slug, vehicleuseOptions);

    // ? Get select element
    let this_vehicleuse = document.querySelector('#vehicleuse');

	// Create option elements
    matchArray.forEach(option => {
        let coverfor = option.getAttribute('coverfor');
        let covering = option.getAttribute('covering');
        // ? covering can have vehicle motor-bike or vehicle (many or single), so we need to check if it contains this_covering_url
        // if (coverfor == cover_slug && covering.includes(covering_slug)) {
            this_vehicleuse.appendChild(option);
        // }
    });
}

/* -----------------MATCH OPTIONS------------------------ */
const VehicleMatches = (cover,covering, options) => {
	return options.filter(option => {
		let this_cover = option.getAttribute('coverfor');
		let this_covering = option.getAttribute('covering');
		let this_value = option.getAttribute('value');

        if(this_value == 0) return option;

		const rege_cover = new RegExp(cover, "gi");
		const regex_covering = new RegExp(covering, "gi");

        // ? covering can have vehicle motor-bike or vehicle (many or single), so we need to check if it contains this_covering_url
        return this_cover.match(rege_cover) && this_covering.match(regex_covering);
	});
}


