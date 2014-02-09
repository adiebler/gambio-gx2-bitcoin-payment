<?php
/**
 * CommerceCoding Bitcoin Payment for Gambio GX2
 *
 * NOTICE OF LICENSE
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; version 2 of the License
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @copyright   Copyright (c) 2013 CommerceCoding (http://www.commerce-coding.de)
 * @author      Alexander Diebler
 * @license     http://opensource.org/licenses/GPL-2.0  GNU General Public License, version 2 (GPL-2.0)
 */

class bitcoin
{
    /**
     * Constructor class, sets the settings.
     */
    function bitcoin()
    {
        $this->code = 'bitcoin';
        $this->version = '0.1.0';
        $this->title = MODULE_PAYMENT_BITCOIN_TEXT_TITLE;
        $this->description = MODULE_PAYMENT_BITCOIN_TEXT_DESCRIPTION;
        $this->sort_order = MODULE_PAYMENT_BITCOIN_SORT_ORDER;
        $this->enabled = (MODULE_PAYMENT_BITCOIN_STATUS == 'True') ? true : false;
    }

    /**
     * Settings update. Not used in this module.
     *
     * @return boolean
     */
    function update_status()
    {
        return false;
    }

    /**
     * Javascript code. Not used in this module.
     *
     * @return boolean
     */
    function javascript_validation()
    {
        return false;
    }

    /**
     * Sets information for checkout payment selection page.
     *
     * @return array
     */
    function selection()
    {
        $title = $this->title;
        $description = MODULE_PAYMENT_BITCOIN_TEXT_FRONTEND_DESCRIPTION;

        return array('id' => $this->code, 'module' => $title, 'description' => $description);
    }

    /**
     * Actions before confirmation. Not used in this module.
     *
     * @return boolean
     */
    function pre_confirmation_check()
    {
        return false;
    }

    /**
     * Payment method confirmation. Not used in this module.
     *
     * @return boolean
     */
    function confirmation()
    {
        return false;
    }

    /**
     * Module start via button. Not used in this module.
     *
     * @return boolean
     */
    function process_button()
    {
        return false;
    }

