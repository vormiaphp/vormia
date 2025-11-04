import { safeReinitializeSelect2 } from "../plugins/select2";

export const setupLivewireHooks = () => {
    window.Livewire.hook("message.processed", safeReinitializeSelect2);

    window.Livewire.on("reinitialize-select2", safeReinitializeSelect2);
    window.Livewire.on("reinitialize-app-select2", safeReinitializeSelect2);

    window.Livewire.on("clear-select2-selection", () => {
        $(".select2").val(null).trigger("change");
    });

    window.Livewire.on("clear-this-selection", (className) => {
        $("." + className)
            .val(null)
            .trigger("change");
    });

    window.Livewire.on("parent-inheritance-options", (payload) => {
        const data = Array.isArray(payload) ? payload[0] : payload;
        const { options = [], selected = null } = data;

        const $select = $("#parent_inheritance_select");
        if ($select.length) {
            $select.empty();

            // Add default "-- No Parent --" option
            $select.append(new Option("-- No Parent --", "0", false, false));

            options.forEach(({ id, name }) => {
                // Handle selected value - check if this option is selected
                const isSelected =
                    selected !== null && selected !== 0 && selected === id;
                $select.append(new Option(name, id, isSelected, isSelected));
            });

            // Set the selected value - use 0 if selected is null or 0
            const valueToSet =
                selected === null || selected === 0 ? 0 : selected;
            $select.val(valueToSet).trigger("change");
        }
    });

    // Handle hierarchical location selects for houses
    window.Livewire.on("update-house-location-options", (payload) => {
        const data = Array.isArray(payload) ? payload[0] : payload;
        const { type, options = [], selected = null } = data;

        let selectId = "";
        switch (type) {
            case "city":
                selectId = "#house_city_select";
                break;
            case "area":
                selectId = "#house_area_select";
                break;
            case "zone":
                selectId = "#house_zone_select";
                break;
        }

        if (!selectId) {
            return;
        }

        const $select = $(selectId);
        if ($select.length) {
            // Destroy select2 if already initialized
            if ($select.hasClass("select2-hidden-accessible")) {
                $select.select2("destroy");
            }

            // Clear and rebuild options
            $select.empty();
            $select.append(new Option(`-- Select ${type.charAt(0).toUpperCase() + type.slice(1)} --`, "0", false, false));

            options.forEach(({ id, name }) => {
                const isSelected = selected !== null && selected !== 0 && selected === id;
                $select.append(new Option(name, id, isSelected, isSelected));
            });

            // Reinitialize select2
            $select.select2({
                width: "100%",
                placeholder: `Select ${type}`,
                allowClear: true,
            });

            // Set the selected value
            const valueToSet = selected === null || selected === 0 ? 0 : selected;
            $select.val(valueToSet).trigger("change");

            // Handle change event for Livewire
            $select.off("change").on("change", function () {
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
        }
    });
};
