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
define('MODULE_PAYMENT_BITCOIN_TEXT_DESCRIPTION', 'Erlauben Sie ihren Kunden Bestellungen mit Bitcoins zu begleichen.');

// Configuration Titles & Descriptions
define('MODULE_PAYMENT_BITCOIN_STATUS_TITLE', 'Bitcoin Modul aktivieren');
define('MODULE_PAYMENT_BITCOIN_STATUS_DESC', 'M&ouml;chten Sie Zahlungen &uuml;ber Bitcoin akzeptieren?');
define('MODULE_PAYMENT_BITCOIN_ALLOWED_TITLE', 'Erlaubte Zonen');
define('MODULE_PAYMENT_BITCOIN_ALLOWED_DESC', 'Geben Sie <b>einzeln</b> die Zonen an, welche f&uuml;r dieses Modul erlaubt sein sollen. (z.B. AT,DE (wenn leer, werden alle Zonen erlaubt))');
define('MODULE_PAYMENT_BITCOIN_UNITS_TITLE', 'Einheiten');
define('MODULE_PAYMENT_BITCOIN_UNITS_DESC', 'In welchen Einheiten sollen Bitcoin-Preise angezeigt werden?');
define('MODULE_PAYMENT_BITCOIN_SOURCE_TITLE', 'Quelle f&uuml;r Bitcoin-Wechselkurse');
define('MODULE_PAYMENT_BITCOIN_SOURCE_DESC', '&Uuml;ber welchen Anbieter sollen Kursinformationen abgefragt werden?');
define('MODULE_PAYMENT_BITCOIN_BTCEUR_TITLE', 'Euro / Bitcoin');
define('MODULE_PAYMENT_BITCOIN_BTCEUR_DESC', 'Zu welchem Kurs sollen Euro in Bitcoin umgerechnet werden?');
define('MODULE_PAYMENT_BITCOIN_API_ADDRESS_TITLE', 'Ziel-Adresse');
define('MODULE_PAYMENT_BITCOIN_API_ADDRESS_DESC', 'Alle Zahlungen werden an diese Adresse weitergeleitet.');
define('MODULE_PAYMENT_BITCOIN_API_CONFIRMS_TITLE', 'Notwendige Best&auml;tigungen');
define('MODULE_PAYMENT_BITCOIN_API_CONFIRMS_DESC', 'Minimum der ben&ouml;tigten Best&auml;tigungen, um eine Zahlung zu akzeptieren.');
define('MODULE_PAYMENT_BITCOIN_API_SHARED_TITLE', 'Shared');
define('MODULE_PAYMENT_BITCOIN_API_SHARED_DESC', 'Die Zahlungen werden durch ein geteiltes Wallet geleitet, wodurch mehr Privatsphere erm&ouml;glicht werden soll. (Geb&uuml;hr: 0,5%) Mehr Informationen: http://blockchain.info/de/wallet/send-shared');
define('MODULE_PAYMENT_BITCOIN_NEW_STATUS_TITLE', 'Status f&uuml;r unbezahlte Bestellungen');
define('MODULE_PAYMENT_BITCOIN_NEW_STATUS_DESC', 'Geben Sie den Status an, welcher unbezahlten Bestellungen zugewiesen werden soll.');
define('MODULE_PAYMENT_BITCOIN_PAID_STATUS_TITLE', 'Status f&uuml;r bezahlte Bestellungen');
define('MODULE_PAYMENT_BITCOIN_PAID_STATUS_DESC', 'Geben Sie den Status an, welcher bezahlten Bestellungen zugewiesen werden soll.');
define('MODULE_PAYMENT_BITCOIN_SORT_ORDER_TITLE', 'Anzeigereihenfolge');
define('MODULE_PAYMENT_BITCOIN_SORT_ORDER_DESC', 'Reihenfolge der Anzeige. Kleinste Ziffer wird zuerst angezeigt.');

// Frontend Texts
define('MODULE_PAYMENT_BITCOIN_TEXT_FRONTEND_DESCRIPTION', 'Bezahlen Sie Ihre Bestellung mit Bitcoins. Der f&auml;llige Betrag wird in der Bestell&uuml;bersicht wieder angezeigt. Nach Abschluss Ihrer Bestellung erhalten Sie die Empf&auml;ngeradresse angezeigt. Die Waren werden nach Zahlungseingang versandt.');
define('MODULE_PAYMENT_BITCOIN_TEXT_ERROR', 'Zahlungsfehler');
define('MODULE_PAYMENT_BITCOIN_TEXT_PAYMENT_ERROR', 'Es gab einen Fehler bei der Erstellung einer Zahlungsadresse. Bitte versuchen Sie es erneut oder wechseln Sie die Zahlungsweise.');
define('MODULE_PAYMENT_BITCOIN_NEW_COMMENT', 'Bitcoin-Adresse: %s | Betrag: %s');
define('MODULE_PAYMENT_BITCOIN_PAID_COMMENT', 'Der Bitcoinbetrag wurde von der angegebenen Adresse empfangen und verifiziert.');