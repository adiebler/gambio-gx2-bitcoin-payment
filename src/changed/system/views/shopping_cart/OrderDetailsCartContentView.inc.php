<?php
/* --------------------------------------------------------------
   OrderDetailsCartContentView.inc.php 2012-03-23 gambio
   Gambio GmbH
   http://www.gambio.de
   Copyright (c) 2012 Gambio GmbH
   Released under the GNU General Public License (Version 2)
   [http://www.gnu.org/licenses/gpl-2.0.html]
   --------------------------------------------------------------


   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(order_details.php,v 1.8 2003/05/03); www.oscommerce.com
   (c) 2003	 nextcommerce (order_details.php,v 1.16 2003/08/17); www.nextcommerce.org
   (c) 2003 XT-Commerce - community made shopping http://www.xt-commerce.com ($Id: order_details_cart.php 1281 2005-10-03 09:30:17Z mz $)

   Released under the GNU General Public License
   -----------------------------------------------------------------------------------------
   Third Party contribution:

   Customers Status v3.x  (c) 2002-2003 Copyright Elari elari@free.fr | www.unlockgsm.com/dload-osc/ | CVS : http://cvs.sourceforge.net/cgi-bin/viewcvs.cgi/elari/?sortby=date#dirlist

   Credit Class/Gift Vouchers/Discount Coupons (Version 5.10)
   http://www.oscommerce.com/community/contributions,282
   Copyright (c) Strider | Strider@oscworks.com
   Copyright (c  Nick Stanko of UkiDev.com, nick@ukidev.com
   Copyright (c) Andre ambidex@gmx.net
   Copyright (c) 2001,2002 Ian C Wilson http://www.phesis.org

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

// include needed functions
require_once (DIR_FS_INC.'xtc_check_stock.inc.php');
require_once (DIR_FS_INC.'xtc_get_products_stock.inc.php');
require_once (DIR_FS_INC.'xtc_remove_non_numeric.inc.php');
require_once (DIR_FS_INC.'xtc_get_short_description.inc.php');
require_once (DIR_FS_INC.'xtc_format_price.inc.php');
require_once (DIR_FS_INC.'xtc_get_attributes_model.inc.php');

require_once(DIR_FS_CATALOG . 'gm/inc/gm_prepare_number.inc.php');

class OrderDetailsCartContentView extends ContentView
{
	function OrderDetailsCartContentView()
	{
		$this->set_content_template('module/order_details.html');
	}
	
	function get_html($p_products_array)
	{
		$coo_properties_control = MainFactory::create_object('PropertiesControl');
		$coo_properties_view = MainFactory::create_object('PropertiesView');
			
		$t_content_array = array();
		
		$module_content = array ();
		$any_out_of_stock = '';
		$mark_stock = '';

		$coo_seo_boost = MainFactory::create_object('GMSEOBoost');
		$coo_main = new main();
		$xtPrice = new xtcPrice($_SESSION['currency'], $_SESSION['customers_status']['customers_status_id']);

		for ($i = 0, $n = sizeof($p_products_array); $i < $n; $i ++)
		{
			$t_combis_id = $coo_properties_control->extract_combis_id($p_products_array[$i]['id']);
            
            // check if combis_id is empty
            if($t_combis_id == '')
            {
                // combis_id is empty = article without properties
                if(STOCK_CHECK == 'true')
                {
                    $mark_stock = xtc_check_stock($p_products_array[$i]['id'], $p_products_array[$i]['quantity']);
                    if($mark_stock)
                    {
                        $_SESSION['any_out_of_stock'] = 1;
                    }
                }
            }      

			$image = '';
			if ($p_products_array[$i]['image'] != '') {
				$image = DIR_WS_THUMBNAIL_IMAGES.$p_products_array[$i]['image'];
			}


			//bof gm
			$gm_products_id = $p_products_array[$i]['id'];
			$gm_products_id = str_replace('{', '_', $gm_products_id);
			$gm_products_id = str_replace('}', '_', $gm_products_id);

			$gm_query = xtc_db_query("SELECT gm_show_weight FROM products WHERE products_id='" . $p_products_array[$i]['id'] . "'");
			$gm_array = xtc_db_fetch_array($gm_query);
			if(empty($gm_array['gm_show_weight'])) { $p_products_array[$i]['gm_weight'] = 0; }
			
			$gm_product_link = xtc_href_link(FILENAME_PRODUCT_INFO, xtc_product_link($p_products_array[$i]['id'], $p_products_array[$i]['name']) . '&no_boost=1');
			include(DIR_FS_CATALOG . 'gm/modules/gm_gprint_order_details_cart.php');
			
			$t_shipping_time = $p_products_array[$i]['shipping_time'];
			$t_products_weight = $p_products_array[$i]['gm_weight'];
			
			$t_products_model = $p_products_array[$i]['model'];
			
			#properties
			if($t_combis_id != '')
			{
				$t_properties_html = $coo_properties_view->get_order_details_by_combis_id($t_combis_id, 'cart');     

                $coo_products = MainFactory::create_object('GMDataObject', array('products', array('products_id' => $p_products_array[$i]['id']) ));
                $use_properties_combis_quantity = $coo_products->get_data_value('use_properties_combis_quantity');               
                
                if($use_properties_combis_quantity == 1){
                    // check article quantity
                    $mark_stock = xtc_check_stock($p_products_array[$i]['id'], $p_products_array[$i]['quantity']);
                    if ($mark_stock){
                        $_SESSION['any_out_of_stock'] = 1;
                    }
                }else if(($use_properties_combis_quantity == 0 && ATTRIBUTE_STOCK_CHECK == 'true' && STOCK_CHECK == 'true') || $use_properties_combis_quantity == 2){
                    // check combis quantity
                    $t_properties_stock = $coo_properties_control->get_properties_combis_quantity($t_combis_id);
					if($t_properties_stock < $p_products_array[$i]['quantity'])
					{
						$_SESSION['any_out_of_stock'] = 1;
						$mark_stock = '<span class="markProductOutOfStock">' . STOCK_MARK_PRODUCT_OUT_OF_STOCK . '</span>';
					}
                }
                
                $t_weight = $coo_properties_control->get_properties_combis_weight($t_combis_id);

                if($coo_products->get_data_value('use_properties_combis_weight') == 1){
                    $t_products_weight = gm_prepare_number($t_weight, $xtPrice->currencies[$xtPrice->actualCurr]['decimal_point']);
                }else{
                    $t_products_weight = gm_prepare_number($t_weight+$p_products_array[$i]['weight'], $xtPrice->currencies[$xtPrice->actualCurr]['decimal_point']);
                }
                         
                if($coo_products->get_data_value('use_properties_combis_shipping_time') == 1){
                    $t_shipping_time = $coo_properties_control->get_properties_combis_shipping_time($t_combis_id);
                }
                
				$t_combi_model = $coo_properties_control->get_properties_combis_model($t_combis_id);
				
				if(APPEND_PROPERTIES_MODEL == "true") {
                    // Artikelnummer (Kombi) an Artikelnummer (Artikel) anhängen
                    if($t_products_model != '' && $t_combi_model != ''){
                        $t_products_model = $t_products_model .'-'. $t_combi_model;
                    }else if($t_combi_model != ''){
                        $t_products_model = $t_combi_model;
                    }
				}else{
                    // Artikelnummer (Artikel) durch Artikelnummer (Kombi) ersetzen
                    if($t_combi_model != ''){
                        $t_products_model = $t_combi_model;
                    }
				}
			}
			else {
				$t_properties_html = '';
			}

			$module_content[$i] = array (
				'PRODUCTS_NAME'					=> $p_products_array[$i]['name'].$mark_stock,
				'PRODUCTS_QTY'					=> xtc_draw_input_field('cart_quantity[]', gm_convert_qty($p_products_array[$i]['quantity'], false), ' size="2" onblur="gm_qty_is_changed(' . $p_products_array[$i]['quantity'] . ', this.value, \'' . GM_QTY_CHANGED_MESSAGE . '\')"', 'text', true, "gm_cart_data gm_class_input").xtc_draw_hidden_field('products_id[]', $p_products_array[$i]['id'], 'class="gm_cart_data"').xtc_draw_hidden_field('old_qty[]', $p_products_array[$i]['quantity']),

				'PRODUCTS_OLDQTY_INPUT_NAME'	=> 'old_qty[]',
				'PRODUCTS_QTY_INPUT_NAME'		=> 'cart_quantity[]',
				'PRODUCTS_QTY_VALUE'            => gm_convert_qty($p_products_array[$i]['quantity'], false),
				'PRODUCTS_ID_INPUT_NAME'       	=> 'products_id[]',
				'PRODUCTS_ID_EXTENDED'			=> $p_products_array[$i]['id'],

				'PRODUCTS_MODEL'				=> $t_products_model,
				'SHOW_PRODUCTS_MODEL'			=> SHOW_PRODUCTS_MODEL,
				'PRODUCTS_SHIPPING_TIME'		=> $t_shipping_time,
				'PRODUCTS_TAX'					=> (double)$p_products_array[$i]['tax'],
				'PRODUCTS_IMAGE'				=> $image,
				'IMAGE_ALT'						=> $p_products_array[$i]['name'],
				'BOX_DELETE'					=> xtc_draw_checkbox_field('cart_delete[]', $p_products_array[$i]['id'], false, 'id="gm_delete_product_' . $gm_products_id . '"'),
				'PRODUCTS_LINK'					=> $gm_product_link,
				'PRODUCTS_PRICE'				=> $xtPrice->xtcFormat($p_products_array[$i]['price'] * $p_products_array[$i]['quantity'], true),
				'PRODUCTS_SINGLE_PRICE'			=> $xtPrice->xtcFormat($p_products_array[$i]['price'], true),
				'PRODUCTS_SHORT_DESCRIPTION'	=> xtc_get_short_description($p_products_array[$i]['id']),
				'ATTRIBUTES'					=> '',
				'PROPERTIES'					=> $t_properties_html,
				'GM_WEIGHT'						=> $t_products_weight,
				'PRODUCTS_ID'					=> $gm_products_id,
				'UNIT'							=> $p_products_array[$i]['unit_name']
			);
			//eof gm


			// Product options names
			$attributes_exist = ((isset ($p_products_array[$i]['attributes'])) ? 1 : 0);

			if ($attributes_exist == 1) {
				reset($p_products_array[$i]['attributes']);

				while (list ($option, $value) = each($p_products_array[$i]['attributes'])) {

					if (ATTRIBUTE_STOCK_CHECK == 'true' && STOCK_CHECK == 'true' && $value != 0) {
						$attribute_stock_check = xtc_check_stock_attributes($p_products_array[$i][$option]['products_attributes_id'], $p_products_array[$i]['quantity']);
						if ($attribute_stock_check)
							$_SESSION['any_out_of_stock'] = 1;
					}

					$module_content[$i]['ATTRIBUTES'][] = array ('ID' => $p_products_array[$i][$option]['products_attributes_id'], 'MODEL' => xtc_get_attributes_model(xtc_get_prid($p_products_array[$i]['id']), $p_products_array[$i][$option]['products_options_values_name'],$p_products_array[$i][$option]['products_options_name']), 'NAME' => $p_products_array[$i][$option]['products_options_name'], 'VALUE_NAME' => $p_products_array[$i][$option]['products_options_values_name'].$attribute_stock_check);

					// BOF GM_MOD GX-Customizer:
					require(DIR_FS_CATALOG . 'gm/modules/gm_gprint_order_details_cart_2.php');
				}
			}

		}

		$total_content = '';
		$total =$_SESSION['cart']->show_total();
		if ($_SESSION['customers_status']['customers_status_ot_discount_flag'] == '1' && $_SESSION['customers_status']['customers_status_ot_discount'] != '0.00') {
			if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) {
				$price = $total-$_SESSION['cart']->show_tax(false);
			} else {
				$price = $total;
			}
			// BOF GM_MOD
			$discount = round($xtPrice->xtcGetDC($price, $_SESSION['customers_status']['customers_status_ot_discount']), 2);
			$total_content = $_SESSION['customers_status']['customers_status_ot_discount'].' % '.SUB_TITLE_OT_DISCOUNT.' -'.xtc_format_price($discount, $price_special = 1, $calculate_currencies = false).'<br />';
		
			$this->set_content_data('DISCOUNT_TEXT', round((double)$_SESSION['customers_status']['customers_status_ot_discount'], 2) . '% ' . SUB_TITLE_OT_DISCOUNT);
			$this->set_content_data('DISCOUNT_VALUE', '-' . xtc_format_price($discount, $price_special = 1, $calculate_currencies = false));
		}

		if ($_SESSION['customers_status']['customers_status_show_price'] == '1') {
			if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 0) $total-=$discount;
			if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 0 && $_SESSION['customers_status']['customers_status_add_tax_ot'] == 1) $total-=$discount;
			if ($_SESSION['customers_status']['customers_status_show_price_tax'] == 1) $total-=$discount;
			$total_content .= SUB_TITLE_SUB_TOTAL.$xtPrice->xtcFormat($total, true).'<br />';
			$t_total = $xtPrice->xtcFormat($total+$discount, true);
		} else {
			$total_content .= NOT_ALLOWED_TO_SEE_PRICES.'<br />';
		}
		// display only if there is an ot_discount
		if ($customer_status_value['customers_status_ot_discount'] != 0) {
			$total_content .= TEXT_CART_OT_DISCOUNT.$customer_status_value['customers_status_ot_discount'].'%';
		}
		if (SHOW_SHIPPING == 'true') {
			$this->set_content_data('SHIPPING_INFO', ' '.SHIPPING_EXCL.'<a href="' . $coo_main->gm_get_shipping_link(true) . '" target="_blank" class="lightbox_iframe"> '.SHIPPING_COSTS.'</a>');
		}

		if ($_SESSION['customers_status']['customers_status_show_price'] == '1')
		{
			$gm_cart_tax_info = '';

			if(gm_get_conf('TAX_INFO_TAX_FREE') == 'true')
			{
				$gm_cart_tax_info = GM_TAX_FREE .'<br />';
				$this->set_content_data('TAX_FREE_TEXT', GM_TAX_FREE);
			}
			else
			{
				$gm_cart_tax_info = $_SESSION['cart']->show_tax();

				if(!empty($gm_cart_tax_info) && $_SESSION['customers_status']['customers_status_show_price_tax'] == '0' && $_SESSION['customers_status']['customers_status_add_tax_ot'] == '1')
				{
					if(!defined(MODULE_ORDER_TOTAL_SUBTOTAL_TITLE_NO_TAX))
					{
						include_once(DIR_FS_CATALOG . 'lang/' . $_SESSION['language'] . '/modules/order_total/ot_subtotal.php');
					}

					$t_gm_tax = 0;

					foreach($_SESSION['cart']->tax AS $t_gm_key => $t_gm_value)
					{
						$t_gm_tax += $t_gm_value['value'];
					}

					$gm_cart_tax_info = MODULE_ORDER_TOTAL_SUBTOTAL_TITLE_NO_TAX . ': ' . $xtPrice->xtcFormat((double)$total-(double)$t_gm_tax, true) . '<br />' . $gm_cart_tax_info;
				
					$t_total = $xtPrice->xtcFormat((double)$total-(double)$t_gm_tax+$discount, true);
				}
			}

			$this->set_content_data('UST_CONTENT', $gm_cart_tax_info, 1);
		}


		$t_taxes_data_array = explode('<br />', $_SESSION['cart']->show_tax(true));
		$t_tax_array = array();
		for($i = 0; $i < count($t_taxes_data_array); $i++)
		{
			if(!empty($t_taxes_data_array[$i]))
			{
				$t_tax_data_array = explode(':', $t_taxes_data_array[$i]);
				$t_tax_array[] = array('TEXT' => $t_tax_data_array[0], 'VALUE' => $t_tax_data_array[1]);
			}
		}
		$this->set_content_data('tax_data', $t_tax_array);
		
		$this->set_content_data('SUBTOTAL', $t_total);
		$this->set_content_data('TOTAL', $xtPrice->xtcFormat($total, true));

		
		$this->set_content_data('TOTAL_CONTENT', $total_content, 1);
		$this->set_content_data('language', $_SESSION['language']);
		$this->set_content_data('module_content', $module_content);

		$coo_gift_cart = MainFactory::create_object('GiftCartContentView');
		$t_view_html = $coo_gift_cart->get_html();
		$this->set_content_data('MODULE_gift_cart', $t_view_html);

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
               $btcPrice = number_format($total / MODULE_PAYMENT_BITCOIN_BTCEUR * $multiplier, $digits, '.', '');
               $this->set_content_data('BITCOIN_PRICE', $btcPrice . ' ' . MODULE_PAYMENT_BITCOIN_UNITS);
       }
       // Bitcoin Payment - Commerce Coding - END

		$t_html_output = $this->build_html();

		return $t_html_output;
	}
}
?>