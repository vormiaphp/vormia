
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


/* -------------------- FOR COUNTRY --------------------- */
let mainOptions = null;
let childwayOptions = null;
let mainurl = null;
let childwayurl = null;
// Get value Selected #this_main
let this_main = document.querySelector('#this_main');
if(this_main){
    mainurl = this_main.querySelector('option:checked').getAttribute('meta-url');

    // Get value Selected #this_child_main
    let this_child_main = document.querySelector('#this_child_main');
    let this_child_main_active = this_child_main.querySelector('option:checked');

    // Get Select [Child way] Options
    mainOptions = Array.from(this_main.options);
    childwayOptions = Array.from(this_child_main.options);

    // Get selected
    childwayOptions.forEach(option => {
        let parent = option.getAttribute('parent');
        if (parent == mainurl) {
            return;
        } else if (option.value == "0") {
            return;
        } else {
            option.remove();
            option.selected = false;
        }
    });
}

// OnChange
const thisSelectMain = section_this => {

	// Optionns
	let options = childwayOptions;

	childwayOptions.forEach(option => {
		option.remove();
		option.selected = false;
	});

	// Parent Element attribute meta-url
	let parent_url = section_this.querySelector('option:checked').getAttribute('meta-url');
	// console.log(parent_url,options);
	const matchArray = findMatches(parent_url, childwayOptions);

	// Create option elements
	matchArray.forEach(option => {
        document.getElementById("this_child_main").appendChild(option);
	});
}

/**
 * Todo: For Tonnage
 */
let usedforOptions = null;
let tonnageOptions = null;
let usedforurl = null;
let tonnageyurl = null;
// Get value Selected #productrate
let this_productrate = document.querySelector('#productrate');
if(this_productrate){
    let this_usedfor = document.querySelector('#usedfor');
    usedforurl = this_usedfor.querySelector('option:checked').getAttribute('meta-url');

    // Get value Selected #this_child_main
    let this_usedtonnage = document.querySelector('#usedtonnage');
    let this_usedtonnage_active = this_usedtonnage.querySelector('option:checked');

    // Get Select [Child way] Options
    usedforOptions = Array.from(this_usedfor.options);
    tonnageOptions = Array.from(this_usedtonnage.options);

    // Get selected
    tonnageOptions.forEach(option => {
        let parent = option.getAttribute('parent');
        if (parent == usedforurl) {
            return;
        } else if (option.value == "0") {
            return;
        } else {
            option.remove();
            option.selected = false;
        }
    });
}

// OnChange
const thisUsedFor = section_this => {

	// Optionns
	let options = tonnageOptions;

	tonnageOptions.forEach(option => {
		option.remove();
		option.selected = false;
	});

	// Parent Element attribute meta-url
	let parent_url = section_this.querySelector('option:checked').getAttribute('meta-url');

	// console.log(parent_url,options);
	const matchArray = findMatches(parent_url, tonnageOptions);

	// Create option elements
	matchArray.forEach(option => {
        document.getElementById("usedtonnage").appendChild(option);
	});
}

// ? Covering
const thisCovering = section_this => {
    usedforOptions.forEach(option => {
		option.remove();
		option.selected = false;
	});
    tonnageOptions.forEach(option => {
		option.remove();
		option.selected = false;
	});

	// Parent Element value
	let covering = section_this.querySelector('option:checked').getAttribute('value');
    // change to int
    covering = parseInt(covering);

    // loop
    usedforOptions.forEach(option => {
        let this_covered = option.getAttribute('covering');
        // ? trim space
        this_covered = this_covered.trim();
        // ? change to array
        let this_covered_array = this_covered.split(',');
        // ? change to inter
        this_covered_array = this_covered_array.map(Number);
        // ? loop
        this_covered_array.forEach((item,index) => {
            // ? check if covering is in array
            if(item != covering){
                option.remove();
            }else{
                document.getElementById("usedfor").appendChild(option);
            }
        });
    })
}

