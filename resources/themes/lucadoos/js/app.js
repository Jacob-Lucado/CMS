import "./bootstrap";
import { initFlowbite, initPopovers } from "flowbite";

import "swiper/css";
import "swiper/css/pagination";

import { Livewire, Alpine } from "livewire";

import AtomSliders from "../../atom/js/components/AtomSliders.js";
import LucadoOS from "./lucadoos.js";

import "../../../js/components/HomeManager.js";

AtomSliders.init();
LucadoOS.init();

window.Alpine = Alpine;

const livewireStartedKey = Symbol.for("atomcms.livewire.started");
const startLivewire = () => {
    if (window[livewireStartedKey]) {
        return;
    }

    window[livewireStartedKey] = true;

    try {
        Livewire.start();
    } catch (error) {
        delete window[livewireStartedKey];
        throw error;
    }
};

// LucadoOS keeps every page in its own window instead of swapping pages with Turbolinks, so the
// "turbolinks:load" hooks the shared components listen for fire once, when the page is ready.
const ready = () => {
    startLivewire();
    initFlowbite();
    document.dispatchEvent(new Event("turbolinks:load"));
};

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", ready, { once: true });
} else {
    ready();
}

document.addEventListener("reactions:loaded", () => initPopovers());
