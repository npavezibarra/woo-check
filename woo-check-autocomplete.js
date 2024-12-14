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

    // Autocomplete functionality for comuna fields
    $('#billing_comuna, #shipping_comuna').autocomplete({
        source: Object.keys(comunaToRegionMap).map(capitalizeWords),
        minLength: 1,
        select: function (event, ui) {
            const selectedComuna = normalizeString(ui.item.value);
            const associatedRegion = comunaToRegionMap[selectedComuna];

            if (associatedRegion) {
                const regionSelect = $(this).attr('id') === 'billing_comuna' ? '#billing_state' : '#shipping_state';

                if (regionSelect) {
                    const regionValue = $(`${regionSelect} option`).filter(function () {
                        return normalizeString($(this).text()).includes(normalizeString(associatedRegion));
                    }).val();

                    if (regionValue) {
                        // Set the corresponding region and make it readonly
                        $(regionSelect).val(regionValue).prop('readonly', true).trigger('change');
                        $('body').trigger('update_checkout');
                    } else {
                        console.error("No se encontró un valor para la región:", associatedRegion);
                    }
                }
            } else {
                alert("Esta comuna no es válida. Por favor, selecciona una comuna de Chile.");
                $(this).val('');
            }
        }
    });

    // Remove unnecessary elements added by autocomplete
    $('.ui-helper-hidden-accessible').remove();
});
