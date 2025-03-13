
// Free
const input_excess = (num_excess) => {

    let ck_excess = `
        <div class="col-md-4 col-sm-12 rq_excess">
            <div class="form-group">
                <label for="" class="sks-required">
                    Title
                </label>
                <input type="text" class="form-control" placeholder="" name="op_excess_key[]" value="">
            </div>
        </div>
        <div class="col-md-6 col-sm-12 rq_excess">
            <div class="form-group">
                <label for="" class="">
                    Value
                </label>
                <input type="text" class="form-control" placeholder="" name="op_excess_value[]"
                    value="">
            </div>
        </div>
        <div class="col-md-1 col-sm-12 ck_excess">
            <button type="button" class="btn btn-danger btn-xs mt-4"  onclick="thisClose(this)">
                <i class="bx bx-x align-middle"></i>
            </button>
        </div>
    `;

    // Free or Paid
    return ck_excess;
}

// Add Question & Answer
const question_answer_excess = (num_excess) => {
    let qa_excess = `
        <div class="row mb-1 qn_an_box_excess" num="${num_excess}">
            ${input_excess(num_excess)}
        </div>
    `;

    // Question & Answer
    return qa_excess;
}

//  Remove Question & Answer
const remove_question_answer_excess = () => {
    // Find all elements with div.qn_an_box class and remove the last one
    let qn_an_box_excess = document.querySelectorAll(".qn_an_box_excess");
    qn_an_box_excess[qn_an_box_excess.length - 1].remove();
}

// create function addQnAnBox
const addQnAnBoxExcess = () => {
    // Select all elements with div.qn_an_box class, count the count of elements and add 1
    let qn_an_box_excess = document.querySelectorAll(".qn_an_box_excess");
    // ? get all attributes with num - get the biggest number
    let all_num = [];
    qn_an_box_excess.forEach((item) => {
        all_num.push(item.getAttribute('num'));
    });

    // ? get the biggest number
    let num_excess_large = Math.max(...all_num);
    let num_excess = num_excess_large + 1;

    // ? check if number exist
    let check = document.querySelector(`.qn_an_box_excess[num="${num_excess}"]`);
    if(check){
        num_excess = num_excess + 1;
    }

    // Add Question & Answer
    let qa_excess = question_answer_excess(num_excess);

    //Add new qa inside qen_ans_area
    let qen_ans_area_excess = document.querySelector(".qen_ans_area_excess");
    qen_ans_area_excess.insertAdjacentHTML('beforeend',qa_excess);
}

// create function removeQnAnBox
const removeQnAnBoxExcess = () => {
    // Find the total elements with div.qn_an_box class, if they are Excess than 1 remove the element
    let qn_an_box_excess = document.querySelectorAll(".qn_an_box_excess");
    if (qn_an_box_excess.length > 1) {
        // Call function remove_question_answer
        remove_question_answer_excess();
    }
}
