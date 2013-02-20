<?php

class CConpay {
	
	function GetContent($arResult = array(), $custom_settings = array(), $custom_vars = array())
	{
		global $APPLICATION, $USER;
		static $head_script = '';
		static $category = '';
		
		if (!$arResult) return false;
		
		IncludeModuleLangFile(__FILE__);
		
		$details = array();
		$user_details = array();
		$section = 0; // 0: single product; 1: catalog; 2: cart;
		$price = 0;
		
		$products = array();
		
		if ($arResult['ITEMS']) {
			IF ($arResult['ITEMS']['AnDelCanBuy']) {
				$section = 2;
				$products = $arResult['ITEMS']['AnDelCanBuy'];
			} else {
				$section = 1;
				$products = $arResult['ITEMS'];
			}
		} else {
			$products = array($arResult);
		}
		
		foreach ($products as $item) {
			
			$p = 0; $q = 1;
			$prices = $item['PRICES']['BASE'];
			
			if (!$category) {
				$category = CConpay::GetCategoryNameById($item['ID']);
				if (!$category && isset($item['IBLOCK_CODE'])) {
					$category = CConpay::GetCategoryNameByCode($item['IBLOCK_CODE']);
				}
			}
			
			$details[] = array(
				'id'					=> $item['ID'],
				'name' 				=> $item['NAME'],
				'category' 		=> $category,
				'url' 				=> ($host = 'http://' . $_SERVER['HTTP_HOST']) . $item['DETAIL_PAGE_URL'],
				'image' 			=> $host . $item['DETAIL_PICTURE']['SRC'],
				
				// TODO: update quantity according to user's choice
				'quantity' 		=> ($q = (empty($item['QUANTITY']))? 1: $item['QUANTITY']),
				// TODO: учесть валюту цены
				'price' 			=>	($p = ($section == 2)?
													(float) str_replace(' ', '', $item["PRICE_FORMATED"]) :
													(($prices["DISCOUNT_VALUE"])? (float) str_replace(' ', '', $prices["PRINT_DISCOUNT_VALUE_VAT"]) : (float) str_replace(' ', '', $prices['PRINT_VALUE_VAT']))),
			);
			
			$price += $q * $p;
		}
		
		if ($price < COption::GetOptionString('conpay', 'MIN_PRICE', 3000)) return false;
		
		if ($user_id = $USER->GetID()) {
			$user_details['user_id'] = $user_id;
			if ($v = $USER->GetEmail())				$user_details['user_email'] 			= $v;
			if ($v = $USER->GetFirstName())		$user_details['user_name'] 				= $v;
			if ($v = $USER->GetLastName())		$user_details['user_lastname'] 		= $v;
		}
		
		$settings = array(
			'button_class_name' 	=> ($mp = COption::GetOptionString('conpay', 'BUTTON_CLASS_NAME'))? $mp : 'conpay-btn',
			'button_tag_name' 		=> ($mp = COption::GetOptionString('conpay', 'BUTTON_TAG_NAME'))? $mp : 'a',
			'button_text' 				=> ($mp = COption::GetOptionString('conpay', 'BUTTON_TEXT'))? $mp : GetMessage('CONPAY_CREDIT_PURCHASE'),
			'button_container_id' => COption::GetOptionString('conpay', 'BUTTON_CONTAINER_ID'),
		);
		
		$settings = array_merge($settings, $custom_settings);
		$custom_vars = array_merge($user_details, $custom_vars);
		
		if (!$head_script) {
			$head_script = '<script type="text/javascript" src="http://www.conpay.ru/public/api/btn.1.6.min.js"></script>';
			$head_script .= "
			<script type=\"text/javascript\">
				try {
					window.conpay.init('/bitrix/modules/conpay/conpay-proxy.php', {" .
						"'className': '" . $settings['button_class_name'] . "', " .
						"'tagName': '" . $settings['button_tag_name'] . "', " .
						"'text': '" . $settings['button_text'] . "'}" .
						(($custom_vars)? ', ' . json_encode($custom_vars) : '') .
					");
				} catch(e){};
			</script>";
			
			echo $head_script;
		}
		
		$script .= "
		<script type=\"text/javascript\">
			try {
				window.conpay.addButton(" . json_encode($details) . (($s = $settings['button_container_id'] && $section != 1)? ", '" . $s . "'" : '') . ");
			} catch(e){};
		</script>";
		
		echo $script;
		
		return true;
	}
	
	function GetCategoryNameById($product_id = 0) {
		
		global $DB;
		
		$sql = "
		SELECT s.name AS name
		FROM b_iblock_section s
		LEFT JOIN b_iblock_element p ON p.iblock_section_id = s.id
		WHERE p.id = $product_id;
		";
		
		$res = $DB->Query($sql);
		$res = $res->Fetch();
		
		if (!$res) return '';
		
		return $res['name'];
	}
	
	function GetCategoryNameByCode($code = '') {
		
		global $DB;
		
		$sql = "
		SELECT name
		FROM b_iblock
		WHERE code = '$code'
		";
		
		$res = $DB->Query($sql);
		$res = $res->Fetch();
		
		if (!$res) return '';
		
		return $res['name'];
	}
}

?>