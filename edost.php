<?php
/** @noinspection SpellCheckingInspection */

/*
== Обработчик POST запроса ========================================

По параметрам пришедшим в POST запросе производится расчет доставки.
Результат выводится в виде таблицы (построчно: название компании (название тарифа) - количество дней доставки - цена доставки в рублях).

Параметры пришедшие в POST запросе:
1. edost_to_city - город или страна, куда неоходимо отправить посылку (название на русском языке в кодировке windows-1251)
2. edost_weight - вес в кг
3. edost_strah - сумма для страховки в рублях
4. edost_length - длина посылки (можно не указывать)
5. edost_width - ширина посылки (можно не указывать)
6. edost_height - высота посылки (можно не указывать)
7. edost_zip - почтовый индекс (можно не указывать)

===================================================================
*/

//== Расчет доставки ==============================================
$EdostConfig = require('EdostCalculator.config.php');
require('EdostCalculator.php');

$EdostCalculator = new EdostCalculator($EdostConfig['id'], $EdostConfig['password'], $EdostConfig['max_code']);
$calculation = $EdostCalculator->calcByArray($_POST);

//	Результат выводится в массив r:
//	1. r['stat'] - код результата запроса
//	2. r['qty_company'] - количество тарифов
//		Записи по каждому тарифу (N):
//		3. r['id'.N] - код тарифа
//		4. r['price'.N] - цена доставки в рублях
//		5. r['day'.N] - количество дней доставки
//		6. r['starh'.N] - код страхования (1 - рассчитано со страховкой, 0 - рассчитано БЕЗ страховки)
//		7. r['company'.N] - название компании
//		8. r['name'.N] - название тарифа

//== Вывод результатов ============================================

