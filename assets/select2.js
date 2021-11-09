console.log('select2.js loaded');
import Choices from "choices.js";

//new Choices('.js-select2');


let select_arr = document.querySelectorAll('.js-select2')

select_arr.forEach(function (el) {
    let choices = new Choices(el)
})