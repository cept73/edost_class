<?php
/** @noinspection SpellCheckingInspection */

require('EdostService.php');

?>
<html lang="ru">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Пример - Расчет по России с автозаполнением города (с jQuery)</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css"
          integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
</head>
<body>

<div class="container my-5" style="max-width: 600px">

    <h1>Расчет стоимости экспресс доставки</h1>

    <form name="calc" method="post" onSubmit="return false;">
        <div class="row mt-2">
            <div class="col-md-8 mt-3 form-floating">
                <input type="text" class="form-control" id="edost_to_city" name="edost_to_city" required="required">
                <label for="edost_to_city" class="px-4">Город:</label>
            </div>
            <div class="col-md-4 mt-3 form-floating">
                <input type="text" class="form-control" id="edost_zip" name="edost_zip" size="6" maxlength="6">
                <label for="edost_zip" class="px-4">Индекс:</label>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-6 col-md-3 mt-3 form-floating">
                <input type="text" class="form-control" id="edost_weight" name="edost_weight" size="5" maxlength="5">
                <label for="edost_weight" class="px-4">Вес (кг):</label>
            </div>
            <div class="col-sm-6 col-md-3 mt-3 form-floating">
                <input type="text" class="form-control" id="edost_length" name="edost_length" size="10" maxlength="10">
                <label for="edost_length" class="px-4">Длина (см):</label>
            </div>
            <div class="col-sm-6 col-md-3 mt-3 form-floating">
                <input type="text" class="form-control" id="edost_width" name="edost_width" size="10" maxlength="10">
                <label for="edost_width" class="px-4">Ширина (см):</label>
            </div>
            <div class="col-sm-6 col-md-3 mt-3 form-floating">
                <input type="text" class="form-control" id="edost_height" name="edost_height" size="10" maxlength="10">
                <label for="edost_height" class="px-4">Высота (см):</label>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-offset-6 col-6">
                <div id="errorsMessage" style="color:red"></div>
                <input type="submit" class="btn btn-primary" value="Расчет" onclick="EdostServiceInstance.calculate();">
            </div>
        </div>
    </form>

    <span id="calculationResult"></span>

<script type="text/javascript" src="https://code.jquery.com/jquery-3.6.0.js"></script>
<script type='text/javascript' src='https://code.jquery.com/ui/1.13.2/jquery-ui.js'></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4"
        crossorigin="anonymous"></script>
<script type="text/javascript" src="js/edost-service.js"></script>
<script>
    let EdostServiceInstance = new EdostService(
        [<?= '"' . implode('","', EdostService::CITIES_LIST) . '"' ?>],
        [<?= '"' . implode('", "', EdostService::REGIONS_LIST) . '"' ?>],
        [<?= implode(', ', EdostService::CITIES_TO_REGION_LIST) ?>]
    )
</script>

</div>

</body>
</html>