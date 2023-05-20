<?
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


//== Отключить вывод ошибок =======================================
//	ini_set("display_errors","0");
//	ini_set("display_startup_errors","0");

$strJavaScriptPickPoint  = '<script type="text/javascript" src="http://www.pickpoint.ru/select/postamat.js"></script>';

$strJavaScript  = <<<EOT
<script language=javascript>

function EdostPickPoint(rz) {
	document.getElementById("edost_select_pickpoint").innerHTML='<br>Выбран постамат: '+rz['name']+', '+rz['id']+', '+rz['address'];
}

function edost_deliveryclick() {
	var arr = new Array();
	arr = document.getElementsByName("edost_delivery");

	for (var i = 0; i < arr.length; i++) {
		var obj = document.getElementsByName("edost_delivery").item(i);

		if (obj.checked == true) {
			//obj.value;
			var rez = obj.value.split('|');
			var s='';
			if ( ( rez[4] != -1 )) s='<br> Стоимость доставки при наложенном платеже: '+rez[4]+' руб., доплата при получении: '+rez[5]+' руб.';
			document.getElementById("edost_select_delivery").innerHTML='Выбран тариф: '+rez[0]+' ('+rez[1]+'), код тарифа: '+obj.id+', кол-во дней: '+rez[2]+', цена: '+rez[3]+' руб.'+s;
			document.getElementById("edost_select_pickpoint").innerHTML='';
			break;
		}
	}
}

</script>
EOT;

require("edost_class.php"); //подключение класса edost