// TODO: continue refactor
$st = '';
if ( $calculation['qty_company'] === 0 ) {
    $st = $calculation['status_message'];
} else {
    $st .=
// таблица с выбором
	'<table align="center" width="700" cellpadding="0" cellspacing="0" border="1" bordercolor="#D0D0D0">
		<tr height="15"><td>
			<table align="center" width="700" cellpadding="0" cellspacing="0" border="0" bordercolor="#D0D0D0"><tr>
				<td width="25"></td>
				<td width="70"></td>
				<td width="35%">Служба доставки</td>
				<td width="20%" align="center">Тип отправления</td>
				<td width="15%" align="center">Срок доставки</td>
				<td align="center">Стоимость</td>
			</tr></table>
		</td></tr>';

    $ar_office = [];
    $office = '';
    for ($i=1; $i<=$calculation['qty_company']; $i++) {
        if ($calculation['name'.$i]=='') $q=''; else $q=' ('.$calculation['name'.$i].')';

        if ( isset($calculation['office'.$i]) && isset($calculation['office'.$i][0]['name']) ) {
            // убираем повторы офисов
            if  ( !isset($ar_office[ "'".$calculation['company'.$i]."'" ]) ) {

                $ar_office[ "'".$calculation['company'.$i]."'" ] = 1;

                $office .= '<table align="center" width="700" cellpadding="0" cellspacing="0" border="0" bordercolor="#D0D0D0">
                <tr><td style="padding-top: 10px;"><b>'.$calculation['company'.$i].'</b>:<td></tr>';
                //echo "<br><pre>".print_r($calculation['office'.$i], true)."</pre>";
                for ($h = 0; $h < count($calculation['office'.$i]); $h++) {
                    //echo '<br>'.$i.' = '.$calculation['office'.$i][$h]['name'];

                    if ( isset($calculation['office'.$i][$h]['name']) ) {

                        $office .= '<tr><td style="padding-top: 10px;">Пункт выдачи '.'<b>'.$calculation['office'.$i][$h]['name'].'</b>';

                        if (isset($calculation['office'.$i][$h]['id']))
                            $office .= ' (<a href="http://www.edost.ru/office.php?c='.$calculation['office'.$i][$h]['id'].'" target="_blank" style="cursor: pointer; text-decoration: none;" >показать на карте</a>)';

                        $office .= '<br>';

                        if (isset($calculation['office'.$i][$h]['code'])) $office .= 'код: '.$calculation['office'.$i][$h]['code'].', ';
                        if (isset($calculation['office'.$i][$h]['address'])) $office .= 'адрес: '.$calculation['office'.$i][$h]['address'].', <br>';
                        if (isset($calculation['office'.$i][$h]['tel'])) $office .= 'телефон: '.$calculation['office'.$i][$h]['tel'].', ';
                        if (isset($calculation['office'.$i][$h]['schedule'])) $office .= 'офис: '.$calculation['office'.$i][$h]['schedule'].' ';
                        $office .= '<td></tr>';
                    }

                }
                $office .= '</table>';

            }

        }

        if ($calculation['id'.$i] == 29) {
            $flPickPoint = true;
            $refPickPoint = '<br><a style="font-family: Arial; font-size: 10pt; color: rgb(222, 0, 0); text-decoration: none;" href="#" id="EdostPickPointRef1" onclick="PickPoint.open(EdostPickPoint, {city:\''.$calculation['pickpointmap'.$i].'\', ids:null});">Выбрать постамат или пункт выдачи</a>';
        }
        else {
            $refPickPoint = '';
        }

        $for_label = $calculation['id'.$i].'-'.$calculation['strah'.$i];
        $st .=
		'<tr height="40"><td>
			<table align="center" width="700" cellpadding="0" cellspacing="0" border="0" bordercolor="#D0D0D0"><tr>'.

				'<td height="40" width="25" align="center"> <input type="radio" id="'.$for_label.'" name="edost_delivery" value="'.$calculation['company'.$i].'|'.$calculation['name'.$i].'|'.$calculation['day'.$i].'|'.$calculation['price'.$i].'|'.$calculation['pricecash'.$i].'|'.$calculation['transfer'.$i].'" onclick="edost_deliveryclick();"> </td>'.
				'<td width="70"><label for="'.$for_label.'"><img src="delivery_img/'.$calculation['id'.$i].'.gif" width="60" height="32" border="0"></label> </td>'.
				'<td width="35%">'.$calculation['company'.$i].$refPickPoint.'</td>'.
				'<td width="20%" align="center">'.$calculation['name'.$i].'</td>'.
				'<td width="15%" align="center">'.$calculation['day'.$i].'</td>'.
				'<td align="center"><p class="c2"><b>'.$calculation['price'.$i].' руб.</b></p></td>'.

			'</tr></table>
		</td></tr>';
    }


    $st .= '</table>';

    $st .= $office;

    $st .=
	'<table align="center" width="700" cellpadding="0" cellspacing="0" border="0" bordercolor="#D0D0D0">
		<tr height="15"><td>
			<br><span id="edost_select_delivery"></span>
			<br><span id="edost_select_pickpoint"></span>
		</td></tr>
	</table>';

}

if ($flPickPoint) : ?><script type="text/javascript" src="http://www.pickpoint.ru/select/postamat.js"></script><?php endif ?>

<script>

    function EdostPickPoint(rz) {
        document.getElementById("edost_select_pickpoint").innerHTML = '<br>Выбран постамат: '+rz['name']+', '+rz['id']+', '+rz['address'];
    }

    function edost_deliveryclick() {
        const arr = document.getElementsByName("edost_delivery");

        for (let i = 0; i < arr.length; i++) {
            const obj = document.getElementsByName("edost_delivery").item(i);

            if (obj.checked === true) {
                //obj.value;
                const res = obj.value.split('|');
                let s = '';
                if (res[4] !== -1) {
                    s='<br> Стоимость доставки при наложенном платеже: '+res[4]+' руб., доплата при получении: '+res[5]+' руб.';
                }
                document.getElementById("edost_select_delivery").innerHTML = 'Выбран тариф: '+res[0]+' ('+res[1]+'), код тарифа: '+obj.id
                    +', кол-во дней: '+res[2]+', цена: '+res[3]+' руб.'+s;
                document.getElementById("edost_select_pickpoint").innerHTML='';

                break;
            }
        }
    }

</script>

<?= $st ?>
