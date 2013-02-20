<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?

include(GetLangFileName(dirname(__FILE__)."/", "/conpay.php"));

$button_text 	= htmlspecialchars_decode(CSalePaySystemAction::GetParamValue("BUTTON_TEXT"));
$order_id 		= (strlen(CSalePaySystemAction::GetParamValue("ORDER_ID")) > 0) ? CSalePaySystemAction::GetParamValue("ORDER_ID") : $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["ID"];
$should_pay 	= (strlen(CSalePaySystemAction::GetParamValue("SHOULD_PAY")) > 0) ? CSalePaySystemAction::GetParamValue("SHOULD_PAY") : $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SHOULD_PAY"];
$currency 		= (strlen(CSalePaySystemAction::GetParamValue("CURRENCY")) > 0) ? CSalePaySystemAction::GetParamValue("CURRENCY") : $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"];

global $DB;

$sql = "
SELECT 	p.name AS name, 
				CONCAT('/catalog/', s.code, '/', p.code) AS url, 
				CONCAT('/upload/', i.subdir, '/', i.file_name) AS image,
				s.name AS category,
				b.price AS price,
				b.quantity AS quantity
FROM b_sale_basket b
LEFT JOIN b_iblock_element p ON b.product_id = p.id
LEFT JOIN b_iblock_section s ON p.iblock_section_id = s.id
LEFT JOIN b_file i ON p.detail_picture = i.id
WHERE b.order_id = $order_id;
";

$res = $DB->Query($sql, true);

$products = array('ITEMS' => array());
while($item = $res->Fetch()) {
	if ($item) {
		$products['ITEMS'][] = array(
			'NAME' => $item['name'],
			'SECTION' => array('NAME' => $item['category']),
			'DETAIL_PICTURE' => array('SRC' => $item['image']),
			'DETAIL_PAGE_URL' => $item['url'],
			'QUANTITY' => $item['quantity'],
			'PRICES' => array('BASE' => array('PRINT_VALUE_VAT' => $item['price'])),
		);
	}
}

if (CModule::IncludeModule('conpay')) {
	CConpay::GetContent($products, array('button_text' => $button_text), array('order_id' => $order_id));
}

?>
