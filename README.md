Плагин Conpay.ru для 1c-Bitrix 12.0 и 10.0
==========================================

## Установка

1. Скопируйте содержимое папки `/utf-8` в папку `/bitrix`.
2. В случае, если на сайте используется кодировка windows-1251, также скопируйте содержимое папки `/windows-1251` в папку `/bitrix`.
3. Установите основной модуль из списка модулей `(Найстройки -> Модули -> Сервис CONPAY.RU -> Установить)`.
4. Измените настройки модуля, используя данные для подключения сервиса из вашего Личного Кабинета на сайте conpay.ru. Незаполненные поля примут значения по-умолчанию.
5. Подключите модуль в шаблонах сайта:

* в шаблоне страницы товара `/bitrix/templates/[шаблон сайта]/components/bitrix/catalog/[.default]/bitrix/catalog.element/[.default]/template.php` добавить в блоке
```php
<?if($arResult["CAN_BUY"]):?>
```
следующую строку
```php
<?php if (CModule::IncludeModule('conpay')) CConpay::GetContent($arResult); ?>
```

* в шаблоне страницы каталога `/bitrix/templates/[шаблон сайта]/components/bitrix/catalog/[.default]/bitrix/catalog.section/[.default]/template.php` добавить в блоке
```php
<?foreach($arResult["ITEMS"] as $cell=>$arElement):?>
```
следующую строку
```php
<?php if (CModule::IncludeModule('conpay')) CConpay::GetContent($arElement); ?>
```
