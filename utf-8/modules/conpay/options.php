<?php
IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/options.php");
CModule::IncludeModule('iblock');

$aTabs = array(
	array(
		"DIV" => "basic_settings",
		"TAB" => GetMessage("CONPAY_BASIC_SETTINGS"),
		"TITLE" => GetMessage("CONPAY_BASIC_SETTINGS_TITLE"),
	),
	array(
		"DIV" => "advanced_settings",
		"TAB" => GetMessage("CONPAY_ADVANCED_SETTINGS"),
		"TITLE" => GetMessage("CONPAY_ADVANCED_SETTINGS_TITLE"),
	)
);

$tabControl = new CAdminTabControl("tabControl", $aTabs);

if ($REQUEST_METHOD == "POST" && strlen($Update.$Apply.$RestoreDefaults) > 0 && check_bitrix_sessid())
{
	if (strlen($RestoreDefaults) > 0)
	{
		COption::RemoveOption("conpay");
	}
	else
	{
		$set = array(
			'MERCHANT_ID' => $_REQUEST['merchant_id'],
			'API_KEY' => $_REQUEST['api_key'],
			'RESPONSE_PASS' => $_REQUEST['response_pass'],
<<<<<<< HEAD
			'BUTTON_CONTAINER_ID' => $_REQUEST['button_container_id'],
=======
			'PRICE_TYPES' => $_REQUEST['price_types'],
>>>>>>> Regular changes
			'MIN_PRICE' => $_REQUEST['min_price'],
			'BUTTON_CLASS_NAME' => $_REQUEST['button_class_name'],
			'BUTTON_TAG_NAME' => $_REQUEST['button_tag_name'],
			'BUTTON_TEXT' => $_REQUEST['button_text'],
		);

		foreach ($set as $k => $v)
		{
			COption::SetOptionString('conpay', $k, $v);
		}
	}

	if (strlen($Update) > 0 && strlen($_REQUEST["back_url_settings"]) > 0)
	{
		LocalRedirect($_REQUEST["back_url_settings"]);
	}
	else
	{
		LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($mid)."&lang=".urlencode(LANGUAGE_ID)."&back_url_settings=".urlencode($_REQUEST["back_url_settings"])."&".$tabControl->ActiveTabParam());
	}
}

$tabControl->Begin();
?>

<form method="post" action="<?php
	$APPLICATION->GetCurPage() ?>?mid=<?php
	echo urlencode($mid); ?>&amp;lang=<?php
	echo LANGUAGE_ID; ?>" name="conpay_options_form">

	<?php echo bitrix_sessid_post(); ?>
	<?php $tabControl->BeginNextTab(); ?>

	<tr>
		<td>
			<label><?php echo GetMessage('MERCHANT_ID'); ?></label>
		</td>
		<td>
			<input name="merchant_id" type="text" value="<?php echo COption::GetOptionString('conpay', 'MERCHANT_ID'); ?>" id="conpay_merchant_id" size="60" />
		</td>
	</tr>

	<tr>
		<td>
			<label><?php echo GetMessage('API_KEY'); ?></label>
		</td>
		<td>
			<input name="api_key" type="text" value="<?php echo COption::GetOptionString('conpay', 'API_KEY'); ?>" id="conpay_api_key" size="60" />
		</td>
	</tr>

	<tr>
		<td>
			<label><?php echo GetMessage('RESPONSE_PASS'); ?></label>
		</td>
		<td>
			<input name="response_pass" type="text" value="<?php echo COption::GetOptionString('conpay', 'RESPONSE_PASS'); ?>" id="conpay_response_pass" size="60" />
		</td>
	</tr>

	<tr>
		<td>
			<label><?php echo GetMessage('PRICE_TYPES'); ?></label>
		</td>
		<td>
			<input name="price_types" type="text" value="<?php echo COption::GetOptionString('conpay', 'PRICE_TYPES'); ?>" id="conpay_price_types" size="60" />
		</td>
	</tr>

	<tr>
		<td>
			<label><?php echo GetMessage('MIN_PRICE'); ?></label>
		</td>
		<td>
			<input name="min_price" type="text" value="<?php echo COption::GetOptionString('conpay', 'MIN_PRICE'); ?>" id="conpay_min_price" size="60" />
		</td>
	</tr>

	<?php $tabControl->BeginNextTab(); ?>

	<tr>
		<td>
			<label><?php echo GetMessage('BUTTON_CLASS_NAME'); ?></label>
		</td>
		<td>
			<input name="button_class_name" type="text" value="<?php echo COption::GetOptionString('conpay', 'BUTTON_CLASS_NAME'); ?>" id="conpay_button_class_name" size="60" />
		</td>
	</tr>

	<tr>
		<td>
			<label><?php echo GetMessage('BUTTON_TAG_NAME'); ?></label>
		</td>
		<td>
			<input name="button_tag_name" type="text" value="<?php echo COption::GetOptionString('conpay', 'BUTTON_TAG_NAME'); ?>" id="conpay_button_tag_name" size="60" />
		</td>
	</tr>

	<tr>
		<td>
			<label><?php echo GetMessage('BUTTON_TEXT'); ?></label>
		</td>
		<td>
			<textarea rows="10" cols="80" name="button_text" id="conpay_button_text"><?php echo COption::GetOptionString('conpay', 'BUTTON_TEXT'); ?></textarea>
		</td>
	</tr>

	<?php $tabControl->Buttons(); ?>
	<input type="submit" class="adm-btn-save" name="Update" value="<?php echo GetMessage("MAIN_SAVE"); ?>" title="<?php echo GetMessage("MAIN_OPT_SAVE_TITLE"); ?>" />
	<input type="submit" name="Apply" value="<?php echo GetMessage("MAIN_APPLY"); ?>" title="<?php echo GetMessage("MAIN_OPT_APPLY_TITLE"); ?>" />
	<?php if (strlen($_REQUEST["back_url_settings"]) > 0): ?>
		<input type="button" name="Cancel" value="<?php echo GetMessage("MAIN_OPT_CANCEL"); ?>" title="<?php echo GetMessage("MAIN_OPT_CANCEL_TITLE"); ?>" onclick="window.location='<?php echo htmlspecialcharsbx(CUtil::addslashes($_REQUEST["back_url_settings"])); ?>'" />
		<input type="hidden" name="back_url_settings" value="<?php echo htmlspecialcharsbx($_REQUEST["back_url_settings"]); ?>">
	<?php endif; ?>
	<input type="submit" name="RestoreDefaults" title="<?php echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS"); ?>" onclick="return confirm('<?php echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING")); ?>')" value="<?php echo GetMessage("MAIN_RESTORE_DEFAULTS"); ?>" />

	<?php $tabControl->End(); ?>
</form>