    /**
     * Before process.
     *
     * @return boolean
     */
    function before_process()
    {
        $transaction = $this->getBitcoinOrderAddress();

        if(is_array($transaction)) {
            $_SESSION['bitcoin_address'] = $transaction['address'];
            $_SESSION['bitcoin_uniqid'] = $transaction['uniqid'];
        } else {
            xtc_redirect(xtc_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=bitcoin&' . session_name() . '=' . session_id(), 'SSL'));
        }
    }

    /**
     * After process.
     */
    function after_process()
    {
        global $order, $insert_id;

        $totals = $order->totals;
        $total = end($totals);

        $price = $total['value'] / MODULE_PAYMENT_BITCOIN_BTCEUR;
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
                $btcPrice = number_format($price * $multiplier, $digits, '.', '');
        }
        $_SESSION['bitcoin_amount'] = $btcPrice . ' ' . MODULE_PAYMENT_BITCOIN_UNITS;

        $query = xtc_db_query("SELECT orders_status_history_id, comments FROM " . TABLE_ORDERS_STATUS_HISTORY . "
                               WHERE orders_id = '" . $insert_id . "'
                               ORDER BY orders_status_history_id DESC");
        $last = xtc_db_fetch_array($query);

        xtc_db_query("UPDATE " . TABLE_ORDERS . "
                      SET bitcoin_address = '" . $_SESSION['bitcoin_address'] . "' ,
                          bitcoin_amount = '" .  round($price * 100000000) . "',
                          bitcoin_uniqid = '" .  $_SESSION['bitcoin_uniqid'] . "',
                          orders_status = '" . MODULE_PAYMENT_BITCOIN_NEW_STATUS . "'
                      WHERE orders_id = '" . $insert_id . "'");

        xtc_db_query("UPDATE " . TABLE_ORDERS_STATUS_HISTORY . "
                      SET orders_status_id = '" . MODULE_PAYMENT_BITCOIN_NEW_STATUS . "',
                          comments = '" . sprintf(MODULE_PAYMENT_BITCOIN_NEW_COMMENT, $_SESSION['bitcoin_address'], $_SESSION['bitcoin_amount']) . "'
                      WHERE orders_status_history_id = '" . $last['orders_status_history_id'] . "'");
    }

    /**
     * Extracts and returns error.
     */
    function get_error()
    {
        $error = false;
        if (isset($_GET['payment_error']) && $_GET['payment_error'] == 'bitcoin') {
            $error = array('title' => MODULE_PAYMENT_BITCOIN_TEXT_ERROR, 'error' => MODULE_PAYMENT_BITCOIN_TEXT_PAYMENT_ERROR);
        }

        return $error;
    }

    /**
     * Error output. Not used in this module.
     *
     * @return boolean
     */
    function output_error()
    {
        return false;
    }

    /**
     * Checks if Bitcoin payment module is installed.
     *
     * @return integer
     */
    function check()
    {
        if (!isset($this->_check)) {
            $check_query = xtc_db_query("SELECT configuration_value FROM " . TABLE_CONFIGURATION . "
                                   WHERE configuration_key = 'MODULE_PAYMENT_BITCOIN_STATUS'");
            $this->_check = xtc_db_num_rows($check_query);
        }
        return $this->_check;
    }

    /**
     * Install sql queries.
     */
    function install()
    {
        // settings installation
        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, date_added)
            VALUES
            ('MODULE_PAYMENT_BITCOIN_STATUS', 'False', '6', '1', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now()),
            ('MODULE_PAYMENT_BITCOIN_SOURCE', 'none', '6', '4', 'xtc_cfg_select_option(array(\'none\', \'blockchain.info\', \'bitstamp.net\', \'coinbase.com\'), ', now()),
            ('MODULE_PAYMENT_BITCOIN_UNITS', 'BTC', '6', '3', 'xtc_cfg_select_option(array(\'BTC\', \'mBTC\', \'uBTC\'), ', now()),
            ('MODULE_PAYMENT_BITCOIN_API_SHARED', 'False', '6', '8', 'xtc_cfg_select_option(array(\'True\', \'False\'), ', now())");

        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, date_added)
            VALUES
            ('MODULE_PAYMENT_BITCOIN_ALLOWED', '', '6', '2', now()),
            ('MODULE_PAYMENT_BITCOIN_BTCEUR', '', '6', '5', now()),
            ('MODULE_PAYMENT_BITCOIN_API_ADDRESS', '', '6', '6', now()),
            ('MODULE_PAYMENT_BITCOIN_API_CONFIRMS', '', '6', '7', now()),
            ('MODULE_PAYMENT_BITCOIN_SORT_ORDER', '0', '6', '11', now())");

        xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . "
            (configuration_key, configuration_value, configuration_group_id, sort_order, set_function, use_function, date_added)
            VALUES
            ('MODULE_PAYMENT_BITCOIN_NEW_STATUS', '0', '6', '9', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now()),
            ('MODULE_PAYMENT_BITCOIN_PAID_STATUS', '0', '6', '10', 'xtc_cfg_pull_down_order_statuses(', 'xtc_get_order_status_name', now())");

        // expand order table with bitcoin uniqid, address and amount fields
        $query1 = xtc_db_query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS
                                WHERE table_name = '" . TABLE_ORDERS . "'
                                AND table_schema = '" . DB_DATABASE . "'
                                AND column_name = 'bitcoin_uniqid'");

        if (xtc_db_num_rows($query1) == 0) {
            xtc_db_query("ALTER TABLE `" . TABLE_ORDERS . "` ADD `bitcoin_uniqid` varchar(30) NOT NULL default '';");
        }

        $query2 = xtc_db_query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS
                                WHERE table_name = '" . TABLE_ORDERS . "'
                                AND table_schema = '" . DB_DATABASE . "'
                                AND column_name = 'bitcoin_address'");

        if (xtc_db_num_rows($query2) == 0) {
            xtc_db_query("ALTER TABLE `" . TABLE_ORDERS . "` ADD `bitcoin_address` varchar(40) NOT NULL default '';");
        }

        $query3 = xtc_db_query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS
                                WHERE table_name = '" . TABLE_ORDERS . "'
                                AND table_schema = '" . DB_DATABASE . "'
                                AND column_name = 'bitcoin_amount'");

        if (xtc_db_num_rows($query3) == 0) {
            xtc_db_query("ALTER TABLE `" . TABLE_ORDERS . "` ADD `bitcoin_amount` bigint(16) NOT NULL default '0';");
        }
    }

    /**
     * Uninstall sql queries.
     */
    function remove()
    {
        $parameters = $this->keys();
        xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('" . implode("', '", $parameters) . "')");
    }

    /**
     * All necessary configuration attributes for the payment module.
     *
     * @return array with configuration attributes
     */
    function keys()
    {
        return array('MODULE_PAYMENT_BITCOIN_STATUS',
            'MODULE_PAYMENT_BITCOIN_ALLOWED',
            'MODULE_PAYMENT_BITCOIN_UNITS',
            'MODULE_PAYMENT_BITCOIN_SOURCE',
            'MODULE_PAYMENT_BITCOIN_BTCEUR',
            'MODULE_PAYMENT_BITCOIN_API_ADDRESS',
            'MODULE_PAYMENT_BITCOIN_API_CONFIRMS',
            'MODULE_PAYMENT_BITCOIN_API_SHARED',
            'MODULE_PAYMENT_BITCOIN_NEW_STATUS',
            'MODULE_PAYMENT_BITCOIN_PAID_STATUS',
            'MODULE_PAYMENT_BITCOIN_SORT_ORDER');
    }

    function getBitcoinOrderAddress()
    {
        // prepare url
        $uniqid = str_replace('.', '', rand(0,1000) . uniqid('', true));
        $url = 'https://blockchain.info/api/receive?method=create';
        $url .= '&address=' . MODULE_PAYMENT_BITCOIN_API_ADDRESS;
        $url .= MODULE_PAYMENT_BITCOIN_API_SHARED == 'True' ? '&shared=true' : '&shared=false';
        $url .= '&callback=' . urlencode(xtc_href_link('callback/bitcoin/callback.php', 'uniqid=' . $uniqid, 'SSL'));
        
        // get bitcoin address for current order
        $json = @file_get_contents($url);
        $object = json_decode($json);
        $address = $object->input_address;

        // check received address
        if((string) file_get_contents('http://blockexplorer.com/q/checkaddress/' . $address) == '00') {
            return array('address' => $address, 'uniqid' => $uniqid);
        } else {
            return null;
        }
    }
}
