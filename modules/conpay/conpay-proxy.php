<?php

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/bx_root.php");
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$api_key 			= (string) 	COption::GetOptionString('conpay', 'API_KEY', '');
$merchant_id 	= (int) 		COption::GetOptionString('conpay', 'MERCHANT_ID', '');

// Подключаем скрипт с классом ConpayProxyModel, выполняющим бизнес-логику
require_once './ConpayProxyModel.php';
try
{
	// Создаем объект класса ConpayProxyModel
	$proxy = new ConpayProxyModel;
	// Устанавливаем свой идентификатор продавца
	$proxy->setMerchantId($merchant_id);
	// Устанавливаем свой API-ключ
	$proxy->setApiKey($api_key);
	// Устанавливаем кодировку, используемую на сайте (по-умолчанию 'UTF-8')
	$proxy->setCharset('UTF-8');
	// Выполняем запрос, выводя его результат
	echo $proxy->sendRequest();
}
catch (Exception $e) {
	echo json_encode(array('error'=>$e->getMessage()));
}

?>