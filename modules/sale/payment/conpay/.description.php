<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?php

include(GetLangFileName(dirname(__FILE__)."/", "/conpay.php"));

$psTitle = "Conpay";
$psDescription = GetMessage("SALE_CONPAY_DESCRIPTION");

$arPSCorrespondence = array(
	"BUTTON_TEXT" => array(
		"NAME" => GetMessage("SALE_CONPAY_BUTTON_TEXT"),
		"DESCR" => GetMessage("SALE_CONPAY_BUTTON_TEXT_DESC"),
		"VALUE" => "",
		"TYPE" => ""
	),
);

?>
