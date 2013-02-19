Плагин Conpay.ru для 1c-Bitrix 12.0
===================================

## Установка

1. Разархивируйте основной модуль и скопируйте его в папку "/bitrix/modules/conpay".
2. Разархивируйте платежный модуль и скопируйте его в папку "/bitrix/modules/sale/payment/conpay".
2. Установите основной модуль из списка модулей (Найстройки -> Модули -> Сервис CONPAY.RU -> Установить).
4. Внесите настройки модуля. Незаполненные поля примут значения по умолчанию.
5. Подключите модуль в шаблонах сайта:
* в шаблоне страницы товара "/bitrix/templates/[шаблон сайта]/components/bitrix/catalog/[.default]/bitrix/catalog.element/[.default]/template.php" добавить строку в блоке

```php
<?if($arResult["CAN_BUY"]):?>:
<?php if (CModule::IncludeModule('conpay')) CConpay::GetContent($arResult); ?>
```

* в шаблоне страницы каталога "/bitrix/templates/[шаблон сайта]/components/bitrix/catalog/[.default]/bitrix/catalog.section/[.default]/template.php" добавить строку в блоке

```php
<?foreach($arResult["ITEMS"] as $cell=>$arElement):?>:
<?php if (CModule::IncludeModule('conpay')) CConpay::GetContent($arElement); ?>
```
