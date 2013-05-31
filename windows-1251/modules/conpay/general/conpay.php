<?php
  class CConpay
  {
    function GetContent($arResult = array(), $custom_settings = array(), $custom_vars = array())
    {
      global $APPLICATION, $USER;

      static $head_script = '';
      static $buttons = 0;

      if (!$arResult) return false;

      // ���������� �������� ����

      IncludeModuleLangFile(__FILE__);

      $details = array();
      $user_details = array();
      $total = 0;

      // ���� ������ $arResult �������� ���� �����, ������������ ��� � ���� ������� �������

      if (!isset($arResult['ITEMS'])) $arResult = array('ITEMS' => array($arResult));

      $price_types = explode(';', COption::GetOptionString('conpay', 'PRICE_TYPES', 'BASE'));

      // �������� �� ������� ������� � ������� ������ ������� ��� ������

      foreach ($arResult['ITEMS'] as $item)
      {
        $price = 0; // ���� ������
        $quantity = 1; // ���������� ������
        $category = ''; // ��������� ������
        $url = ''; // URL �������� ������
        $image = ''; // URL ����������� ������

        $host = 'http://' . $_SERVER['HTTP_HOST'];

        // ���������� ���� ������

        $price_type = '';

        foreach ($price_types as $type)
        {
          $type = trim($type);

          if (!empty($item['PRICES'][$type]))
          {
            $price_type = $type;
            break;
          }
        }

        if ($price_type)
        {
          if (!empty($item['PRICES'][$price_type]['DISCOUNT_VALUE']))
          {
            $price = (float) str_replace(' ', '', $item['PRICES'][$price_type]["PRINT_DISCOUNT_VALUE_VAT"]);
          }
          else
          {
            $price = (float) str_replace(' ', '', $item['PRICES'][$price_type]['PRINT_VALUE_VAT']);
          }
        }
        else
        {
          if (!empty($item["DISCOUNT_PRICE"]))
          {
            $price = (float) $item['DISCOUNT_PRICE'];
          }
          else
          {
            $price = (float) $item['PRICE'];
          }
        }

        // ���������� ���������� ������

        if (!empty($item['QUANTITY']))
        {
          $quantity = $item['QUANTITY'];
        }

        // ���������� ��������� ������

        if (!empty($item['SECTION']['NAME']))
        {
          $category = $item['SECTION']['NAME'];
        }
        else
        {
          $category = CConpay::GetCategoryNameById($item['ID']);

          if (!$category && !empty($item['IBLOCK_CODE']))
          {
            $category = CConpay::GetCategoryNameByCode($item['IBLOCK_CODE']);
          }
        }

        // ���������� URL �������� ������

        if (!empty($item['DETAIL_PAGE_URL']))
        {
          $url = $item['DETAIL_PAGE_URL'];
        }

        // Bitrix 10

        elseif (!empty($item['SECTION']['SECTION_PAGE_URL']))
        {
          $url = $item['SECTION']['SECTION_PAGE_URL'] . $item['ID'] . '.html';
        }
        elseif (!empty($item['SECTION_PAGE_URL']))
        {
          $url = $item['SECTION_PAGE_URL'] . $item['ID'] . '.html';
        }

        // ����� ������

        else
        {
          $url =
            ($item['IBLOCK_TYPE_ID'] == 'catalog' ? '/catalog/' : '/')
              .CConpay::GetProductNavChainByProductId($item['ID'], $item['IBLOCK_TYPE_ID'])
              .'/'.$item['ID'].'.html';
        }

        // ���������� URL ����������� ������

        if (isset($item['DETAIL_PICTURE']) && is_array($item['DETAIL_PICTURE']) && !empty($item['DETAIL_PICTURE']['SRC']))
        {
          $image = $item['DETAIL_PICTURE']['SRC'];
        }
        else
        {
          $image = CConpay::GetProductImageByProductId($item['ID']);
        }

        // ��������� ������ � ������ � ��������� ��� � ������� �������

        $details[] = array(
          'id' => $item['ID'],
          'name' => $item['NAME'],
          'category' => $category,
          'url' => $host.$url,
          'image' => $host.$image,
          // TODO: update quantity according to user's choice
          'quantity' => $quantity,
          // TODO: ������ ������ ����
          'price' => $price,
        );

        $total += $quantity * $price;
      }

      if ($total < COption::GetOptionString('conpay', 'MIN_PRICE', 3000) || $total > 200000) return false;

      // ��������� ������ � ������������

      if ($user_id = $USER->GetID())
      {
        $user_details['user_id'] = $user_id;

        if ($v = $USER->GetEmail())
        {
          $user_details['user_email'] = $v;
        }
        if ($v = $USER->GetFirstName())
        {
          $user_details['user_name'] = $v;
        }
        if ($v = $USER->GetLastName())
        {
          $user_details['user_lastname'] = $v;
        }
      }

      // �������� ��������� ������

      $settings = array(
        'button_class_name' => ($mp = COption::GetOptionString('conpay', 'BUTTON_CLASS_NAME')) ? $mp : 'conpay-btn',
        'button_tag_name' => ($mp = COption::GetOptionString('conpay', 'BUTTON_TAG_NAME')) ? $mp : 'a',
        'button_text' => ($mp = COption::GetOptionString('conpay', 'BUTTON_TEXT')) ? $mp : GetMessage('CONPAY_CREDIT_PURCHASE'),
        'button_container_id' => COption::GetOptionString('conpay', 'BUTTON_CONTAINER_ID'),
      );

      // �������� ��������� ������ ��������������� �����������

      $settings = array_merge($settings, $custom_settings);
      $custom_vars = array_merge($user_details, $custom_vars);

      // ������� ������� conpay.init, ���� ��� ��� �� ��������

      if (!$head_script)
      {
        $head_script = '<script type="text/javascript" src="//www.conpay.ru/public/api/btn.1.6.min.js"></script>';
        $head_script .= "
        <script type=\"text/javascript\">
          try {
            window.conpay.init('/bitrix/modules/conpay/conpay-proxy.php', {".
            "'className': '".$settings['button_class_name']."', ".
            "'tagName': '".$settings['button_tag_name']."', ".
            "'text': '".$settings['button_text']."'}".
            (($custom_vars) ? ', '.preg_replace('/^\[|,\]$/sm', '', CConpay::ConpayJsonEncode(array($custom_vars))) : '').
            ");
          } catch(e){};
        </script>";

        echo $head_script;
      }

      // ������� ��������� ������ � ������� conpay.addButton

      $script = "
      <div id=\"conpay-btn-" . $item['ID'] . "\"></div>
      <script type=\"text/javascript\">
        try {
          window.conpay.addButton("
          . CConpay::ConpayJsonEncode($details)
          . (($s = $settings['button_container_id'])? ", '" . $s . '-' . $item['ID'] . "'" : '')
          . ");
        } catch(e){};
      </script>";

      echo $script;

      // ��������� ������� ������

      $buttons++;

      return true;
    }

    function ConpayJsonEncode($data)
    {
      $c = count($data);
      $output = $c > 1? '[' : '';

      foreach ($data as $o)
      {
        $output .= '{';
        foreach ($o as $k => $v)
        {
          $output .= "'" . $k . "': '" . htmlspecialchars($v, ENT_QUOTES, 'WINDOWS-1251') . "',";
        }
        $output = rtrim($output, ',') . '},';
      }
      $output = rtrim($output, ',') . ($c > 1? ']' : '');

      return $output;
    }

    function GetProductImageByProductId($product_id = 0)
    {
      global $DB;

      $sql = "
      SELECT CONCAT('/upload/', i.subdir, '/', i.file_name) AS image
      FROM b_file i
      LEFT JOIN b_iblock_element p ON p.detail_picture = i.id
      WHERE p.id = $product_id
      ";

      $res = $DB->Query($sql);
      $res = $res->Fetch();

      if (!$res) return '';

      return $res['image'];
    }

    function GetCategoryNameById($product_id = 0)
    {
      global $DB;

      $sql = "
      SELECT s.name AS name
      FROM b_iblock_section s
      LEFT JOIN b_iblock_element p ON p.iblock_section_id = s.id
      WHERE p.id = $product_id
      ";

      $res = $DB->Query($sql);
      $res = $res->Fetch();

      if (!$res) return '';

      return $res['name'];
    }

    function GetProductNavChainByProductId($product_id, $iblock_type_id)
    {
      global $DB;

      $sql = "
      SELECT	(
        SELECT GROUP_CONCAT(u.code SEPARATOR '/')
        FROM b_iblock_section u
        LEFT JOIN b_iblock b ON b.id = iblock_id
        WHERE u.left_margin <= s.left_margin AND u.right_margin >= s.right_margin AND b.iblock_type_id = '$iblock_type_id'
        ORDER BY u.left_margin
      ) AS url
      FROM b_iblock_element p
      LEFT JOIN b_iblock_section s ON p.iblock_section_id = s.id
      WHERE p.id = $product_id
      ";

      $res = $DB->Query($sql);
      $res = $res->Fetch();

      if (!$res) return '';

      return $res['url'];
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