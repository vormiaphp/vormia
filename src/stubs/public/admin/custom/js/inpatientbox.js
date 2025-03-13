/**
 *
 * Add Question & Answer Box
 */
// ? Get all options from #
let sample_options = [];
let sample_premiumformat = document.querySelector('#copypremiumformat');
if(sample_premiumformat){
    // ? get options
    sample_options = Array.from(sample_premiumformat.options);
}

// Question
const question = (num) => {
    let qn = `
        <div class="col-md-5 col-sm-12 qn">
            <div class="form-group">
                <label for="" class="sks-required">
                    Format
                </label>
                <select class="form-control" name="inpatient_format[]">
                    ${sample_options.map(option => {
                        return `
                            <option value="${option.value}">${option.text}</option>
                        `;
                    })}
                </select>
            </div>
        </div>
    `;

    // Question
    return qn;
}

// Answer
const answer = (num) => {
    let an = `
        <div class="col-md-5 col-sm-12 an">
            <div class="form-group">
                <label for="" class="sks-required">Payable (Premium)</label>
                <input type="numeric" class="form-control number-format" name="inpatient_premium[]">
            </div>
        </div>

    `;

    // Answer
    return an;
}

// Free
const free = (num) => {
    let ck = `
        <div class="col-md-2 col-sm-12 ck">
            <button type="button" class="btn btn-danger btn-xs mt-4" onclick="thisClose(this)">
                <i class="bx bx-x align-middle"></i>
            </button>
        </div>
    `;

    // Free or Paid
    return ck;
}

// Add Question & Answer
const question_answer = (num) => {
    let qa = `
        <div class="row mb-1 shaow-style-3 py-2 qn_an_box" num="${num}">
            ${question(num)}
            ${answer(num)}
            ${free(num)}
        </div>
    `;

    // Question & Answer
    return qa;
}

//  Remove Question & Answer
const remove_question_answer = () => {
    // Find all elements with div.qn_an_box class and remove the last one
    let qn_an_box = document.querySelectorAll(".qn_an_box");
    qn_an_box[qn_an_box.length - 1].remove();
}

// create function addQnAnBox
const addQnAnBox = () => {
    // Select all elements with div.qn_an_box class, count the count of elements and add 1
    let qn_an_box = document.querySelectorAll(".qn_an_box");
    let num = qn_an_box.length + 1;

    // Add Question & Answer
    let qa = question_answer(num);

    //Add new qa inside qen_ans_area
    let qen_ans_area = document.querySelector(".qen_ans_area");
    qen_ans_area.insertAdjacentHTML('beforeend',qa);
}

// create function removeQnAnBox
const removeQnAnBox = () => {
    // Find the total elements with div.qn_an_box class, if they are more than 1 remove the element
    let qn_an_box = document.querySelectorAll(".qn_an_box");
    if (qn_an_box.length > 1) {
        // Call function remove_question_answer
        remove_question_answer();
    }
}

// remove specific box
const thisClose = (btn) => {
    // ? Get the parent element on clicked button
    let parent = btn.parentElement.parentElement;

    // ? Remove the parent element
    parent.remove();
}
