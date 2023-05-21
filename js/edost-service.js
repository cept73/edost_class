// noinspection SpellCheckingInspection

class EdostService
{
    constructor(cities, regions, citiesToRegions) {
        this.cities  = cities;
        this.regions = regions;
        this.citiesToRegions = citiesToRegions;

        $("#edost_to_city").autocomplete({
            'delay'     : 3,
            'minLength' : 1,
            'autoFill'  : true,
            'source'    : (request, response) => {
                const results = $.ui.autocomplete.filter(this.cities, request.term);
                response(results.slice(0, 15));
            },
            'select'    : (event, ui) => {
                this.detectRegion(ui.item?.value);
            }
        });
    }

    detectRegion = (toCity) => {
        if (toCity === '') {
            return
        }

        let cityIndex = this.cities.indexOf(toCity);
        let regionName = null;
        if (cityIndex >= 0) {
            let regionId = this.citiesToRegions[cityIndex];
            regionName = this.regions[regionId];
        }

        this.setRegion(regionName);

        $("#edost_weight").focus();
    }

    setRegion = (regionName = null) => {
        if (!regionName) {
            regionName = '-';
        }

        $("#edost_to_city").attr('title', regionName);
    }

    setCalculateResult = (result = '') => {
        $("#calculationResult").html(result);
    }

    setErrorsMessage = (errorsMessage = '') => {
        $("#errorsMessage").html(errorsMessage);
    }

    calculate = () => {
        this.setErrorsMessage();
        this.setCalculateResult();

        let city            = $("#edost_to_city").val();
        let weight          = $("#edost_weight").val();
        let strah           = $("#edost_strah").val();
        let edost_length    = $("#edost_length").val();
        let edost_width     = $("#edost_width").val();
        let edost_height    = $("#edost_height").val();
        let edost_zip       = $("#edost_zip").val();

        let errors = [];
        if (city === '') {
            errors.push('Не заполнен город доставки!');
        }
        if (weight === '') {
            errors.push('Не заполнен вес!');
        } else {
            if (weight.indexOf(',') >= 0) {
                weight = weight.replace(',', '.');
            }
            if (isNaN(weight)) {
                errors.push('Вес должен быть цифра!');
            }
        }
        if (errors.length) {
            this.setErrorsMessage(errors.join('<br />'));
            return false;
        }

        this.setCalculateResult('<b>Идет расчет стоимости доставки. Ждите...<\/b>');
        $.post("edost.php", {
            edost_to_city   : city,
            edost_weight    : weight,
            edost_strah     : strah,
            edost_kod       : 1,
            edost_rus       : 1,
            edost_length    : edost_length,
            edost_width     : edost_width,
            edost_height    : edost_height,
            edost_zip       : edost_zip
        }, (responseHtml) => {
            this.setCalculateResult(responseHtml);
        });
    }
}
