<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {die();}

include(GetLangFileName(dirname(__FILE__)."/", "/conpay.php"));

$button_text = htmlspecialchars_decode(CSalePaySystemAction::GetParamValue("BUTTON_TEXT"));

$order_id = (strlen(CSalePaySystemAction::GetParamValue("ORDER_ID")) > 0) ? CSalePaySystemAction::GetParamValue("ORDER_ID") : $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"];
$should_pay = (strlen(CSalePaySystemAction::GetParamValue("SHOULD_PAY")) > 0) ? CSalePaySystemAction::GetParamValue("SHOULD_PAY") : $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"];
$currency = (strlen(CSalePaySystemAction::GetParamValue("CURRENCY")) > 0) ? CSalePaySystemAction::GetParamValue("CURRENCY") : $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"];

// Выбираем заказанные товары из базы данных

global $DB;

$sql = "
SELECT	p.id AS id,
		p.name AS name,
		(
			SELECT GROUP_CONCAT(u.code SEPARATOR '/')
			FROM b_iblock_section u
			LEFT JOIN b_iblock ibp ON ibp.id = u.iblock_id
			WHERE u.left_margin <= s.left_margin AND u.right_margin >= s.right_margin AND ibp.id = ib.id
			ORDER BY u.left_margin
		) AS navchain,
		p.code AS code,
		s.code AS section,
		ib.iblock_type_id AS iblock_type_id,
		ib.detail_page_url AS detail_page_url,
		CONCAT('/upload/', i.subdir, '/', i.file_name) AS image,
		s.name AS category,
		b.price AS price,
		b.quantity AS quantity
FROM b_sale_basket b
LEFT JOIN b_iblock_element p ON b.product_id = p.id
LEFT JOIN b_iblock_section s ON p.iblock_section_id = s.id
LEFT JOIN b_file i ON p.detail_picture = i.id
LEFT JOIN b_iblock ib ON ib.id = s.iblock_id
WHERE b.order_id = $order_id;
";

$res = $DB->Query($sql, true);

// Заполняем массив товаров и передаем его в модуль

$products = array('ITEMS' => array());

while ($item = $res->Fetch())
{
	if ($item)
	{
		$url = '';

		// Определяем URL страницы товара
		$url_masks = array(
			'#SITE_DIR#' => '',
			'#SECTION_CODE#' => $item['section'],
			'#ELEMENT_CODE#' => $item['code'],
			'#ELEMENT_ID#' => $item['id']
		);

		if ($item['detail_page_url'])
		{
			$url = $item['detail_page_url'];
			foreach ($url_masks as $k => $v)
			{
				$url = str_replace($k, $v, $url);
			}
		}
		else
		{
			$url = ($item['iblock_type_id'] == 'catalog' ? '/catalog/' : '/').$item['navchain'].'/'.($item['code'] ? $item['code'] : $item['id']).'.html';
		}

		// Добавляем свойства товара к массиву
		$products['ITEMS'][] = array(
			'ID' => $item['id'],
			'NAME' => $item['name'],
			'SECTION' => array('NAME' => $item['category']),
			'DETAIL_PICTURE' => array('SRC' => $item['image']),
			'DETAIL_PAGE_URL' => $url,
			'QUANTITY' => $item['quantity'],
			'PRICE' => $item['price'],
		);
	}
}

if (CModule::IncludeModule('conpay'))
{
	CConpay::GetContent($products, array('button_text' => $button_text), array('order_id' => $order_id));
}
