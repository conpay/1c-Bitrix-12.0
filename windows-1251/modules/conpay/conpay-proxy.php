<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/bx_root.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$api_key = (string)COption::GetOptionString('conpay', 'API_KEY', '');
$merchant_id = (int)COption::GetOptionString('conpay', 'MERCHANT_ID', '');

// ���������� ������ � ������� ConpayProxyModel, ����������� ������-������
require_once './ConpayProxyModel.php';
try
{
	// ������� ������ ������ ConpayProxyModel
	$proxy = new ConpayProxyModel;
	// ������������� ���� ������������� ��������
	$proxy->setMerchantId($merchant_id);
	// ������������� ���� API-����
	$proxy->setApiKey($api_key);
	// ������������� ���������, ������������ �� ����� (��-��������� 'UTF-8')
	$proxy->setCharset('WINDOWS-1251');
	// ��������� ������, ������ ��� ���������
	echo $proxy->sendRequest();
}
catch (Exception $e) {
	echo json_encode(array('error'=>$e->getMessage()));
}
