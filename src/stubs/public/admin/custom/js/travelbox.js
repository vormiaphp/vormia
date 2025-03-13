/**
 *
 * Add Question & Answer Box
 */
// ? Get all options from #
let sample_currency_options = [];
let sample_currencyformat = document.querySelector('#copycurrencyformat');
if(sample_currencyformat){
    // ? get options
    sample_currency_options = Array.from(sample_currencyformat.options);
}

let sample_family_options = [];
let sample_groupformat = document.querySelector('#copygroupformat');
if(sample_groupformat){
    // ? get options
    sample_family_options = Array.from(sample_groupformat.options);
}


// MinAge
const agelist = (num) => {
    let an = `
        <div class="col-md-2 col-sm-12 min">
            <div class="form-group">
                <label for="" class=""> Min Age</label>
                <input type="number" class="form-control" min="0" name="minage[]">
            </div>
        </div>

        <div class="col-md-2 col-sm-12 min">
            <div class="form-group">
                <label for="" class=""> Max Age</label>
                <input type="number" class="form-control" min="0" name="maxage[]">
            </div>
        </div>

    `;

    // Answer
    return an;
}

// MaxAge
const maxage = (num) => {
    let an = `
        <div class="col-md-2 col-sm-12 min">
            <div class="form-group">
                <label for="" class=""> Max Age</label>
                <input type="number" class="form-control" min="0" name="maxage[]">
            </div>
        </div>

    `;

    // Answer
    return an;
}

// Premium
const premium = (num) => {
    let an = `
        <div class="col-md-3 col-sm-12 an">
            <div class="form-group">
                <label for="" class="sks-required"> Premium </label>
                <input type="text" class="form-control number-format" name="premium[]">
            </div>
        </div>

        <div class="col-md-3 col-sm-12 qn">
            <div class="form-group">
                <label for="" class="sks-required"> Currency </label>
                <select class="form-control"  name="currency[]">
                    ${sample_currency_options.map(option => {
                        return `
                            <option value="${option.value}">${option.text}</option>
                        `;
                    })}
                </select>
            </div>
        </div>
    `;

    // Answer
    return an;
}

// For group
const group = (num) => {
    let qn = `
        <div class="col-md-3 col-sm-12 qn">
            <div class="form-group">
                <label for="" class="sks-required"> For Group? </label>
                <select class="form-control"  name="group[]">
                    ${sample_family_options.map(option => {
                        return `
                            <option value="${option.value}">${option.text}</option>
                        `;
                    })}
                </select>
            </div>
        </div>

        <div class="col-lg-3 col-md-3 col-sm-12 min">
            <div class="form-group">
                <label for="" class="">
                    Min Group No
                </label>
                <input type="number" class="form-control" placeholder="" min="0" name="groupmin[]">
            </div>
        </div>

        <div class="col-lg-3 col-md-3 col-sm-12 min">
            <div class="form-group">
                <label for="" class="">
                    Max Group No
                </label>
                <input type="number" class="form-control" placeholder="" min="0" name="groupmax[]">
            </div>
        </div>

    `;

    // Question
    return qn;
}


// Close
const close_btn = (num) => {
    let ck = `
        <div class="col-md-2 col-sm-12 ck">
            <button type="button" class="btn btn-danger btn-xs mt-4" onclick="thisCloseTravel(this)">
                <i class="bx bx-x align-middle"></i>
            </button>
        </div>
    `;

    // Free or Paid
    return ck;
}

// Percent
const perce = (num) => {
    let an = `
        <div class="col-md-3 col-sm-12 per">
            <div class="form-group">
                <label for="" class=""> Percent </label>
                <input type="text" class="form-control" placeholder="0.5,200%" name="rate[]">
            </div>
        </div>
    `;

    // Answer
    return an;
}


// Add Question & Answer
const question_answer_travel = (num) => {
    let qa = `
        <div class="mb-2 shaow-style-3 py-2 qn_an_box" num="${num}">
            <div class="row px-2">
                ${agelist(num)}
                ${premium(num)}
                ${close_btn(num)}
            </div>
            <div class="row px-2">
                ${group(num)}
                ${perce(num)}
            </div>
        </div>
    `;

    // Question & Answer
    return qa;
}

//  Remove Question & Answer
const remove_question_answer_travel = () => {
    // Find all elements with div.qn_an_box class and remove the last one
    let qn_an_box = document.querySelectorAll(".qn_an_box");
    qn_an_box[qn_an_box.length - 1].remove();
}

// create function addQnAnBoxTravel
const addQnAnBoxTravel = () => {
    // Select all elements with div.qn_an_box class, count the count of elements and add 1
    let qn_an_box = document.querySelectorAll(".qn_an_box");
    let num = qn_an_box.length + 1;

    // Add Question & Answer
    let qa = question_answer_travel(num);

    //Add new qa inside qen_ans_area
    let qen_ans_area = document.querySelector(".qen_ans_area");
    qen_ans_area.insertAdjacentHTML('beforeend',qa);
}

// create function removeQnAnBoxTravel
const removeQnAnBoxTravel = () => {
    // Find the total elements with div.qn_an_box class, if they are more than 1 remove the element
    let qn_an_box = document.querySelectorAll(".qn_an_box");
    if (qn_an_box.length > 1) {
        // Call function remove_question_answer_travel
        remove_question_answer_travel();
    }
}

// remove specific box
const thisCloseTravel = (btn) => {
    // ? Get the parent element on clicked button
    let parent = btn.parentElement.parentElement.parentElement;

    console.log(parent);

    // ? Remove the parent element
    parent.remove();
}
