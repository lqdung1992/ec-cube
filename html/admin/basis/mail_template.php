<?php
/*
 * Copyright(c) 2000-2007 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 */
require_once("../require.php");

class LC_Page {
    var $arrSession;
    var $list_data;
    var $arrMagazineType;
    
    function LC_Page() {
        $this->tpl_mainpage = 'basis/template.tpl';
        $this->tpl_mainno = 'basis';
        $this->tpl_subnavi = 'basis/subnavi.tpl';
        $this->tpl_subno = 'mail';
        $this->tpl_subtitle = '�ƥ�ץ졼������';
    }
}

$conn = new SC_DBConn();
$objPage = new LC_Page();
$objView = new SC_AdminView();
$objSess = new SC_Session();

// ǧ�ڲ��ݤ�Ƚ��
sfIsSuccess($objSess);

if ( $_GET['mode'] == "delete" && sfCheckNumLength($_GET['id'])===true ){
    
    // ��Ͽ���
    $sql = "UPDATE dtb_mailtemplate SET del_flg = 1 WHERE template_id = ?";
    $conn->query($sql, array($_GET['id']));
    sfReload();
}


$sql = "SELECT * FROM dtb_mailtemplate WHERE del_flg = 0 ORDER BY create_date ASC";
$list_data = $conn->getAll($sql);
$objPage->list_data = $list_data;
$objPage->arrMagazineType = $arrMagazineTypeAll;


$objView->assignobj($objPage);
$objView->display(MAIN_FRAME);
?>