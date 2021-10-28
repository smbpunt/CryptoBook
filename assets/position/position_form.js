console.log('position_form.js loaded');

import {Tab} from 'bootstrap';
import Choices from "choices.js";

const choices = new Choices('.js-select2');
/*import 'moment';
import Pikaday from 'pikaday/pikaday';*/
//var picker = new Pikaday({field: document.getElementById('js-datetimepicker')});

const triggerTabList = [].slice.call(document.querySelectorAll('#myTab a'));
triggerTabList.forEach(function (triggerEl) {
    const tabTrigger = new Tab(triggerEl)

    triggerEl.addEventListener('click', function (event) {
        console.log('ici');
        event.preventDefault()
        tabTrigger.show()
    })
})