<?php
/** @noinspection SpellCheckingInspection */

require('EdostCalculator.php');

?>
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Пример - Расчет по России с автозаполнением города (с jQuery)</title>
	<link rel="stylesheet" type="text/css" href="js/jquery.autocomplete.css" />
</head>
<body>

<p><b>Расчет стоимости экспресс доставки</b></p>

<form name="calc" method="post" onSubmit="return false;">
	<table>
		<tr>
			<td></td>
			<td>Город:</td>
			<td>Регион:</td>
		</tr>

		<tr>
			<td>Куда:</td>
			<td><input type="text" id="edost_to_city" name="edost_to_city" size="35" maxlength="80"></td>
			<td><span id="edost_to_region">-</span></td>
		</tr>

		<tr>
			<td>Индекс:</td>
			<td><input type="text" id="edost_zip" name="edost_zip" size="6" maxlength="6"></td>
			<td><p id="errorsMessage" style="color:red"></p></td>
		</tr>

		<tr>
			<td>Вес:</td>
			<td><input type="text" id="edost_weight" name="edost_weight" size="5" maxlength="5"> кг.</td>
			<td></td>
		</tr>

		<tr>
			<td>Оценка:</td>
			<td><input type="text" id="edost_strah" name="edost_strah" size="10" maxlength="12"> руб.</td>
			<td></td>
		</tr>

		<tr>
			<td>Длина:</td>
			<td><input type="text" id="edost_length" name="edost_length" size="10" maxlength="10"> см.</td>
			<td></td>
		</tr>

		<tr>
			<td>Ширина:</td>
			<td><input type="text" id="edost_width" name="edost_width" size="10" maxlength="10"> см.</td>
			<td></td>
		</tr>

		<tr>
			<td>Высота:</td>
			<td><input type="text" id="edost_height" name="edost_height" size="10" maxlength="10"> см.</td>
			<td></td>
		</tr>

		<tr>
			<td></td>
			<td><input type="submit" name="B_Calc" value="Расчет" onclick="EdostCalculation.calculate();"></td>
		</tr>
	</table>
</form>

<span id="calculationResult"></span>

<script type="text/javascript" src="js/jquery-1.2.6.pack.js"></script>
<script type='text/javascript' src='js/jquery.ajaxQueue.js'></script>
<script type='text/javascript' src='js/jquery.autocomplete.pack.js'></script>
<script>

    let EdostCalculation = new (class {
        cities  = [<?= '"' . implode('","', EdostCalculator::CITIES_LIST) . '"' ?>];
        regions = [<?= '"' . implode('", "', EdostCalculator::REGIONS_LIST) . '"' ?>];
        citiesToRegions = [<?= implode(', ', EdostCalculator::CITIES_TO_REGION_LIST) ?>];

        detectRegion = () => {
            const toCity = $("#edost_to_city").val();
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

            $("#edost_to_region").html(regionName);
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

        constructor() {
            $(document).ready(() => {
                $("#edost_to_city").autocomplete(this.cities, {
                    delay           : 3,
                    minChars        : 1,
                    matchSubset     : 1,
                    autoFill        : true,
                    maxItemsToShow  : 10,
                    max             : 20,
                });

                $(":text, textarea")
                    .result(this.detectRegion)
                    .next().click(() => {
                        $(this).prev().search();
                    });
            });
        }
    });
</script>

</body>
</html>