<?php
/* --------------------------------------------------------------
   ProductInfoContentView.inc.php 2012-04-18 gambio
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2012 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------


   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(product_info.php,v 1.94 2003/05/04); www.oscommerce.com
   (c) 2003      nextcommerce (product_info.php,v 1.46 2003/08/25); www.nextcommerce.org
   (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com ($Id: product_info.php 1320 2005-10-25 14:21:11Z matthias $)


   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contribution:
   Customers Status v3.x  (c) 2002-2003 Copyright Elari elari@free.fr | www.unlockgsm.com/dload-osc/ | CVS : http://cvs.sourceforge.net/cgi-bin/viewcvs.cgi/elari/?sortby=date#dirlist
   New Attribute Manager v4b                            Autor: Mike G | mp3man@internetwork.net | http://downloads.ephing.com
   Cross-Sell (X-Sell) Admin 1                          Autor: Joshua Dechant (dreamscape)
   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// include needed functions
require_once (DIR_FS_INC.'xtc_get_download.inc.php');
require_once (DIR_FS_INC.'xtc_delete_file.inc.php');
require_once (DIR_FS_INC.'xtc_get_all_get_params.inc.php');
require_once (DIR_FS_INC.'xtc_date_long.inc.php');
require_once (DIR_FS_INC.'xtc_draw_hidden_field.inc.php');
require_once (DIR_FS_INC.'xtc_image_button.inc.php');
require_once (DIR_FS_INC.'xtc_draw_form.inc.php');
require_once (DIR_FS_INC.'xtc_draw_input_field.inc.php');
require_once (DIR_FS_INC.'xtc_image_submit.inc.php');

require_once (DIR_FS_INC.'xtc_check_categories_status.inc.php');
require_once (DIR_FS_INC.'xtc_get_products_mo_images.inc.php');
require_once (DIR_FS_INC.'xtc_get_vpe_name.inc.php');
require_once (DIR_FS_INC.'get_cross_sell_name.inc.php');

require_once(DIR_FS_INC . 'xtc_get_products_stock.inc.php');

require_once(DIR_FS_CATALOG . 'gm/inc/gm_prepare_number.inc.php');

class ProductInfoContentView extends ContentView
{
	function ProductInfoContentView($p_template = 'default')
	{
		if ($p_template == '' or $p_template == 'default') {
			$files = array ();
			if ($dir = opendir(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/product_info/')) {
				while ($file = readdir($dir)) {
					if (is_file(DIR_FS_CATALOG.'templates/'.CURRENT_TEMPLATE.'/module/product_info/'.$file) and ($file != "index.html") and (substr($file, 0, 1) !=".")) {
						$files[] = array ('id' => $file, 'text' => $file);
					} //if
				} // while
				closedir($dir);
			}
			$c_template = basename($files[0]['id']);
		}
		else
		{
			$c_template = basename($p_template);
		}
		
		$this->set_content_template('module/product_info/' . $c_template);
		$this->set_flat_assigns(true);
	}
	
	function get_html($p_coo_product, $p_current_category_id = 0)
	{
		$t_html_output = '';

		$xtPrice = new xtcPrice($_SESSION['currency'], $_SESSION['customers_status']['customers_status_id']);
		$main = new main();
		
		$group_check = '';

		// xs:booster start (v1.041)
		$xsb_tx = array();
		if(@is_array($_SESSION['xtb0']['tx'])) {
			foreach($_SESSION['xtb0']['tx'] as $tx) {
				if($tx['products_id']==$p_coo_product->data['products_id']) {
					$xsb_tx = $tx;
					break;
				}
			}
		}
		// xs:booster end

		if (!is_object($p_coo_product) || !$p_coo_product->isProduct()) { // product not found in database
			$error = TEXT_PRODUCT_NOT_FOUND;
			include (DIR_WS_MODULES.FILENAME_ERROR_HANDLER);
			$t_html_output = $main_content;
		} 
		else {
			if(ACTIVATE_NAVIGATOR == 'true')
			{
				$coo_product_navigator = MainFactory::create_object('ProductNavigatorContentView');
				$t_view_html = $coo_product_navigator->get_html($p_coo_product, $p_current_category_id);
				$this->set_content_data('PRODUCT_NAVIGATOR', $t_view_html);
			}
		
			xtc_db_query("update ".TABLE_PRODUCTS_DESCRIPTION." set products_viewed = products_viewed+1 where products_id = '".$p_coo_product->data['products_id']."' and language_id = '".$_SESSION['languages_id']."'");

			$products_price = $xtPrice->xtcGetPrice($p_coo_product->data['products_id'], $format = true, 1, $p_coo_product->data['products_tax_class_id'], $p_coo_product->data['products_price'], 1);


			// check if customer is allowed to add to cart
			// BOF GM_MOD:
			if ($_SESSION['customers_status']['customers_status_show_price'] != '0' && $xtPrice->gm_check_price_status($p_coo_product->data['products_id']) == 0) {
				// fsk18
				if ($_SESSION['customers_status']['customers_fsk18'] == '1') {
					if ($p_coo_product->data['products_fsk18'] == '0') {
						// BOF GM_MOD:
						$this->set_content_data('ADD_QTY', xtc_draw_input_field('products_qty', gm_convert_qty($p_coo_product->data['gm_min_order'], false), 'id="gm_attr_calc_qty"').' '.xtc_draw_hidden_field('products_id', $p_coo_product->data['products_id'], 'id="gm_products_id"'));
						$t_quantity = gm_convert_qty($p_coo_product->data['gm_min_order'], false);
						$t_disabled_quantity = 0;
						
						if(@$xsb_tx['XTB_ALLOW_USER_CHQTY']=='true'||$xsb_tx['products_id']!=$p_coo_product->data['products_id'])
						{
							$this->set_content_data('ADD_QTY', xtc_draw_input_field('products_qty', str_replace('.', ',', (double)$p_coo_product->data['gm_min_order']), 'id="gm_attr_calc_qty"').' '.xtc_draw_hidden_field('products_id', $p_coo_product->data['products_id'], 'id="gm_products_id"'));
						}
						else
						{
							$this->set_content_data('ADD_QTY', xtc_draw_input_field('products_qty', '1', 'disabled="disabled" style="background-color:gray;"').' '.xtc_draw_hidden_field('products_id', $p_coo_product->data['products_id'], 'id="gm_attr_calc_qty"'));
							$t_quantity = 1;
							$t_disabled_quantity = 1;
						}

						$this->set_content_data('QUANTITY', $t_quantity);
						$this->set_content_data('DISABLED_QUANTITY', $t_disabled_quantity);
						$this->set_content_data('ADD_CART_BUTTON', xtc_image_submit('button_in_cart.gif', IMAGE_BUTTON_IN_CART, 'id="cart_button"'));
						if(gm_get_conf('GM_SHOW_WISHLIST') == 'true') $this->set_content_data('ADD_WISHLIST_BUTTON', '<a href="javascript:submit_to_wishlist()" id="gm_wishlist_link">'.xtc_image_button('button_in_wishlist.gif', NC_WISHLIST).'</a>');
					}
					// BOF GM_MOD:
					else $this->set_content_data('GM_PID', xtc_draw_hidden_field('products_id', $p_coo_product->data['products_id'], 'id="gm_products_id"'));
				} else {
					// BOF GM_MOD:
					$this->set_content_data('ADD_QTY', xtc_draw_input_field('products_qty', gm_convert_qty($p_coo_product->data['gm_min_order'], false), 'id="gm_attr_calc_qty"').' '.xtc_draw_hidden_field('products_id', $p_coo_product->data['products_id'], 'id="gm_products_id"'));
					$t_quantity = gm_convert_qty($p_coo_product->data['gm_min_order'], false);
					$t_disabled_quantity = 0;

					if(@$xsb_tx['XTB_ALLOW_USER_CHQTY']=='true'||$xsb_tx['products_id']!=$p_coo_product->data['products_id'])
					{
						$this->set_content_data('ADD_QTY', xtc_draw_input_field('products_qty', str_replace('.', ',', (double)$p_coo_product->data['gm_min_order']), 'id="gm_attr_calc_qty"').' '.xtc_draw_hidden_field('products_id', $p_coo_product->data['products_id'], 'id="gm_products_id"'));
					}
					else
					{
						$this->set_content_data('ADD_QTY', xtc_draw_input_field('products_qty', '1', 'disabled="disabled" style="background-color:gray;"').' '.xtc_draw_hidden_field('products_id', $p_coo_product->data['products_id'], 'id="gm_attr_calc_qty"'));
						$t_quantity = 1;
						$t_disabled_quantity = 1;
					}

					$this->set_content_data('QUANTITY', $t_quantity);
					$this->set_content_data('DISABLED_QUANTITY', $t_disabled_quantity);
					$this->set_content_data('ADD_CART_BUTTON', xtc_image_submit('button_in_cart.gif', IMAGE_BUTTON_IN_CART, 'id="cart_button"'));
					if(gm_get_conf('GM_SHOW_WISH_LIST') == 'true') $this->set_content_data('ADD_WISHLIST_BUTTON', '<a href="javascript:submit_to_wishlist()">'.xtc_image_button('button_in_wishlist.gif', NC_WISHLIST).'</a>');
				}
			}
			// BOF GM_MOD:
			elseif($xtPrice->gm_check_price_status($p_coo_product->data['products_id']) > 0 || $_SESSION['customers_status']['customers_status_show_price'] == '0') $this->set_content_data('GM_PID', xtc_draw_hidden_field('products_id', $p_coo_product->data['products_id'], 'id="gm_products_id"'));


			if ($p_coo_product->data['products_fsk18'] == '1') {
				$this->set_content_data('PRODUCTS_FSK18', 'true');
			}
			// BOF GM_MOD:
			if (ACTIVATE_SHIPPING_STATUS == 'true' && $xtPrice->gm_check_price_status($p_coo_product->data['products_id']) == 0) {
				$this->set_content_data('SHIPPING_NAME', $main->getShippingStatusName($p_coo_product->data['products_shippingtime']));
				$this->set_content_data('SHIPPING_IMAGE', $main->getShippingStatusImage($p_coo_product->data['products_shippingtime']));
			}
			// BOF_GM_MOD:
			$this->set_content_data('FORM_ACTION', xtc_draw_form('cart_quantity', xtc_href_link(FILENAME_PRODUCT_INFO, xtc_get_all_get_params(array('action')).'action=add_product'),'post', 'name="cart_quantity" onsubmit="gm_qty_check = new GMOrderQuantityChecker(); return gm_qty_check.check();"'));

			$this->set_content_data('FORM_ACTION_URL', xtc_href_link(FILENAME_PRODUCT_INFO, xtc_get_all_get_params(array('action')).'action=add_product'));
			$this->set_content_data('FORM_ID', 'cart_quantity');
			$this->set_content_data('FORM_NAME', 'cart_quantity');
			$this->set_content_data('FORM_METHOD', 'post');

			// BOF GM_MOD GX-Customizer
			$coo_gm_gprint_product_manager = new GMGPrintProductManager();

			if($coo_gm_gprint_product_manager->get_surfaces_groups_id($p_coo_product->data['products_id']) !== false)
			{
				$coo_gm_gprint_configuration = new GMGPrintConfiguration($_SESSION['languages_id']);

				$this->set_content_data('GM_GPRINT_SHOW_PRODUCTS_DESCRIPTION', $coo_gm_gprint_configuration->get_configuration('SHOW_PRODUCTS_DESCRIPTION'));
				$this->set_content_data('GM_GPRINT', 1);
			}
			// EOF GM_MOD GX-Customizer

			$this->set_content_data('FORM_END', '</form>');
			$this->set_content_data('PRODUCTS_PRICE', $products_price['formated']);
			if ($p_coo_product->data['products_vpe_status'] == 1 && $p_coo_product->data['products_vpe_value'] != 0.0 && $products_price['plain'] > 0)
				$this->set_content_data('PRODUCTS_VPE', $xtPrice->xtcFormat($products_price['plain'] * (1 / $p_coo_product->data['products_vpe_value']), true).TXT_PER.xtc_get_vpe_name($p_coo_product->data['products_vpe']));
			$this->set_content_data('PRODUCTS_ID', $p_coo_product->data['products_id']);
			$this->set_content_data('PRODUCTS_NAME', $p_coo_product->data['products_name']);
			// BOF GM_MOD:
			if ($_SESSION['customers_status']['customers_status_show_price'] != 0  && ($xtPrice->gm_check_price_status($p_coo_product->data['products_id']) == 0 || ($xtPrice->gm_check_price_status($p_coo_product->data['products_id']) == 2 && $p_coo_product->data['products_price'] > 0)) ) {
				// price incl tax
				$tax_rate = $xtPrice->TAX[$p_coo_product->data['products_tax_class_id']];
				$tax_info = $main->getTaxInfo($tax_rate);
				$this->set_content_data('PRODUCTS_TAX_INFO', $tax_info);
				// BOF GM_MOD:
				if($xtPrice->gm_check_price_status($p_coo_product->data['products_id']) == 0)
				{
					$this->set_content_data('PRODUCTS_SHIPPING_LINK',$main->getShippingLink(true));
				}
			}

			// BOF GM_MOD
			if(gm_get_conf('GM_TELL_A_FRIEND') == 'true') $this->set_content_data('GM_TELL_A_FRIEND', 1);
			if($p_coo_product->data['gm_show_price_offer'] == 1 && $_SESSION['customers_status']['customers_status_show_price'] != '0' && $xtPrice->gm_check_price_status($p_coo_product->data['products_id']) == 0){
				$this->set_content_data('GM_PRICE_OFFER', 1);
			}
			if((double)$p_coo_product->data['gm_min_order'] != 1) $this->set_content_data('GM_MIN_ORDER', gm_convert_qty($p_coo_product->data['gm_min_order'], false));
			if((double)$p_coo_product->data['gm_graduated_qty'] != 1) $this->set_content_data('GM_GRADUATED_QTY', gm_convert_qty($p_coo_product->data['gm_graduated_qty'], false));
			// EOF GM_MOD

                        $this->set_content_data('PRODUCTS_MODEL', $p_coo_product->data['products_model']);
			$this->set_content_data('PRODUCTS_EAN', $p_coo_product->data['products_ean']);
			if($p_coo_product->data['gm_show_qty_info'] == 1)
			{
				$this->set_content_data('PRODUCTS_QUANTITY', gm_convert_qty(xtc_get_products_stock($p_coo_product->data['products_id']), false));
				if($p_coo_product->data['quantity_unit_id'] > 0)
				{
					$this->set_content_data('PRODUCTS_QUANTITY_UNIT', $p_coo_product->data['unit_name']);
				}
			}

			// BOF GM_MOD
			if($p_coo_product->data['gm_show_weight'] == '1') {
				$this->set_content_data('SHOW_PRODUCTS_WEIGHT', 1);
				$this->set_content_data('PRODUCTS_WEIGHT', gm_prepare_number($p_coo_product->data['products_weight'], $xtPrice->currencies[$xtPrice->actualCurr]['decimal_point']));
			}
			// EOF GM_MOD		

			$this->set_content_data('PRODUCTS_STATUS', $p_coo_product->data['products_status']);
			$this->set_content_data('PRODUCTS_ORDERED', $p_coo_product->data['products_ordered']);
			$this->set_content_data('PRODUCTS_PRINT', '<img src="templates/'.CURRENT_TEMPLATE.'/buttons/'.$_SESSION['language'].'/print.gif"  style="cursor:hand;" onclick="javascript:window.open(\''.xtc_href_link(FILENAME_PRINT_PRODUCT_INFO, 'products_id='.$p_coo_product->data['products_id']).'\', \'popup\', \'toolbar=0, width=640, height=600\')" alt="" />');

			//GM_MOD:
			$gmTabTokenizer = MainFactory::create_object('GMTabTokenizer', array(stripslashes($p_coo_product->data['products_description'])));
			$gm_products_descrition = $gmTabTokenizer->get_prepared_output();

			$this->set_content_data('PRODUCTS_DESCRIPTION', $gm_products_descrition);

			$t_gm_images_data = array();
			$t_thumbnails_array = array();
			$t_main_max_width = 369;
			$t_main_max_height = 279;
			$t_thumbnail_max_width = 86;
			$t_thumbnail_max_height = 86;

			// BOF GM_MOD
			require_once(DIR_FS_CATALOG . 'gm/classes/GMGMotion.php');
			$coo_gm_gmotion = new GMGMotion();

			$this->set_content_data('GMOTION', $coo_gm_gmotion->check_status($p_coo_product->data['products_id']));

			// EOF GM_MOD

				if(isset($xsb_tx['XTB_REDIRECT_USER_TO'])&&$xsb_tx['products_id']==$p_coo_product->data['products_id'])
					$this->set_content_data('XTB_REDIRECT_USER_TO', $xsb_tx['XTB_REDIRECT_USER_TO']);

			// BOF GM_MOD

			if($p_coo_product->data['products_image'] != '' && $p_coo_product->data['gm_show_image'] == '1')
			{
				$t_info_image_size_array = @getimagesize(DIR_WS_INFO_IMAGES . $p_coo_product->data['products_image']);
				$t_thumbnail_image_size_array = @getimagesize(DIR_WS_IMAGES . 'product_images/gallery_images/' . $p_coo_product->data['products_image']);

				$t_main_padding_left = 0;
				$t_main_padding_top = 0;

				if(isset($t_info_image_size_array[0]) && $t_info_image_size_array[0] < $t_main_max_width)
				{
					$t_main_padding_left = round(($t_main_max_width - $t_info_image_size_array[0]) / 2);
				}

				if(isset($t_info_image_size_array[1]) && $t_info_image_size_array[1] < $t_main_max_height)
				{
					$t_main_padding_top = round(($t_main_max_height - $t_info_image_size_array[1]) / 2);
				}

				$t_zoom_image = DIR_WS_POPUP_IMAGES . $p_coo_product->data['products_image'];

				if(file_exists(DIR_WS_ORIGINAL_IMAGES . $p_coo_product->data['products_image']))
				{
					$t_zoom_image = DIR_WS_ORIGINAL_IMAGES . $p_coo_product->data['products_image'];
				}

				$t_gm_images_data[] = array('IMAGE' => DIR_WS_INFO_IMAGES . $p_coo_product->data['products_image'],
											'IMAGE_ALT' => $p_coo_product->data['gm_alt_text'],
											'IMAGE_NR' => 0,
											'ZOOM_IMAGE' => $t_zoom_image,
											'PRODUCTS_NAME' => $p_coo_product->data['products_name'],
											'PADDING_LEFT' => $t_main_padding_left,
											'PADDING_TOP' => $t_main_padding_top,
											'WIDTH' => $t_info_image_size_array[0],
											'HEIGHT' => $t_info_image_size_array[1]
										);
				
				$t_thumbnail_padding_left = 0;
				$t_thumbnail_padding_top = 0;

				if(isset($t_thumbnail_image_size_array[0]) && $t_thumbnail_image_size_array[0] < $t_thumbnail_max_width)
				{
					$t_thumbnail_padding_left = round(($t_thumbnail_max_width - $t_thumbnail_image_size_array[0]) / 2);
				}

				if(isset($t_thumbnail_image_size_array[1]) && $t_thumbnail_image_size_array[1] < $t_thumbnail_max_height)
				{
					$t_thumbnail_padding_top = round(($t_thumbnail_max_height - $t_thumbnail_image_size_array[1]) / 2);
				}

				$t_thumbnails_array[] = array(	'IMAGE' => DIR_WS_IMAGES . 'product_images/gallery_images/' . $p_coo_product->data['products_image'],
												'IMAGE_ALT' => $p_coo_product->data['gm_alt_text'],
												'IMAGE_NR' => 0,
												'ZOOM_IMAGE' => $t_zoom_image,
												'INFO_IMAGE' => DIR_WS_INFO_IMAGES . $p_coo_product->data['products_image'],
												'PRODUCTS_NAME' => $p_coo_product->data['products_name'],
												'PADDING_LEFT' => $t_thumbnail_padding_left,
												'PADDING_TOP' => $t_thumbnail_padding_top
											);
			}

			$t_gm_images = xtc_get_products_mo_images($p_coo_product->data['products_id']);

			if($t_gm_images != false)
			{
				$coo_gm_alt_form = MainFactory::create_object('GMAltText');

				foreach($t_gm_images as $t_gm_image)
				{
					$t_info_image_size_array = @getimagesize(DIR_WS_INFO_IMAGES . $t_gm_image['image_name']);
					$t_thumbnail_image_size_array = @getimagesize(DIR_WS_IMAGES . 'product_images/gallery_images/' . $t_gm_image['image_name']);

					$t_main_padding_left = 0;
					$t_main_padding_top = 0;

					if(isset($t_info_image_size_array[0]) && $t_info_image_size_array[0] < $t_main_max_width)
					{
						$t_main_padding_left = round(($t_main_max_width - $t_info_image_size_array[0]) / 2);
					}

					if(isset($t_info_image_size_array[1]) && $t_info_image_size_array[1] < $t_main_max_height)
					{
						$t_main_padding_top = round(($t_main_max_height - $t_info_image_size_array[1]) / 2);
					}

					$t_zoom_image = DIR_WS_POPUP_IMAGES . $t_gm_image['image_name'];

					if(file_exists(DIR_WS_ORIGINAL_IMAGES . $t_gm_image['image_name']))
					{
						$t_zoom_image = DIR_WS_ORIGINAL_IMAGES . $t_gm_image['image_name'];
					}

					$t_gm_images_data[] = array('IMAGE' => DIR_WS_INFO_IMAGES . $t_gm_image['image_name'],
												'IMAGE_ALT' => $coo_gm_alt_form->get_alt($t_gm_image["image_id"], $t_gm_image['image_nr'], $p_coo_product->data['products_id']),
												'IMAGE_NR' => $t_gm_image['image_nr'],
												'ZOOM_IMAGE' => $t_zoom_image,
												'PRODUCTS_NAME' => $p_coo_product->data['products_name'],
												'PADDING_LEFT' => $t_main_padding_left,
												'PADDING_TOP' => $t_main_padding_top,
												'IMAGE_POPUP_URL' => DIR_WS_POPUP_IMAGES . $t_gm_image['image_name'],
											);

					$t_thumbnail_padding_left = 0;
					$t_thumbnail_padding_top = 0;

					if(isset($t_thumbnail_image_size_array[0]) && $t_thumbnail_image_size_array[0] < $t_thumbnail_max_width)
					{
						$t_thumbnail_padding_left = round(($t_thumbnail_max_width - $t_thumbnail_image_size_array[0]) / 2);
					}

					if(isset($t_thumbnail_image_size_array[1]) && $t_thumbnail_image_size_array[1] < $t_thumbnail_max_height)
					{
						$t_thumbnail_padding_top = round(($t_thumbnail_max_height - $t_thumbnail_image_size_array[1]) / 2);
					}

					$t_thumbnails_array[] =  array(	'IMAGE' => DIR_WS_IMAGES . 'product_images/gallery_images/' . $t_gm_image['image_name'],
													'IMAGE_ALT' => $coo_gm_alt_form->get_alt($t_gm_image["image_id"], $t_gm_image['image_nr'], $p_coo_product->data['products_id']),
													'IMAGE_NR' => $t_gm_image['image_nr'],
													'ZOOM_IMAGE' => $t_zoom_image,
													'INFO_IMAGE' => DIR_WS_INFO_IMAGES . $t_gm_image['image_name'],
													'PRODUCTS_NAME' => $p_coo_product->data['products_name'],
													'PADDING_LEFT' => $t_thumbnail_padding_left,
													'PADDING_TOP' => $t_thumbnail_padding_top
												);
				}		
			}

			$this->set_content_data('images', $t_gm_images_data);
			$this->set_content_data('thumbnails', $t_thumbnails_array);
			// EOF GM_MOD

			$discount = 0.00;
			if ($_SESSION['customers_status']['customers_status_discount'] != '0.00') { // BOF GM_MOD:
				$discount = $_SESSION['customers_status']['customers_status_discount'];
				if ($p_coo_product->data['products_discount_allowed'] < $_SESSION['customers_status']['customers_status_discount'])
					$discount = $p_coo_product->data['products_discount_allowed'];
				if ($discount != '0.00')
					$this->set_content_data('PRODUCTS_DISCOUNT', $discount.'%');
			}

			// BOF GM_MOD
			if(PRODUCT_IMAGE_INFO_WIDTH < (190 - 16)){
				$this->set_content_data('MIN_IMAGE_WIDTH', 188);
				$this->set_content_data('MIN_INFO_BOX_WIDTH', 156-10);
				$this->set_content_data('MARGIN_LEFT', 188+10);
			}
			else{
				$this->set_content_data('MIN_IMAGE_WIDTH', PRODUCT_IMAGE_INFO_WIDTH+16);
				$this->set_content_data('MIN_INFO_BOX_WIDTH', PRODUCT_IMAGE_INFO_WIDTH+16-32-10);
				$this->set_content_data('MARGIN_LEFT', PRODUCT_IMAGE_INFO_WIDTH+16+10);
			}
			// EOF GM_MOD

			$coo_product_attributes = MainFactory::create_object('ProductAttributesContentView', array($p_coo_product->data['options_template']));
			$t_view_html = $coo_product_attributes->get_html($p_coo_product);
			$this->set_content_data('MODULE_product_options', $t_view_html);

			$coo_product_reviews = MainFactory::create_object('ProductReviewsContentView');
			$t_view_html = $coo_product_reviews->get_html($p_coo_product);
			$this->set_content_data('MODULE_products_reviews', $t_view_html);

			if (xtc_not_null($p_coo_product->data['products_url']))
				$this->set_content_data('PRODUCTS_URL', sprintf(TEXT_MORE_INFORMATION, xtc_href_link(FILENAME_REDIRECT, 'action=product&id='.$p_coo_product->data['products_id'], 'NONSSL', true)));

			if ($p_coo_product->data['products_date_available'] > date('Y-m-d H:i:s')) {
				$this->set_content_data('PRODUCTS_DATE_AVIABLE', sprintf(TEXT_DATE_AVAILABLE, xtc_date_long($p_coo_product->data['products_date_available'])));
			} else {
				// BOF GM_MOD:
				if ($p_coo_product->data['products_date_added'] != '0000-00-00 00:00:00' && $p_coo_product->data['gm_show_date_added'] == 1)
					$this->set_content_data('PRODUCTS_ADDED', sprintf(TEXT_DATE_ADDED, xtc_date_long($p_coo_product->data['products_date_added'])));
			}

			$coo_product_media = MainFactory::create_object('ProductMediaContentView');
			$t_view_html = $coo_product_media->get_html($p_coo_product->data['products_id'], $_SESSION['languages_id']);
			$this->set_content_data('MODULE_products_media', $t_view_html);

			$coo_graduated_prices = MainFactory::create_object('GraduatedPricesContentView');
			$t_view_html = $coo_graduated_prices->get_html($p_coo_product);
			$this->set_content_data('MODULE_graduated_price', $t_view_html);

			$coo_also_purchased = MainFactory::create_object('AlsoPurchasedContentView');
			$t_view_html = $coo_also_purchased->get_html($p_coo_product);
			$this->set_content_data('MODULE_also_purchased', $t_view_html);

			$coo_cross_selling = MainFactory::create_object('CrossSellingContentView', array('cross_selling'));
			$t_view_html = $coo_cross_selling->get_html($p_coo_product);
			$this->set_content_data('MODULE_cross_selling', $t_view_html);

			$coo_reverse_cross_selling = MainFactory::create_object('CrossSellingContentView', array('reverse_cross_selling'));
			$t_view_html = $coo_reverse_cross_selling->get_html($p_coo_product);
			$this->set_content_data('MODULE_reverse_cross_selling', $t_view_html);

			$i = count($_SESSION['tracking']['products_history']);
			if ($i > 6) {
				array_shift($_SESSION['tracking']['products_history']);
				$_SESSION['tracking']['products_history'][6] = $p_coo_product->data['products_id'];
				$_SESSION['tracking']['products_history'] = array_unique($_SESSION['tracking']['products_history']);
			} else {
				$_SESSION['tracking']['products_history'][$i] = $p_coo_product->data['products_id'];
				$_SESSION['tracking']['products_history'] = array_unique($_SESSION['tracking']['products_history']);
			}

			$coo_stop_watch = new StopWatch();
			$coo_stop_watch->start();

			$coo_properties_view = MainFactory::create_object('PropertiesView', array($_GET, $_POST));
			$t_properties_selection_form = $coo_properties_view->get_selection_form($p_coo_product->data['products_id'], $_SESSION['languages_id']);
			if(trim($t_properties_selection_form) != ""){ 
                if($p_coo_product->data['gm_show_qty_info'] == 1)
                {
                    if(($p_coo_product->data['use_properties_combis_quantity'] == 0 && STOCK_CHECK == 'true' && ATTRIBUTES_STOCK_CHECK == 'true') || $p_coo_product->data['use_properties_combis_quantity'] == 2){
                        $this->set_content_data('PRODUCTS_QUANTITY', '-');
                        $this->set_content_data('SHOW_PRODUCTS_QUANTITY', true);
                    }else if($p_coo_product->data['use_properties_combis_quantity'] == 1){
                        $this->set_content_data('SHOW_PRODUCTS_QUANTITY', true);
                    }
                }                
				$this->set_content_data('SHOW_PRODUCTS_MODEL', true);
                if(APPEND_PROPERTIES_MODEL == "false" || trim($p_coo_product->data['products_model']) == ''){
                    $this->set_content_data('PRODUCTS_MODEL', '-');
                }   
                if (ACTIVATE_SHIPPING_STATUS == 'true' && $xtPrice->gm_check_price_status($p_coo_product->data['products_id']) == 0 && $p_coo_product->data['use_properties_combis_shipping_time'] == 1) {
                    $this->set_content_data('SHOW_SHIPPING_TIME', true);
                    $this->set_content_data('SHIPPING_NAME', '');
                    $this->set_content_data('SHIPPING_IMAGE', 'admin/images/icons/gray.png');
                }
			}
			$this->set_content_data('properties_selection_form', $t_properties_selection_form);

			$coo_stop_watch->stop();
			//$coo_stop_watch->log_total_time('PropertiesView get_selection_form');

			// BOF GM_MOD
			$t_gm_show_wishlist = gm_get_conf('GM_SHOW_WISHLIST');
			if($t_gm_show_wishlist == 'true')
			{
				$this->set_content_data('GM_SHOW_WISHLIST', 1);
			}

			$t_show_facebook = gm_get_conf('SHOW_FACEBOOK');
			if($t_show_facebook == 'true')
			{
				$this->set_content_data('SHOW_FACEBOOK', 1);
			}

			$t_show_twitter = gm_get_conf('SHOW_TWITTER');
			if($t_show_twitter == 'true')
			{
				$this->set_content_data('SHOW_TWITTER', 1);
			}

			$t_show_googleplus = gm_get_conf('SHOW_GOOGLEPLUS');
			if($t_show_googleplus == 'true')
			{
				$this->set_content_data('SHOW_GOOGLEPLUS', 1);
			}

			$t_show_pinterest = gm_get_conf('SHOW_PINTEREST');
			if($t_show_pinterest == 'true')
			{
				$this->set_content_data('SHOW_PINTEREST', 1);
			}

			$t_show_print = gm_get_conf('SHOW_PRINT');
			if($t_show_print == 'true')
			{
				$this->set_content_data('SHOW_PRINT', 1);
			}

			$t_show_bookmarking = gm_get_conf('SHOW_BOOKMARKING');
			if($t_show_bookmarking == 'true')
			{
				$this->set_content_data('SHOW_BOOKMARKING', 1);
			}
			// EOF GM_MOD

           // Bitcoin Payment - Commerce Coding - BEGIN
           $multiplier = 1;
           $digits = 8;
           switch (MODULE_PAYMENT_BITCOIN_UNITS) {
               case 'uBTC':
                   $multiplier *= 1000;
                   $digits -= 3;
               case 'mBTC':
                   $multiplier *= 1000;
                   $digits -= 3;
               case 'BTC':
                   $btcPrice = number_format($products_price['plain'] / MODULE_PAYMENT_BITCOIN_BTCEUR * $multiplier, $digits, '.', '');
                   $this->set_content_data('BITCOIN_PRICE', $btcPrice . ' ' . MODULE_PAYMENT_BITCOIN_UNITS);
           }
           // Bitcoin Payment - Commerce Coding - END

			include_once DIR_FS_DOCUMENT_ROOT.'/shopgate/plugins/gambiogx/system/views/product_info/ProductInfoContentView.inc.php';
			$t_html_output = $this->build_html();
		}
		
		return $t_html_output;
	}
	
}
?>