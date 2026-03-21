import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";

/**
 * Initialize Flatpickr on inputs marked with .flatpickr or data-flatpickr.
 * Skips elements that already have an instance (e.g. after a previous init).
 */
export function initFlatpickr() {
    const nodes = document.querySelectorAll(
        ".flatpickr, input[data-flatpickr], [data-flatpickr-init]",
    );

    nodes.forEach((el) => {
        if (el._flatpickr) {
            return;
        }
        const mode = el.dataset.flatpickrMode || el.dataset.mode;
        const options = {
            dateFormat: el.dataset.dateFormat || "Y-m-d",
            ...(mode === "time"
                ? { enableTime: true, noCalendar: true }
                : mode === "datetime"
                  ? { enableTime: true, dateFormat: el.dataset.dateFormat || "Y-m-d H:i" }
                  : {}),
        };
        flatpickr(el, options);
    });
}