// ? Cover For
const thisCoverFor = section_this => {
	// Parent Element attribute meta-url
	let parent_url = section_this.querySelector('option:checked').getAttribute('meta-url');
	// ? Optionns
	let options = usedforOptions;
	usedforOptions.forEach(option => {
		option.remove();
		option.selected = false;
	});
    tonnageOptions.forEach(option => {
		option.remove();
		option.selected = false;
	});

    // console.log(parent_url);
	const matchArray = findMatches(parent_url, usedforOptions);

	// Create option elements
	matchArray.forEach(option => {
        document.getElementById("usedfor").appendChild(option);
	});
}

/**
 * Todo: For Medical Product
 */
let productOptions = null;
let planOptions = null;
let producturl = null;
let planurl = null;
// Get value Selected #productplan
let this_productplan = document.querySelector('#productplan');
if(this_productplan){
    let this_product = document.querySelector('#product');
    producturl = this_product.querySelector('option:checked').getAttribute('meta-url');

    // Get value Selected #this_child_main
    let this_plan = document.querySelector('#plan');
    let this_plan_ctive = this_plan.querySelector('option:checked');

    // Get Select [Child way] Options
    productOptions = Array.from(this_product.options);
    planOptions = Array.from(this_plan.options);

    // Get selected
    planOptions.forEach(option => {
        let parent = option.getAttribute('parent');
        if (parent == producturl) {
            return;
        } else if (option.value == "0") {
            return;
        } else {
            option.remove();
            option.selected = false;
        }
    });
}


// ? get product plans
const thisProduct = section_this => {
	// Optionns
	let options = planOptions;

	planOptions.forEach(option => {
		option.remove();
		option.selected = false;
	});

	// Parent Element attribute meta-url
	let parent_url = section_this.querySelector('option:checked').getAttribute('meta-url');

	// console.log(parent_url,options);
	const matchArray = findMatches(parent_url, planOptions);

	// Create option elements
	matchArray.forEach(option => {
        document.getElementById("plan").appendChild(option);
	});
}

// ? Get form by name="import_form"
let importForm = document.querySelector('form[name="import_form"]');
if(importForm){
    document.addEventListener('DOMContentLoaded', function() {
        const productTypeSelect = document.import_form.visibility;
        const companySelect = document.querySelector('[name="forcompany"]');
        const partnerSelect = document.querySelector('[name="forpartner"]');
        const productSelect = document.querySelector('[name="product"]');
        const products = document.querySelectorAll('[insurer][visibility][forcompany][forpartner]');

        function filterProducts() {
            const selectedProductType = productTypeSelect.value;
            var selectedCompany = companySelect.value;
            var selectedPartner = partnerSelect.value;

            if(selectedProductType == 1){
                // clear product and company select
                selectedPartner = null;
                partnerSelect.value = 'na';
                selectedCompany = null;
                companySelect.value = 'na';
            }else if(selectedProductType == 2){
                // clear company select
                selectedCompany = null;
                companySelect.value = 'na';
            }else if(selectedProductType == 3){
                // clear partner select
                selectedPartner = null;
                partnerSelect.value = 'na';
            }

            // clear product select
            productSelect.value = 'na';

            products.forEach(function(product) {
                const productType = product.getAttribute('visibility');
                const company = product.getAttribute('forcompany');
                const partner = product.getAttribute('forpartner');

                if ((productType === '1' && selectedProductType === '1') ||
                    (selectedPartner == partner && selectedProductType === '2') ||
                    (selectedCompany == company && selectedProductType === '3')) {
                    product.style.display = 'block';
                } else {
                    product.style.display = 'none';
                }
            });
        }
        for (var i = 0; i < productTypeSelect.length; i++) {
            productTypeSelect[i].addEventListener('change', filterProducts);
        }
        companySelect.addEventListener('change', filterProducts);
        partnerSelect.addEventListener('change', filterProducts);
    });
}
