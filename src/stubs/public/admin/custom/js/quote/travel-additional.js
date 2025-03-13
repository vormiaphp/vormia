/**
 *
 * Add Question & Answer Box
 */
// ? Get all options from #
let month_options = [];
let sample_month = document.querySelector('#copymonth');
if(sample_month){
    // ? get options
    month_options = Array.from(sample_month.options);
}

let year_options = [];
let sample_year = document.querySelector('#copyyear');
if(sample_month){
    // ? get options
    year_options = Array.from(sample_year.options);
}

// Question
const question = (num) => {
    let qn = `
        <div class="col-md-3 col-sm-12 rq">
            <input type="number" min="1" max="31" class="form-control form-control-sm"
                name="opdOb[]" value="" placeholder="DD" required>
        </div>

        <div class="col-md-5 col-sm-12 rq">
            <select class="form-select  form-select-sm" name="opmOb[]" required>
                ${month_options.map(option => {
                    return `
                        <option value="${option.value}">${option.text}</option>
                    `;
                })}
            </select>
        </div>

        <div class="col-md-4 col-sm-12 rq">
            <select class="form-select form-select-sm" name="opyOb[]" required>
                ${year_options.map(option => {
                    return `
                        <option value="${option.value}">${option.text}</option>
                    `;
                })}
            </select>
        </div>
    `;

    // Question
    return qn;
}

// Answer
const answer = (num) => {
    let an = `
        <div class="col-md-2 col-sm-12 qn">
            <label for="" class="sks-required">Travel As</label>
            <select class="form-select form-select-sm" name="optAs[]">
                <option value="0"> Non Student</option>
                <option value="1"> Student</option>
            </select>
        </div>

        <div class="col-md-4 col-sm-12 qn">
            <div class="form-group">
                <label for="" class="sks-required">Full Name</label>
                <input type="text" class="form-control form-control-sm" name="opfN[]" value="" required>
            </div>
        </div>
    `;

    // Answer
    return an;
}

// Free
const free = (num) => {
    let ck = `
        <div class="col-md-1 col-sm-12 ck">
            <button type="button" class="btn btn-danger btn-sm mt-4" onclick="thisClose(this)">
                <i class="fa fa-times align-middle"></i>
            </button>
        </div>
    `;

    // Free or Paid
    return ck;
}

// Add Question & Answer
const question_answer = (num) => {
    let qa = `
        <div class="row mb-1 qn_an_box" num="${num}">
            ${answer(num)}
            <div class="col-md-5 col-sm-12">
                <div class="row">
                    <label for="" class="sks-required">Enter Date of Birth</label>
                    ${question(num)}
                </div>
            </div>
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
