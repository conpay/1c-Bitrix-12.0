1c-Bitrix-12.0
==============

Conpay.ru credit module for 1c-Bitrix v.12.0

��������� ������ ������ Conpay.ru �� CMS Bitrix 12:
1. �������������� �������� ������ � ���������� ��� � ����� "/bitrix/modules/conpay".
2. �������������� ��������� ������ � ���������� ��� � ����� "/bitrix/modules/sale/payment/conpay".
2. ���������� �������� ������ �� ������ ������� (���������� -> ������ -> ������ CONPAY.RU -> ����������).
4. ������� ��������� ������. ������������� ���� ������ �������� �� ���������.
5. ���������� ������ � �������� �����:
- � ������� �������� ������ "/bitrix/templates/[������ �����]/components/bitrix/catalog/[.default]/bitrix/catalog.element/[.default]/template.php" �������� ������ � ����� <?if($arResult["CAN_BUY"]):?>:
<?php if (CModule::IncludeModule('conpay')) CConpay::GetContent($arResult); ?>
{if $module == 'ProductView' || $module == 'ProductsView'}{include file='../../../payment/Conpay/conpay.tpl'}{/if}
- � ������� �������� �������� "/bitrix/templates/[������ �����]/components/bitrix/catalog/[.default]/bitrix/catalog.section/[.default]/template.php" �������� ������ � ����� <?foreach($arResult["ITEMS"] as $cell=>$arElement):?>:
<?php if (CModule::IncludeModule('conpay')) CConpay::GetContent($arElement); ?>