//== Расчет доставки ==============================================
	$edost_calc = new edost_class (); //создание класса edost

	//$edost_calc -> SetSiteWin(); //вывод результата в кодировке win
	$r = $edost_calc -> edost_calc_post(); //вызов расчета доставки по параметрам пришедшим в POST запросе

	//echo "<br><pre>".print_r($r, true)."</pre>";

	$st = '';
	$flPickPoint=false;

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

	//echo "<br><pre>".print_r($r['warning'], true)."</pre>";

	$warning_name = array(
		1 => 'Почтового отделения с указанным индексом НЕ существует!',
		2 => 'В вашем регионе НЕТ почтового отделения с указанным индексом!',
		);


	if ( isset($r['warning']) && count($r['warning'])>0 ) {
		foreach($r['warning'] as $n)
			$st .= "<b>Предупреждение:</b> {$warning_name[$n]}<br><br>";
	}

	if ( $r['qty_company']==0 ) {
		switch($r['stat']) {
			// коды ошибок из главного запроса на сервер edost
			case 2:		$st = "Доступ к расчету заблокирован"; break;
			case 3:		$st = "Не верные данные магазина (пароль или идентификатор)"; break;
			case 4:		$st = "Не верные входные параметры"; break;
			case 5:		$st = "Не верный город или страна"; break;
			case 6:		$st = "Внутренняя ошибка сервера расчетов"; break;
			case 7:		$st = "Не заданы компании доставки в настройках магазина"; break;
			case 8:		$st = "Сервер расчета не отвечает"; break;
			case 9:		$st = "Превышен лимит расчетов за день"; break;
			case 11:	$st = "Не указан вес"; break;
			case 12:	$st = "Не заданы данные магазина (пароль или идентификатор)"; break;
			case 14:	$st = "Настройки сервера не позволяют отправить запрос на расчет"; break;
			case 15:	$st = "Не верный город отправки"; break;
			case 16:	$st = "Ваш тарифный план не поддерживает возможность изменения города отправки"; break;
			// коды ошибок из класса edost_class
			case 10:	$st = "Не верный формат XML"; break;
			default:	$st = "В данный город автоматический расчет доставки не осуществляется";
		};
	}
	else {

/*
		$st =
// таблица
	'<table align="center" width="700" cellpadding="0" cellspacing="0" border="1" bordercolor="#D0D0D0">
		<tr height="15">
			<td width="30%" align="center">Служба доставки</td>
			<td width="30%" align="center">Тип отправления</td>
			<td width="15%" align="center">Срок доставки</td>
			<td align="center">Стоимость</td>
		</tr>';

		for ($i=1; $i<=$r['qty_company']; $i++) {
			if ($r['name'.$i]=='') $q='&nbsp;'; else $q=$r['name'.$i];
				$st = $st.
		'<tr height="15">'.
			'<td align="center">'.$r['company'.$i].'</td>'.
			'<td width="20%" align="center">'.$q.'</td>'.
			'<td align="center">'.$r['day'.$i].'</td>'.
			'<td align="center">'.$r['price'.$i].'</td>'.
// стоимость доставки и стоимость денежного перевода при наложенном платеже
//			'<td align="center">'.$r['price'.$i].' ('.$r['pricecash'.$i].') - ('.$r['transfer'.$i].')</td>'.
		'</tr>';
			}

		$st = $st.
	'</table>';

		$st = $st.'<br><br><br>';
*/

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
		for ($i=1; $i<=$r['qty_company']; $i++) {
			if ($r['name'.$i]=='') $q=''; else $q=' ('.$r['name'.$i].')';

			if ( isset($r['office'.$i]) && isset($r['office'.$i][0]['name']) ) {
				// убираем повторы офисов
				if  ( !isset($ar_office[ "'".$r['company'.$i]."'" ]) ) {

					$ar_office[ "'".$r['company'.$i]."'" ] = 1;

					$office .= '<table align="center" width="700" cellpadding="0" cellspacing="0" border="0" bordercolor="#D0D0D0">
					<tr><td style="padding-top: 10px;"><b>'.$r['company'.$i].'</b>:<td></tr>';
					//echo "<br><pre>".print_r($r['office'.$i], true)."</pre>";
					for ($h = 0; $h < count($r['office'.$i]); $h++) {
						//echo '<br>'.$i.' = '.$r['office'.$i][$h]['name'];

						if ( isset($r['office'.$i][$h]['name']) ) {

							$office .= '<tr><td style="padding-top: 10px;">Пункт выдачи '.'<b>'.$r['office'.$i][$h]['name'].'</b>';

							if (isset($r['office'.$i][$h]['id']))
								$office .= ' (<a href="http://www.edost.ru/office.php?c='.$r['office'.$i][$h]['id'].'" target="_blank" style="cursor: pointer; text-decoration: none;" >показать на карте</a>)';

                		    $office .= '<br>';

							if (isset($r['office'.$i][$h]['code'])) $office .= 'код: '.$r['office'.$i][$h]['code'].', ';
							if (isset($r['office'.$i][$h]['address'])) $office .= 'адрес: '.$r['office'.$i][$h]['address'].', <br>';
							if (isset($r['office'.$i][$h]['tel'])) $office .= 'телефон: '.$r['office'.$i][$h]['tel'].', ';
							if (isset($r['office'.$i][$h]['schedule'])) $office .= 'офис: '.$r['office'.$i][$h]['schedule'].' ';
							$office .= '<td></tr>';
						}

					}
					$office .= '</table>';

				}

			}

				if ($r['id'.$i] == 29) {
					$flPickPoint=true;
					$refPickPoint = '<br><a style="font-family: Arial; font-size: 10pt; color: rgb(222, 0, 0); text-decoration: none;" href="#" id="EdostPickPointRef1" onclick="PickPoint.open(EdostPickPoint, {city:\''.$r['pickpointmap'.$i].'\', ids:null});">Выбрать постамат или пункт выдачи</a>';
				}
				else {
					$refPickPoint = '';
				}

				$for_label = $r['id'.$i].'-'.$r['strah'.$i];
				$st .=
		'<tr height="40"><td>
			<table align="center" width="700" cellpadding="0" cellspacing="0" border="0" bordercolor="#D0D0D0"><tr>'.

				'<td height="40" width="25" align="center"> <input type="radio" id="'.$for_label.'" name="edost_delivery" value="'.$r['company'.$i].'|'.$r['name'.$i].'|'.$r['day'.$i].'|'.$r['price'.$i].'|'.$r['pricecash'.$i].'|'.$r['transfer'.$i].'" onclick="edost_deliveryclick();"> </td>'.
				'<td width="70"><label for="'.$for_label.'"><img src="delivery_img/'.$r['id'.$i].'.gif" width="60" height="32" border="0"></label> </td>'.
				'<td width="35%">'.$r['company'.$i].$refPickPoint.'</td>'.
				'<td width="20%" align="center">'.$r['name'.$i].'</td>'.
				'<td width="15%" align="center">'.$r['day'.$i].'</td>'.
				'<td align="center"><p class="c2"><b>'.$r['price'.$i].' руб.</b></p></td>'.

			'</tr></table>
		</td></tr>';
		}


		$st .=
	'</table>';

		$st .= $office;

		$st .=
	'<table align="center" width="700" cellpadding="0" cellspacing="0" border="0" bordercolor="#D0D0D0">
		<tr height="15"><td>
			<br><span id="edost_select_delivery"></span>
			<br><span id="edost_select_pickpoint"></span>
		</td></tr>
	</table>';

	}

	if (!$flPickPoint) $strJavaScriptPickPoint = ''; //если нет тарифа PickPoint, то не подгружать JavaScript

	//$st = $edost_calc -> decode($strJavaScriptPickPoint.$strJavaScript.$st); // перекодируем результат в кодировку магазина
	$st = $strJavaScriptPickPoint.$strJavaScript.$st;

	echo $st;

?>