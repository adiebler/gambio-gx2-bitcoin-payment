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

chdir('../../');
require_once('includes/application_top.php');
$query = xtc_db_query("SELECT directory FROM " . TABLE_LANGUAGES . " WHERE code = '" . DEFAULT_LANGUAGE . "'");
$result = xtc_db_fetch_array($query);
require_once(DIR_WS_LANGUAGES . $result['directory'] . '/modules/payment/bitcoin.php');

$uniqid = filter_var($_GET['uniqid'], FILTER_SANITIZE_STRING);
$amount = filter_var($_GET['value'], FILTER_SANITIZE_NUMBER_INT);
$confirms = filter_var($_GET['confirmations'], FILTER_SANITIZE_NUMBER_INT);

if ($confirms < MODULE_PAYMENT_BITCOIN_API_CONFIRMS) {
    echo "*failed*";
} else {
    $query = xtc_db_query("SELECT orders_id FROM " . TABLE_ORDERS . "
                           WHERE bitcoin_uniqid = '" . $uniqid . "'
                             AND bitcoin_amount = '" . $amount . "'
                           LIMIT 1");
    $order = xtc_db_fetch_array($query);

    if(!$order) {
        echo "*failed*";
    } else {
        xtc_db_query("UPDATE " . TABLE_ORDERS . "
                      SET orders_status = '" . MODULE_PAYMENT_BITCOIN_PAID_STATUS . "'
                      WHERE orders_id = '" . $order['orders_id'] . "'");

        xtc_db_query("INSERT INTO " . TABLE_ORDERS_STATUS_HISTORY . "
                      (orders_id, orders_status_id, date_added, customer_notified, comments)
                      VALUES
                      ('" . $order['orders_id'] . "', '" . MODULE_PAYMENT_BITCOIN_PAID_STATUS . "',
                      now(), 1, '" . MODULE_PAYMENT_BITCOIN_PAID_COMMENT . "')");

        echo "*ok*";
    }
}