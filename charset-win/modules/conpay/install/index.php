<?

IncludeModuleLangFile(__FILE__);

Class conpay extends CModule
{
	var $MODULE_ID = "conpay";
	var $MODULE_VERSION = "1.0.0";
	var $MODULE_VERSION_DATE = '2013-02-01 00:00:00';
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $PARTNER_NAME = "CONPAY.RU";
	var $PARTNER_URI = "http://www.conpay.ru";

	function conpay()
	{
		$arModuleVersion = array();
		
		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path . "/version.php");
		
		$this->MODULE_VERSION = $arModuleVersion['VERSION'];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = GetMessage("CONPAY_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("CONPAY_MODULE_DESCRIPTION");
	}
	
	// TODO: display error messages
	
	function DoInstall()
	{
		global $APPLICATION;
		
		if (!CModule::IncludeModule('sale')) return false;
		
		$arFields = array(
			// TODO: get actual site id
			'LID' => 's1',
			'CURRENCY' => 'RUB',
			'NAME' => GetMessage('CONPAY_PAYMENT_SYSTEM_NAME'),
			'ACTIVE' => 'Y',
			'DESCRIPTION' => GetMessage('CONPAY_PAYMENT_SYSTEM_DESCRIPTION'),
		);
		
		$ps_id = CSalePaySystem::Add($arFields);
		
		if (!$ps_id) return false;
		
		$arPSCorrespondence = array(
			"BUTTON_TEXT" => array(
				"NAME" => GetMessage("SALE_CONPAY_BUTTON_TEXT"),
				"DESCR" => GetMessage("SALE_CONPAY_BUTTON_TEXT_DESC"),
				"VALUE" => "",
				"TYPE" => ""
			),
		);
		
		$arFields = array(
			'PAY_SYSTEM_ID' => $ps_id,
			'PERSON_TYPE_ID' => 1,
			'NAME' => GetMessage('CONPAY_PAYMENT_SYSTEM_NAME'),
			'ACTION_FILE' => '/bitrix/modules/sale/payment/conpay',
			'NEW_WINDOW' => 'Y',
			'PARAMS' => $arPSCorrespondence,			
		);
		
		$ps_action_id = CSalePaySystemAction::Add($arFields);
		
		if (!$ps_action_id) return false;
		
		RegisterModule('conpay');
		
		if (
			!COption::SetOptionString('conpay', 'ps_id', $ps_id)
			|| !COption::SetOptionString('conpay', 'ps_action_id', $ps_action_id)
		) return false;
		
		return true;
	}

	function DoUninstall()
	{
		$ps_id = COption::GetOptionString('conpay', 'ps_id');
		$ps_action_id = COption::GetOptionString('conpay', 'ps_action_id');
		
		if (!CSalePaySystem::Delete($ps_id)) {
			CSalePaySystem::Update($ps_id, array('ACTIVE' => 'N'));
		} else {
			CSalePaySystemAction::Delete($ps_action_id);
		}
		
		UnRegisterModule("conpay");
		
		return true;
	}
}

?>