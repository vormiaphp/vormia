/**
 *
 * Add Question & Answer Box
 */
// ? Get all options from #
let sample_typeof = [];
let select_typeof = document.querySelector('#copytypeof');
if(select_typeof){
    // ? get options
    sample_typeof = Array.from(select_typeof.options);
}

const typeof_opmotor = (num_opmotor) => {
    let qn_opmotor = `
        <div class="col-md-3 col-sm-12 qn_opmotor">
            <div class="form-group">
                <label for="" class="sks-required">Type Of</label>
                <select class="form-control" name="op_benefit[]">
                    ${sample_typeof.map(option => {
                        return `
                            <option value="${option.value}">${option.text}</option>
                        `;
                    })}
                </select>
            </div>
        </div>
    `;

    // Question
    return qn_opmotor;
}

const inclusive_opmotor = (num_opmotor) => {
    let an_opmotor = `

        <div class="col-md-3 col-sm-12 an_opmotor">
            <div class="form-group">
                <label for="" class="sks-required">
                    Optional/Compulsory/Inclusive ?
                </label>
                <select class="form-control" name="op_inclusive[]">
                    <option value="0">Optional (customer can pick)</option>
                    <option value="2">Compulsory (must be added)</option>
                    <option value="1">Inclusive (applied in basic premium)</option>
                </select>
            </div>
        </div>
    `;

    // Answer
    return an_opmotor;
}

// Free
const input_opmotor = (num_opmotor) => {

    let ck_opmotor = `
        <div class="col-md-2 col-sm-12 rq_opmotor">
            <div class="form-group">
                <label for="" class="">
                    Rate
                </label>
                <input type="text" class="form-control" name="op_rate[]" value="">
            </div>
        </div>
        <div class="col-md-2 col-sm-12 rq_opmotor">
            <div class="form-group">
                <label for="" class="">
                    Minimum (Premium)
                </label>
                <input type="text" class="form-control number-format-added" name="op_minimum[]" value="">
            </div>
        </div>
    `;

    // Free or Paid
    return ck_opmotor;
}

// Add Question & Answer
const question_answer_opmotor = (num_opmotor) => {
    let qa_opmotor = `
        <div class="row mb-1 qn_an_box_opmotor" num="${num_opmotor}">
            ${typeof_opmotor(num_opmotor)}
            ${inclusive_opmotor(num_opmotor)}
            ${input_opmotor(num_opmotor)}

            <div class="col-md-1 col-sm-12 ck_opmotor">
                <button type="button" class="btn btn-danger btn-xs mt-4"  onclick="thisClose(this)">
                    <i class="bx bx-x align-middle"></i>
                </button>
            </div>
        </div>
    `;

    	// Start observing the document with the configured parameters
	observerMotor.observe(document, {
		childList: true,
		subtree: true
	});

    // Question & Answer
    return qa_opmotor;
}

//  Remove Question & Answer
const remove_question_answer_opmotor = () => {
    // Find all elements with div.qn_an_box class and remove the last one
    let qn_an_box_opmotor = document.querySelectorAll(".qn_an_box_opmotor");
    qn_an_box_opmotor[qn_an_box_opmotor.length - 1].remove();
}

// create function addQnAnBox
const addQnAnBoxOpMotor = () => {
    // Select all elements with div.qn_an_box class, count the count of elements and add 1
    let qn_an_box_opmotor = document.querySelectorAll(".qn_an_box_opmotor");
    // ? get all attributes with num - get the biggest number
    let all_num = [];
    qn_an_box_opmotor.forEach((item) => {
        all_num.push(item.getAttribute('num'));
    });

    // ? get the biggest number
    let num_opmotor_large = Math.max(...all_num);
    let num_opmotor = num_opmotor_large + 1;

    // ? check if number exist
    let check = document.querySelector(`.qn_an_box_opmotor[num="${num_opmotor}"]`);
    if(check){
        num_opmotor = num_opmotor + 1;
    }

    // Add Question & Answer
    let qa_opmotor = question_answer_opmotor(num_opmotor);

    //Add new qa inside qen_ans_area
    let qen_ans_area_opmotor = document.querySelector(".qen_ans_area_opmotor");
    qen_ans_area_opmotor.insertAdjacentHTML('beforeend',qa_opmotor);
}

// create function removeQnAnBox
const removeQnAnBoxOpMotor = () => {
    // Find the total elements with div.qn_an_box class, if they are OpMotor than 1 remove the element
    let qn_an_box_opmotor = document.querySelectorAll(".qn_an_box_opmotor");
    if (qn_an_box_opmotor.length > 1) {
        // Call function remove_question_answer
        remove_question_answer_opmotor();
    }
}

// Create a new MutationObserver instance
let observerMotor = new MutationObserver((mutations) => {
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

