/**
 *
 * Add Question & Answer Box
 */
// ? Get all options from #
let sample_benefit = [];
let sample_copybenefit = document.querySelector('#copybenefit');
if(sample_copybenefit){
    // ? get options
    sample_benefit = Array.from(sample_copybenefit.options);
}

// ? Get all options from #
let sample_options_two = [];
let sample_premiumformat_two = document.querySelector('#copypremiumformatoptional');
if(sample_premiumformat_two){
    // ? get options
    sample_options_two = Array.from(sample_premiumformat_two.options);
}

// ? Get all options from #
let sample_options_applied = [];
let sample_copyapplied = document.querySelector('#copyapplied');
if(sample_copyapplied){
    // ? get options
    sample_options_applied = Array.from(sample_copyapplied.options);
}


// Question
const question_more = (num_more) => {
    let qn_more = `
        <div class="col-md-3 col-sm-12 qn_more">
            <div class="form-group">
                <label for="" class="sks-required">
                    Format
                </label>
                <select class="form-control" name="optional_format[]">
                    ${sample_options_two.map(option => {
                        return `
                            <option value="${option.value}">${option.text}</option>
                        `;
                    })}
                </select>
            </div>
        </div>
    `;

    // Question
    return qn_more;
}

// Answer
const answer_more = (num_more) => {
    let an_more = `
        <div class="col-md-4 col-sm-12 an_more">
            <div class="form-group">
                <label for="" class="sks-required">Premium</label>
                <input type="text" class="form-control number-format-added" name="optional_premium[]" required>
            </div>
        </div>

    `;

    // Answer
    return an_more;
}

// Free
const free_more = (num_more) => {
    // ? generate random number
    let rand = Math.floor(Math.random() * 1000);
    // ?
    let id_more = `requiredRadios${rand}_${num_more}`;

    let ck_more = `
        <div class="col-md-1 col-sm-12 ck_more">
            <button type="button" class="btn btn-danger btn-xs mt-4"  onclick="thisClose(this)">
                <i class="bx bx-x align-middle"></i>
            </button>
        </div>

        <div class="col-md-4 col-sm-12 rq_more">
            <div class="form-group">
                <label for="" class="sks-required">
                    Applied
                </label>
                <select class="form-control" name="optional_applyper[]" required>
                    ${sample_options_applied.map(option => {
                        return `
                            <option value="${option.value}">${option.text}</option>
                        `;
                    })}
                </select>
            </div>
        </div>

        <div class="col-md-3 col-sm-12 rq_more">
            <div class="form-group">
                <label for="" class="">
                    Requred
                </label>
                <select class="form-control" name="optional_required[]">
                    <option value="0">No</option>
                    <option value="1">Yes</option>
                </select>
            </div>
        </div>

        <div class="col-md-2 col-sm-12 rq_more">
            <div class="form-group">
                <label for="" class="">
                    Min Age
                </label>
                <input type="number" min="0" class="form-control" name="optional_minage[]"
                    value="">
            </div>
        </div>

        <div class="col-md-2 col-sm-12 rq_more">
            <div class="form-group">
                <label for="" class="">
                    Max Age
                </label>
                <input type="number" min="1" class="form-control" name="optional_maxage[]"
                    value="">
            </div>
        </div>
    `;

    // Free or Paid
    return ck_more;
}

// Attachment
const optional_more = (num_more) => {
    let att_more = `
        <div class="col-md-4 col-sm-12 att_more">
            <div class="form-group">
                <label for="" class="sks-required">
                    Benefit
                </label>
                <select class="form-control" name="optional_benefit[]">
                    ${sample_benefit.map(option => {
                        return `
                            <option value="${option.value}">${option.text}</option>
                        `;
                    })}
                </select>
            </div>
        </div>
    `;

    // Attachment
    return att_more;

}

// Add Question & Answer
const question_answer_more = (num_more) => {
    let qa_more = `
        <div class="row mb-1 qn_an_box_more" num="${num_more}">
            ${optional_more(num_more)}
            ${question_more(num_more)}
            ${answer_more(num_more)}
            ${free_more(num_more)}
        </div>
    `;

    	// Start observing the document with the configured parameters
	observer.observe(document, {
		childList: true,
		subtree: true
	});

    // Question & Answer
    return qa_more;
}

//  Remove Question & Answer
const remove_question_answer_more = () => {
    // Find all elements with div.qn_an_box class and remove the last one
    let qn_an_box_more = document.querySelectorAll(".qn_an_box_more");
    qn_an_box_more[qn_an_box_more.length - 1].remove();
}

// create function addQnAnBox
const addQnAnBoxMore = () => {
    // Select all elements with div.qn_an_box class, count the count of elements and add 1
    let qn_an_box_more = document.querySelectorAll(".qn_an_box_more");
    // ? get all attributes with num - get the biggest number
    let all_num = [];
    qn_an_box_more.forEach((item) => {
        all_num.push(item.getAttribute('num'));
    });

    // ? get the biggest number
    let num_more_large = Math.max(...all_num);
    let num_more = num_more_large + 1;

    // ? check if number exist
    let check = document.querySelector(`.qn_an_box_more[num="${num_more}"]`);
    if(check){
        num_more = num_more + 1;
    }

    // Add Question & Answer
    let qa_more = question_answer_more(num_more);

    //Add new qa inside qen_ans_area
    let qen_ans_area_more = document.querySelector(".qen_ans_area_more");
    qen_ans_area_more.insertAdjacentHTML('beforeend',qa_more);
}

// create function removeQnAnBox
const removeQnAnBoxMore = () => {
    // Find the total elements with div.qn_an_box class, if they are more than 1 remove the element
    let qn_an_box_more = document.querySelectorAll(".qn_an_box_more");
    if (qn_an_box_more.length > 1) {
        // Call function remove_question_answer
        remove_question_answer_more();
    }
}

	// Create a new MutationObserver instance
	let observer = new MutationObserver((mutations) => {
		mutations.forEach((mutation) => {
			// Check if new nodes are added
			if (mutation.addedNodes) {
				mutation.addedNodes.forEach((node) => {
					// Make sure it's an element and matches your selector
					if (node.nodeType === 1 && node.matches('.number-format-added')) {
						maskNumber.call(node, {
							integer: true
						});
					}
				});
			}
		});
	});


// remove specific box
// const thisClose = (btn) => {
//     // ? Get the parent element on clicked button
//     let parent = btn.parentElement.parentElement;

//     // ? Remove the parent element
//     parent.remove();
// }
