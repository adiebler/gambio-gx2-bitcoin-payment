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

switch (MODULE_PAYMENT_BITCOIN_SOURCE) {
    case 'blockchain.info':
        $json = file_get_contents('http://blockchain.info/ticker');
        $object = json_decode($json);
        $rate = $object->USD->sell;
        break;
    case 'bitstamp.net':
        $json = file_get_contents('https://www.bitstamp.net/api/ticker/');
        $object = json_decode($json);
        $rate = $object->ask;
        break;
    case 'coinbase.com':
        $json = file_get_contents('https://coinbase.com/api/v1/currencies/exchange_rates');
        $object = json_decode($json);
        $rate = $object->btc_to_usd;
        break;
    default:
        $rate = null;
}

if($rate) {
    $json = file_get_contents('https://bitdango.com/api/currencypairs/USDEUR');
    $object = json_decode($json);
    $usdToEur = $object->ExchangeRate;
    xtc_db_query("UPDATE " . TABLE_CONFIGURATION . " SET `configuration_value` = '" . number_format($rate * $usdToEur, 5, '.', '')  . "' WHERE `configuration_key` = 'MODULE_PAYMENT_BITCOIN_BTCEUR'");
}
