jQuery.fn.extend({
    /**
     * Prototype clearInput
     *
     * Shows an element able to clear an input content on click.
     *
     * @author Sosthèn Gaillard <mateus.gaillard@gmail.com>
     * @param {string} [inputId=data-input OR closest input] - input id
     */
    clearInput: function(inputId) {
        var input = null;
        if (typeof inputId !== 'undefined') {
            input = $('#'+inputId);
        }
        function getInput(input, clearInputElem) {
            if (typeof input === 'undefined' || input == null) {
                if (typeof $(clearInputElem).data('input') !== 'undefined' && $('#'+$(clearInputElem).data('input')) !== '')
                    input = $('#'+$(clearInputElem).data('input'));
                else if (typeof $(clearInputElem).closest('input') !== 'undefined')
                    input = $(clearInputElem).closest('input');
                else
                    input = false;
            }

            return input;
        }
        this.each(function() {
            var clearInputElem = $(this);

            input = getInput(input, clearInputElem);

            if (input) {
                input.keyup(function () {
                    if ($(this).val() != "")
                        clearInputElem.css('visibility', 'visible');
                    else
                        clearInputElem.css('visibility', 'hidden');

                });
            }
        });
        this.click(function () {
            input = getInput(input, $(this));

            if (input) {
                input.val('');
                input.trigger('change');
                $(this).css('visibility', 'hidden');
            }
        });
    }
});

// Modification des options par défaut du plugin "pickadate"
$.extend($.fn.pickadate.defaults, {

    // The title label to use for the month nav buttons
    labelMonthNext: 'Mois suivant',
    labelMonthPrev: 'Mois précédent',

    // The title label to use for the dropdown selectors
    labelMonthSelect: 'Sélectionner un mois',
    labelYearSelect: 'Sélectionner une année',

    // Months and weekdays
    monthsFull: ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'],
    monthsShort: ['Janv.', 'Févr.', 'Mars', 'Avr.', 'Mai', 'Juin', 'Juill.', 'Août', 'Sept.', 'Oct.', 'Nov.', 'Déc.'],
    weekdaysFull: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
    weekdaysShort: ['Dim.', 'Lun.', 'Mar.', 'Mer.', 'Jeu.', 'Ven.', 'Sam.'],

    // Today and clear
    today: '',
    clear: 'Effacer',
    close: 'Fermer',

    // Picker close behavior
    closeOnSelect: true,
    closeOnClear: true,

    // The format to show on the `input` element
    format: 'd mmmm yyyy',
    formatSubmit: 'yyyy-mm-dd',

    // Other
    max: true,
    selectYears: 25
});