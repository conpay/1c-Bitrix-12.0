<?php
IncludeModuleLangFile(__FILE__);
global $APPLICATION;

if (!CModule::IncludeModule("catalog"))
{
	$APPLICATION->ThrowException(GetMessage("NO_CATALOG_MODULE"));
	return false;
}

CModule::AddAutoloadClasses('conpay', array("CConpay" => "general/conpay.php",));
