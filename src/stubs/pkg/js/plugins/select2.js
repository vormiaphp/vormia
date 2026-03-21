import $ from "jquery";

/**
 * Dynamically loads Select2 and its CSS so the main bundle stays smaller until needed.
 * Call once on DOMContentLoaded before initSelect2().
 */
export async function loadSelect2() {
    await import("select2");
    await import("select2/dist/css/select2.min.css");
    return typeof $.fn.select2 === "function";
}

/**
 * Default selector for selects that should use Select2 (class or data attribute).
 */
const SELECTOR = "select.select2, select[data-select2]";

function bindOne($el) {
    if ($el.hasClass("select2-hidden-accessible")) {
        return;
    }
    const placeholder = $el.data("placeholder");
    const allowClear = $el.data("allow-clear") !== undefined;

    $el.select2({
        theme: "default",
        width: $el.data("width") || "100%",
        ...(placeholder ? { placeholder: String(placeholder) } : {}),
        ...(allowClear ? { allowClear: true } : {}),
    });
}

/**
 * Initialize Select2 on all matching selects that are not already enhanced.
 */
export function initSelect2() {
    if (typeof $.fn.select2 !== "function") {
        return;
    }
    $(SELECTOR).each(function () {
        bindOne($(this));
    });
}

/**
 * After Livewire replaces DOM nodes, tear down Select2 on removed elements and init new ones.
 */
export function safeReinitializeSelect2() {
    if (typeof $.fn.select2 !== "function") {
        return;
    }
    $(SELECTOR).each(function () {
        const $el = $(this);
        if ($el.hasClass("select2-hidden-accessible")) {
            try {
                $el.select2("destroy");
            } catch {
                // Element may already be detached; ignore.
            }
        }
    });
    initSelect2();
}
