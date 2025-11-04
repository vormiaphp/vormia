import "select2/dist/css/select2.min.css";

const loadFromCDN = () => {
    return new Promise((resolve) => {
        if (typeof $.fn.select2 !== "undefined") {
            resolve(true);
            return;
        }

        const script = document.createElement("script");
        script.src =
            "https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js";
        script.onload = () => resolve(true);
        script.onerror = () => resolve(false);
        document.head.appendChild(script);
    });
};

const loadFromImport = async () => {
    try {
        await import("select2/dist/js/select2.full.js");
        return typeof $.fn.select2 !== "undefined";
    } catch {
        return false;
    }
};

export const loadSelect2 = async () => {
    if (await loadFromImport()) return true;
    return await loadFromCDN();
};

export const initSelect2 = () => {
    if (typeof $.fn.select2 === "undefined") return false;

    $(".select2")
        .select2({
            width: "100%",
            placeholder: "Select an option",
            allowClear: true,
        })
        .on("change", function () {
            const val = $(this).val();
            const model = $(this).attr("wire:model");

            if (model && window.Livewire) {
                const component = window.Livewire.find(
                    $(this).closest("[wire\\:id]").attr("wire:id")
                );
                if (component) {
                    component.set(model, val);
                }
            }
        });
    return true;
};

export const destroySelect2Instances = () => {
    $(".select2").each(function () {
        if ($(this).hasClass("select2-hidden-accessible")) {
            $(this).select2("destroy");
        }
    });
};

export const safeReinitializeSelect2 = () => {
    requestAnimationFrame(() => {
        destroySelect2Instances();
        initSelect2();
    });
};
