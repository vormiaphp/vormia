
// Free
const input_benefit = (num_benefit) => {

    let ck_benefit = `
        <div class="col-md-4 col-sm-12 rq_benefit">
            <div class="form-group">
                <label for="" class="sks-required">
                    Title
                </label>
                <input type="text" class="form-control" placeholder="" name="op_benefit_key[]" value="">
            </div>
        </div>
        <div class="col-md-6 col-sm-12 rq_benefit">
            <div class="form-group">
                <label for="" class="">
                    Value
                </label>
                <input type="text" class="form-control" placeholder="" name="op_benefit_value[]"
                    value="">
            </div>
        </div>
        <div class="col-md-1 col-sm-12 ck_benefit">
            <button type="button" class="btn btn-danger btn-xs mt-4"  onclick="thisClose(this)">
                <i class="bx bx-x align-middle"></i>
            </button>
        </div>
    `;

    // Free or Paid
    return ck_benefit;
}

// Add Question & Answer
const question_answer_benefit = (num_benefit) => {
    let qa_benefit = `
        <div class="row mb-1 qn_an_box_benefit" num="${num_benefit}">
            ${input_benefit(num_benefit)}
        </div>
    `;

    // Question & Answer
    return qa_benefit;
}

//  Remove Question & Answer
const remove_question_answer_benefit = () => {
    // Find all elements with div.qn_an_box class and remove the last one
    let qn_an_box_benefit = document.querySelectorAll(".qn_an_box_benefit");
    qn_an_box_benefit[qn_an_box_benefit.length - 1].remove();
}

// create function addQnAnBox
const addQnAnBoxBenefit = () => {
    // Select all elements with div.qn_an_box class, count the count of elements and add 1
    let qn_an_box_benefit = document.querySelectorAll(".qn_an_box_benefit");
    // ? get all attributes with num - get the biggest number
    let all_num = [];
    qn_an_box_benefit.forEach((item) => {
        all_num.push(item.getAttribute('num'));
    });

    // ? get the biggest number
    let num_benefit_large = Math.max(...all_num);
    let num_benefit = num_benefit_large + 1;

    // ? check if number exist
    let check = document.querySelector(`.qn_an_box_benefit[num="${num_benefit}"]`);
    if(check){
        num_benefit = num_benefit + 1;
    }

    // Add Question & Answer
    let qa_benefit = question_answer_benefit(num_benefit);

    //Add new qa inside qen_ans_area
    let qen_ans_area_benefit = document.querySelector(".qen_ans_area_benefit");
    qen_ans_area_benefit.insertAdjacentHTML('beforeend',qa_benefit);
}

// create function removeQnAnBox
const removeQnAnBoxBenefit = () => {
    // Find the total elements with div.qn_an_box class, if they are Benefit than 1 remove the element
    let qn_an_box_benefit = document.querySelectorAll(".qn_an_box_benefit");
    if (qn_an_box_benefit.length > 1) {
        // Call function remove_question_answer
        remove_question_answer_benefit();
    }
}
