<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/bx_root.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

	$response_pass = COption::GetOptionString('conpay', 'RESPONSE_PASS', '');
	$merchant_id = COption::GetOptionString('conpay', 'MERCHANT_ID', '');
	$total = 0;

	if (isset($_POST['goods']))
	{
		foreach ((array)$_POST['goods'] as $item)
		{
			$total += (float)($item['price'] * $item['quantity']);
		}
	}

	$parts = array($response_pass, is_numeric($_POST['delivery']) ? $total + $_POST['delivery'] : $total, $merchant_id);

	if (isset($_POST['custom']))
	{
		foreach ($_POST['custom'] as $v)
		{
			$parts[] = $v;
		}
	}

	$checksum = md5(implode('!', $parts));

	if ($_POST['checksum'] != $checksum || $_SERVER['HTTP_REFERER'] != 'https://www.conpay.ru' || $_SERVER['HTTP_USER_AGENT'] != 'Conpay') {
		die('Access denied.');
	}

	$customer = (array)$_POST['customer'];
	$custom = (array)$_POST['custom'];

	if ($custom['order_id']) {
		die('Order already exists.');
	}

	CModule::IncludeModule("sale");

	$user_id = $custom['user_id'] ? $custom['user_id'] : (int)$USER->GetID();

	if (!$user_id)
	{
		$user = new CUser;

		$login = preg_replace('/@.*/s', '', $customer['Email']);
		$res = $user->GetByLogin($login);

		if ($res = $res->Fetch())
		{
			$user_id = $res['ID'];
		}
		else
		{
			$pass = conpay_get_random_password();
			$arFields = array(
				'LOGIN' => $login,
				'NAME' => $customer['UserName'],
				'LAST_NAME' => $customer['LastName'],
				'EMAIL' => $customer['Email'],
				'PASSWORD' => $pass,
				'CONFIRM_PASSWORD' => $pass,
				'PERSONAL_PHONE' => $customer['ContactPhone'].(isset($customer['HomePhone']) ? ', '.$customer['HomePhone'] : ''),
			);

			$user_id = $user->Add($arFields);

			// TODO: use GetMessage for texts
			$user->SendUserInfo($user_id, SITE_ID, "Приветствуем Вас как нового пользователя нашего интернет-магазина!", true);
		}
	}

	$conpay_id = COption::GetOptionString('conpay', 'ps_id');

	$arFields = array(
		"LID" => LANG,
		"PERSON_TYPE_ID" => 1,
		"PAYED" => "N",
		"CANCELED" => "N",
		"STATUS_ID" => "N",
		"PRICE" => $total,
		"CURRENCY" => "RUB",
		"USER_ID" => $user_id,
		"PAY_SYSTEM_ID" => $conpay_id,
	);

	// add Guest ID
	if (CModule::IncludeModule("statistic"))
	{
		$arFields["STAT_GID"] = CStatistic::GetEventParam();
	}

	$order_id = IntVal(CSaleOrder::Add($arFields));

	foreach ((array)$_POST['goods'] as $i => $item)
	{
		$arFields = array(
			"ORDER_ID" => $order_id,
			"PRODUCT_ID" => $item['id'],
			"PRICE" => $item['price'],
			"CURRENCY" => "RUB",
			"QUANTITY" => IntVal($item['quantity']),
			"LID" => LANG,
			"NAME" => $item['name'],
		);

		CSaleBasket::Add($arFields);
	}
}

function conpay_get_random_password()
{
	$pass = array();
	$alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
	$alphaLength = strlen($alphabet) - 1;

	for ($i = 0; $i < 8; $i++)
	{
		$n = rand(0, $alphaLength);
		$pass[] = $alphabet[$n];
	}

	return implode($pass);
}
