import { safeReinitializeSelect2 } from "../plugins/select2";

/**
 * Re-run Select2 binding after Livewire updates the DOM (new selects, replaced fragments).
 * Works whether Livewire is ready before or after this module runs.
 */
export function setupLivewireHooks() {
    const register = () => {
        if (!window.Livewire || typeof window.Livewire.hook !== "function") {
            return;
        }
        window.Livewire.hook("morph.updated", () => {
            safeReinitializeSelect2();
        });
    };

    if (window.Livewire && typeof window.Livewire.hook === "function") {
        register();
    } else {
        document.addEventListener("livewire:init", register);
    }
}
