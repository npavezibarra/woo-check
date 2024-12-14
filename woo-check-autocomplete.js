jQuery(document).ready(function ($) {

    // Function to capitalize each word
    function capitalizeWords(str) {
        return str.replace(/\b\w+/g, function (word) {
            return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
        });
    }

    // Normalize strings by removing accents, diacritics, and special characters
    function normalizeString(str) {
        return str
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, "") // Removes diacritics
            .replace(/[’']/g, "") // Removes apostrophes
            .replace(/[^a-zA-Z0-9\s]/g, "") // Removes special characters
            .toLowerCase();
    }

    // Create a map to find regions based on comunas
    const comunaToRegionMap = {};
    comunasChile.forEach(entry => {
        entry.comunas.forEach(comuna => {
            comunaToRegionMap[normalizeString(comuna)] = entry.region;
        });
    });

    // Sync Región with Comuna
    function syncRegionWithComuna(comunaInput, regionSelect) {
        const selectedComuna = normalizeString($(comunaInput).val());
        const associatedRegion = comunaToRegionMap[selectedComuna];

        if (associatedRegion) {
            const regionValue = $(`${regionSelect} option`).filter(function () {
                return normalizeString($(this).text()).includes(normalizeString(associatedRegion));
            }).val();

            if (regionValue) {
                // Temporarily enable the field, update its value, then disable it
                $(regionSelect)
                    .prop('disabled', false)
                    .val(regionValue)
                    .trigger('change')
                    .prop('disabled', true);

                // Trigger WooCommerce update
                $('body').trigger('update_checkout');
            } else {
                console.error("No se encontró un valor para la región:", associatedRegion);
            }
        } else {
            alert("Esta comuna no es válida. Por favor, selecciona una comuna de Chile.");
            $(comunaInput).val('');
        }
    }

    // Apply autocomplete for the Comuna field
    $('#billing_comuna, #shipping_comuna').autocomplete({
        source: Object.keys(comunaToRegionMap).map(capitalizeWords),
        minLength: 1,
        select: function (event, ui) {
            const comunaInput = $(this);
            const regionSelect = comunaInput.attr('id') === 'billing_comuna' ? '#billing_state' : '#shipping_state';
            comunaInput.val(ui.item.value);
            syncRegionWithComuna(comunaInput, regionSelect);
        }
    });

    // Set Región field to disabled on load
    function disableRegionFields() {
        $('#billing_state, #shipping_state').prop('disabled', true);
    }

    // Reapply disabled state after WooCommerce updates fields
    $(document.body).on('updated_checkout', function () {
        disableRegionFields();
    });

    // Initialize disabled state for Región fields
    disableRegionFields();

    // Revalidate Región when user changes Comuna
    $('#billing_comuna, #shipping_comuna').on('blur', function () {
        const comunaInput = $(this);
        const regionSelect = comunaInput.attr('id') === 'billing_comuna' ? '#billing_state' : '#shipping_state';
        syncRegionWithComuna(comunaInput, regionSelect);
    });
});