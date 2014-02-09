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

// Backend Information
define('MODULE_PAYMENT_BITCOIN_TEXT_TITLE', 'Bitcoin');
define('MODULE_PAYMENT_BITCOIN_TEXT_DESCRIPTION', 'Allow your customers to pay orders with Bitcoins.');

// Configuration Titles & Descriptions
define('MODULE_PAYMENT_BITCOIN_STATUS_TITLE', 'Enable Bitcoin Module');
define('MODULE_PAYMENT_BITCOIN_STATUS_DESC', 'Would you like to accept payments via Bitcoin?');
define('MODULE_PAYMENT_BITCOIN_ALLOWED_TITLE', 'Allowed Zones');
define('MODULE_PAYMENT_BITCOIN_ALLOWED_DESC', 'Please enter the zones <b>individually</b> that should be allowed to use this module (e.g. US, UK (leave blank to allow all zones))');
define('MODULE_PAYMENT_BITCOIN_UNITS_TITLE', 'Units');
define('MODULE_PAYMENT_BITCOIN_UNITS_DESC', 'In which units should Bitcoin prices be displayed?');
define('MODULE_PAYMENT_BITCOIN_SOURCE_TITLE', 'Source for Bitcoin exchange rates');
define('MODULE_PAYMENT_BITCOIN_SOURCE_DESC', 'About which provider exchange rate information should be queried?');
define('MODULE_PAYMENT_BITCOIN_BTCEUR_TITLE', 'Euro / Bitcoin');
define('MODULE_PAYMENT_BITCOIN_BTCEUR_DESC', 'At what price Euro will be converted into Bitcoin?');
define('MODULE_PAYMENT_BITCOIN_API_ADDRESS_TITLE', 'Target Address');
define('MODULE_PAYMENT_BITCOIN_API_ADDRESS_DESC', 'All payments will be forwarded to this address.');
define('MODULE_PAYMENT_BITCOIN_API_CONFIRMS_TITLE', 'Required Confirmations');
define('MODULE_PAYMENT_BITCOIN_API_CONFIRMS_DESC', 'Minimum of needed confirmations to accept a payment.');
define('MODULE_PAYMENT_BITCOIN_API_SHARED_TITLE', 'Shared');
define('MODULE_PAYMENT_BITCOIN_API_SHARED_DESC', 'The transactions are send through a shared wallet to give you greater privacy. (Fee: 0.5%) More information: http://blockchain.info/wallet/send-shared');
define('MODULE_PAYMENT_BITCOIN_NEW_STATUS_TITLE', 'Status for unpaid orders');
define('MODULE_PAYMENT_BITCOIN_NEW_STATUS_DESC', 'Specify the status that unpaid orders should be assigned.');
define('MODULE_PAYMENT_BITCOIN_PAID_STATUS_TITLE', 'Status for paid order');
define('MODULE_PAYMENT_BITCOIN_PAID_STATUS_DESC', 'Specify the status, which paid orders should be assigned.');
define('MODULE_PAYMENT_BITCOIN_SORT_ORDER_TITLE', 'Display Sort Order');
define('MODULE_PAYMENT_BITCOIN_SORT_ORDER_DESC', 'Display sort order. The lowest value is displayed first.');

// Frontend Texts
define('MODULE_PAYMENT_BITCOIN_TEXT_FRONTEND_DESCRIPTION', 'Pay your order with Bitcoins. The amount due will be shown again in the order summary. After the completion of your order you will be shown the recipient address. The goods are shipped after receipt of payment.');
define('MODULE_PAYMENT_BITCOIN_TEXT_ERROR', 'Payment Error');
define('MODULE_PAYMENT_BITCOIN_TEXT_PAYMENT_ERROR', 'There was an error when creating a payment address. Please try again or change the method of payment.');
define('MODULE_PAYMENT_BITCOIN_NEW_COMMENT', 'Bitcoin Address: %s | Amount: %s');
define('MODULE_PAYMENT_BITCOIN_PAID_COMMENT', 'The Bitcoin amount has been received and confirmed by the specified address.');