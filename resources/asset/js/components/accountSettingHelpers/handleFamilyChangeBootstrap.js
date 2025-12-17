"use strict";
import { id, showError } from "../global";
import { removeDiv, createAndAppendElement } from '../helper/general'
import { renderHtmlFamilyBootstrap } from './renderFamilyBootstrap'
import { checkEmailObj } from "../../data/checkEmailObj";
import { makeCheckEmailObj } from "../../data/checkEmailFactory";


/**
 * Adapted for Account Setting Page (Bootstrap 5)
 */

const syncCheckEmailObj = () => {
    const kids = Number(id("children")?.value) || 0;
    const siblings = Number(id("sibling")?.value) || 0;

    // IMPORTANT: mutate the same object reference
    Object.assign(checkEmailObj, makeCheckEmailObj(kids, siblings));
};



export const show = (kids_or_sib, event) => {
    try {
        const value = Number(event.target.value) || 0;

        // ✅ unique container IDs (avoid clashing with <select id="children">)
        const containerId = `${kids_or_sib}_inputs`;
        const parentId = `${kids_or_sib}_div`;

        // remove the existing dynamic container
        removeDiv(containerId);

        const helpEl = id(`${kids_or_sib}_help`);
        if (helpEl) helpEl.innerHTML = "";

        if (value === 0) {
            syncCheckEmailObj();
            return;
        }
        // create the container under wrapper
        createAndAppendElement("div", containerId, parentId);
        if (helpEl) {
            helpEl.innerHTML =
                value > 1
                    ? "Please, provide details for each below:"
                    : "Please, provide details below:";
            helpEl.classList.remove("text-danger"); // Use BS5 classes or JS logic? keeping simple
            helpEl.style.color = ""; // Reset custom color if any
        }

        const container = id(containerId);
        if (!container) return;

        for (let i = 0; i < value; i++) {
            const no = i + 1;
            const html = renderHtmlFamilyBootstrap(kids_or_sib, no);
            container.insertAdjacentHTML("beforeEnd", html);
        }

        // 🔥 after DOM changes, regenerate ID lists
        syncCheckEmailObj();

    } catch (error) {
        showError(error)
    }
}

// ON CHANGE FOR THE NUMBER OF KIDS AND SIBLING 
const onChangeKidAndSiblings = () => {

    const kidInput = id("children")
    const sibInput = id("sibling");


    if (kidInput) kidInput.addEventListener('change', (event) => show('children', event));
    if (sibInput) sibInput.addEventListener('change', (event) => show('sibling', event));

    // initialise on page load too (if selects already have values)
    syncCheckEmailObj();

}

onChangeKidAndSiblings()
