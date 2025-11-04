import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";

window.flatpickr = flatpickr;

export const initFlatpickr = () => {
    try {
        flatpickr("#dob", {
            dateFormat: "d-m-Y",
            allowInput: true,
            disableMobile: true,
        });
        return true;
    } catch {
        return false;
    }
};
