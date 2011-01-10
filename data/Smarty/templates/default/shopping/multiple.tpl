<!--{*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2010 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *}-->
<!--▼CONTENTS-->
<script type="text/javascript" src="<!--{$smarty.const.URL_PATH}-->js/jquery.fancybox/jquery.mousewheel-3.0.4.pack.js"></script>
<script type="text/javascript" src="<!--{$smarty.const.URL_PATH}-->js/jquery.fancybox/jquery.fancybox-1.3.4.pack.js"></script>
<link rel="stylesheet" href="<!--{$smarty.const.URL_PATH}-->js/jquery.fancybox/jquery.fancybox-1.3.4.css" type="text/css" media="screen" />
<script type="text/javascript">//<![CDATA[
    $(document).ready(function() {
        $("a.expansion").fancybox({
        });
    });
//]]></script>
<div id="under02column">
    <div id="under02column_shopping">
        <p class="flowarea">
            <img src="<!--{$TPL_DIR}-->img/picture/img_flow_01.gif" width="700" height="36" alt="購入手続きの流れ" />
        </p>
        <h2 class="title"><!--{$tpl_title|h}--></h2>

        <p>各商品のお届け先を選択してください。</p>
        <!--{if $tpl_addrmax < $smarty.const.DELIV_ADDR_MAX}-->
            <p>一覧にご希望の住所が無い場合は、「新しいお届け先を追加する」より追加登録してください。</p>
        <!--{/if}-->
        <p>※最大<!--{$smarty.const.DELIV_ADDR_MAX|h}-->件まで登録できます。</p>

        <!--{if $tpl_addrmax < $smarty.const.DELIV_ADDR_MAX}-->
            <p class="addbtn">
                <a href="<!--{$smarty.const.URL_PATH}-->mypage/delivery_addr.php" onclick="win02('<!--{$smarty.const.URL_PATH}-->mypage/delivery_addr.php?page=<!--{$smarty.server.PHP_SELF|h}-->','new_deiv','600','640'); return false;" onmouseover="chgImg('<!--{$TPL_DIR}-->img/button/btn_add_address_on.gif','addition');" onmouseout="chgImg('<!--{$TPL_DIR}-->img/button/btn_add_address.gif','addition');"><img src="<!--{$TPL_DIR}-->img/button/btn_add_address.gif" width="160" height="22" alt="新しいお届け先を追加する" name="addition" id="addition" /></a>
            </p>
        <!--{/if}-->

            <table summary="商品情報">
                <tr>
                    <th>削除</th>
                    <th>商品写真</th>
                    <th>商品名</th>
                    <th>数量</th>
                    <th>お届け先</th>
                </tr>
                <!--{foreach from=$items item=item }-->
                    <tr style="<!--{if $item.error}-->background-color: <!--{$smarty.const.ERR_COLOR}-->;<!--{/if}-->">
                        <td><a href="?" onclick="fnFormModeSubmit('form<!--{$key}-->', 'delete', 'cart_no', '<!--{$item.cart_no}-->'); return false;">削除</a>
                        </td>
                        <td class="phototd">
                        <a
                            <!--{if $item.main_image|strlen >= 1}-->
                                href="<!--{$smarty.const.IMAGE_SAVE_URL}--><!--{$item.main_image|sfNoImageMainList|h}-->"
                                class="expansion"
                                target="_blank"
                            <!--{/if}-->
                        >
                            <img src="<!--{$smarty.const.URL_PATH}-->resize_image.php?image=<!--{$item.main_list_image|sfNoImageMainList|h}-->&amp;width=65&amp;height=65" alt="<!--{$item.productsClass.name|h}-->" /></a>
                        </td>
                        <td><!--{* 商品名 *}--><strong><!--{$item.name|h}--></strong><br />
                            <!--{if $item.classcategory_name1 != ""}-->
                                <!--{$item.class_name1}-->：<!--{$item.classcategory_name1}--><br />
                            <!--{/if}-->
                            <!--{if $item.classcategory_name2 != ""}-->
                                <!--{$item.class_name2}-->：<!--{$item.classcategory_name2}--><br />
                            <!--{/if}-->
                            <!--{$item.price02|sfCalcIncTax:$arrInfo.tax:$arrInfo.tax_rule|number_format}-->円
                        </td>
                        <td><input type="text" name="quantity" value="1" size="4" /></td>
                        <td><select name="shipping"><!--{html_options options=$addrs}--></select></td>
                     </tr>
                  <!--{/foreach}-->
            </table>

            <div class="tblareabtn">
                <a href="<!--{$smarty.const.URL_CART_TOP}-->" onmouseover="chgImgImageSubmit('<!--{$TPL_DIR}-->img/button/btn_back_on.gif',back03)" onmouseout="chgImgImageSubmit('<!--{$TPL_DIR}-->img/button/btn_back.gif',back03)">
                    <img src="<!--{$TPL_DIR}-->img/button/btn_back.gif" width="150" height="30" alt="戻る" border="0" name="back03" id="back03" /></a>
                <input type="image" onmouseover="chgImgImageSubmit('<!--{$TPL_DIR}-->img/button/btn_address_select_on.gif',this)" onmouseout="chgImgImageSubmit('<!--{$TPL_DIR}-->img/button/btn_address_select.gif',this)" src="<!--{$TPL_DIR}-->img/button/btn_address_select.gif" alt="選択したお届け先に送る" class="box190" name="send_button" id="send_button" />
            </div>

    </div>
</div>
