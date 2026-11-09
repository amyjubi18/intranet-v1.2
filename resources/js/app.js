import './bootstrap';
import './app.jsx';
import flatpickr from 'flatpickr';
 import { Spanish } from 'flatpickr/dist/l10n/es.js';
 flatpickr.localize(Spanish);



import Alpine from 'alpinejs'

window.Alpine = Alpine

import("./../../vendor/power-components/livewire-powergrid/dist/powergrid.css");
import("./../../vendor/power-components/livewire-powergrid/dist/tom-select.css");
import './../../vendor/power-components/livewire-powergrid/dist/powergrid'
import Datepicker from 'flowbite-datepicker/Datepicker';

/* Hooks = {}

Hooks.Datepicker = {
    mounted() {
        const datepickerEl = this.el;
        new Datepicker(datepickerEl, {
            // options
        });
    },
    updated() {
        this.mounted();
    }
}
 */
import TomSelect from "tom-select";
window.TomSelect = TomSelect

import './../../vendor/power-components/livewire-powergrid/dist/powergrid'

Alpine.start()

