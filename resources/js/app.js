import './bootstrap';
import Swal from 'sweetalert2';
window.Swal = Swal;

// Configuración Global de Animación Mikael Ainalem para SweetAlert2 en React / JS
const defaultSwalShow = {
    popup: 'animate-modal-expand',
    backdrop: 'animate-modal-backdrop'
};
const defaultSwalHide = {
    popup: 'animate-modal-contract',
    backdrop: 'animate-modal-backdrop-out'
};

const rawSwalFire = Swal.fire.bind(Swal);
Swal.fire = function(...args) {
    if (args.length === 1 && typeof args[0] === 'object' && args[0] !== null) {
        const opts = Object.assign({}, args[0]);
        if (!opts.showClass) opts.showClass = defaultSwalShow;
        if (!opts.hideClass) opts.hideClass = defaultSwalHide;
        return rawSwalFire(opts);
    } else if (args.length > 0 && typeof args[0] === 'string') {
        return rawSwalFire({
            title: args[0],
            html: args[1] || '',
            icon: args[2] || undefined,
            showClass: defaultSwalShow,
            hideClass: defaultSwalHide
        });
    }
    return rawSwalFire(...args);
};

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

