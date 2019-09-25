<?php
/**
 * Handles all the currency related functionalities.
 *
 * Majority of the functions in here have been taken from WooCommerce.
 *
 * @package     posterno
 * @copyright   Copyright (c) 2018, Sematico LTD
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.1.0
 */

namespace PNO;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * The currency helper class.
 */
class CurrencyHelper {

	/**
	 * Get list of currencies available.
	 *
	 * @return array
	 */
	public static function get_currencies() {

		static $currencies;

		if ( ! isset( $currencies ) ) {
			$currencies = array_unique(

				/**
				 * Filter: allow developers to modify the list of currencies available within the plugin.
				 *
				 * @param array $currencies
				 * @return array
				 */
				apply_filters(
					'pno_currencies',
					array(
						'AED' => __( 'United Arab Emirates dirham', 'posterno' ),
						'AFN' => __( 'Afghan afghani', 'posterno' ),
						'ALL' => __( 'Albanian lek', 'posterno' ),
						'AMD' => __( 'Armenian dram', 'posterno' ),
						'ANG' => __( 'Netherlands Antillean guilder', 'posterno' ),
						'AOA' => __( 'Angolan kwanza', 'posterno' ),
						'ARS' => __( 'Argentine peso', 'posterno' ),
						'AUD' => __( 'Australian dollar', 'posterno' ),
						'AWG' => __( 'Aruban florin', 'posterno' ),
						'AZN' => __( 'Azerbaijani manat', 'posterno' ),
						'BAM' => __( 'Bosnia and Herzegovina convertible mark', 'posterno' ),
						'BBD' => __( 'Barbadian dollar', 'posterno' ),
						'BDT' => __( 'Bangladeshi taka', 'posterno' ),
						'BGN' => __( 'Bulgarian lev', 'posterno' ),
						'BHD' => __( 'Bahraini dinar', 'posterno' ),
						'BIF' => __( 'Burundian franc', 'posterno' ),
						'BMD' => __( 'Bermudian dollar', 'posterno' ),
						'BND' => __( 'Brunei dollar', 'posterno' ),
						'BOB' => __( 'Bolivian boliviano', 'posterno' ),
						'BRL' => __( 'Brazilian real', 'posterno' ),
						'BSD' => __( 'Bahamian dollar', 'posterno' ),
						'BTC' => __( 'Bitcoin', 'posterno' ),
						'BTN' => __( 'Bhutanese ngultrum', 'posterno' ),
						'BWP' => __( 'Botswana pula', 'posterno' ),
						'BYR' => __( 'Belarusian ruble (old)', 'posterno' ),
						'BYN' => __( 'Belarusian ruble', 'posterno' ),
						'BZD' => __( 'Belize dollar', 'posterno' ),
						'CAD' => __( 'Canadian dollar', 'posterno' ),
						'CDF' => __( 'Congolese franc', 'posterno' ),
						'CHF' => __( 'Swiss franc', 'posterno' ),
						'CLP' => __( 'Chilean peso', 'posterno' ),
						'CNY' => __( 'Chinese yuan', 'posterno' ),
						'COP' => __( 'Colombian peso', 'posterno' ),
						'CRC' => __( 'Costa Rican col&oacute;n', 'posterno' ),
						'CUC' => __( 'Cuban convertible peso', 'posterno' ),
						'CUP' => __( 'Cuban peso', 'posterno' ),
						'CVE' => __( 'Cape Verdean escudo', 'posterno' ),
						'CZK' => __( 'Czech koruna', 'posterno' ),
						'DJF' => __( 'Djiboutian franc', 'posterno' ),
						'DKK' => __( 'Danish krone', 'posterno' ),
						'DOP' => __( 'Dominican peso', 'posterno' ),
						'DZD' => __( 'Algerian dinar', 'posterno' ),
						'EGP' => __( 'Egyptian pound', 'posterno' ),
						'ERN' => __( 'Eritrean nakfa', 'posterno' ),
						'ETB' => __( 'Ethiopian birr', 'posterno' ),
						'EUR' => __( 'Euro', 'posterno' ),
						'FJD' => __( 'Fijian dollar', 'posterno' ),
						'FKP' => __( 'Falkland Islands pound', 'posterno' ),
						'GBP' => __( 'Pound sterling', 'posterno' ),
						'GEL' => __( 'Georgian lari', 'posterno' ),
						'GGP' => __( 'Guernsey pound', 'posterno' ),
						'GHS' => __( 'Ghana cedi', 'posterno' ),
						'GIP' => __( 'Gibraltar pound', 'posterno' ),
						'GMD' => __( 'Gambian dalasi', 'posterno' ),
						'GNF' => __( 'Guinean franc', 'posterno' ),
						'GTQ' => __( 'Guatemalan quetzal', 'posterno' ),
						'GYD' => __( 'Guyanese dollar', 'posterno' ),
						'HKD' => __( 'Hong Kong dollar', 'posterno' ),
						'HNL' => __( 'Honduran lempira', 'posterno' ),
						'HRK' => __( 'Croatian kuna', 'posterno' ),
						'HTG' => __( 'Haitian gourde', 'posterno' ),
						'HUF' => __( 'Hungarian forint', 'posterno' ),
						'IDR' => __( 'Indonesian rupiah', 'posterno' ),
						'ILS' => __( 'Israeli new shekel', 'posterno' ),
						'IMP' => __( 'Manx pound', 'posterno' ),
						'INR' => __( 'Indian rupee', 'posterno' ),
						'IQD' => __( 'Iraqi dinar', 'posterno' ),
						'IRR' => __( 'Iranian rial', 'posterno' ),
						'IRT' => __( 'Iranian toman', 'posterno' ),
						'ISK' => __( 'Icelandic kr&oacute;na', 'posterno' ),
						'JEP' => __( 'Jersey pound', 'posterno' ),
						'JMD' => __( 'Jamaican dollar', 'posterno' ),
						'JOD' => __( 'Jordanian dinar', 'posterno' ),
						'JPY' => __( 'Japanese yen', 'posterno' ),
						'KES' => __( 'Kenyan shilling', 'posterno' ),
						'KGS' => __( 'Kyrgyzstani som', 'posterno' ),
						'KHR' => __( 'Cambodian riel', 'posterno' ),
						'KMF' => __( 'Comorian franc', 'posterno' ),
						'KPW' => __( 'North Korean won', 'posterno' ),
						'KRW' => __( 'South Korean won', 'posterno' ),
						'KWD' => __( 'Kuwaiti dinar', 'posterno' ),
						'KYD' => __( 'Cayman Islands dollar', 'posterno' ),
						'KZT' => __( 'Kazakhstani tenge', 'posterno' ),
						'LAK' => __( 'Lao kip', 'posterno' ),
						'LBP' => __( 'Lebanese pound', 'posterno' ),
						'LKR' => __( 'Sri Lankan rupee', 'posterno' ),
						'LRD' => __( 'Liberian dollar', 'posterno' ),
						'LSL' => __( 'Lesotho loti', 'posterno' ),
						'LYD' => __( 'Libyan dinar', 'posterno' ),
						'MAD' => __( 'Moroccan dirham', 'posterno' ),
						'MDL' => __( 'Moldovan leu', 'posterno' ),
						'MGA' => __( 'Malagasy ariary', 'posterno' ),
						'MKD' => __( 'Macedonian denar', 'posterno' ),
						'MMK' => __( 'Burmese kyat', 'posterno' ),
						'MNT' => __( 'Mongolian t&ouml;gr&ouml;g', 'posterno' ),
						'MOP' => __( 'Macanese pataca', 'posterno' ),
						'MRU' => __( 'Mauritanian ouguiya', 'posterno' ),
						'MUR' => __( 'Mauritian rupee', 'posterno' ),
						'MVR' => __( 'Maldivian rufiyaa', 'posterno' ),
						'MWK' => __( 'Malawian kwacha', 'posterno' ),
						'MXN' => __( 'Mexican peso', 'posterno' ),
						'MYR' => __( 'Malaysian ringgit', 'posterno' ),
						'MZN' => __( 'Mozambican metical', 'posterno' ),
						'NAD' => __( 'Namibian dollar', 'posterno' ),
						'NGN' => __( 'Nigerian naira', 'posterno' ),
						'NIO' => __( 'Nicaraguan c&oacute;rdoba', 'posterno' ),
						'NOK' => __( 'Norwegian krone', 'posterno' ),
						'NPR' => __( 'Nepalese rupee', 'posterno' ),
						'NZD' => __( 'New Zealand dollar', 'posterno' ),
						'OMR' => __( 'Omani rial', 'posterno' ),
						'PAB' => __( 'Panamanian balboa', 'posterno' ),
						'PEN' => __( 'Sol', 'posterno' ),
						'PGK' => __( 'Papua New Guinean kina', 'posterno' ),
						'PHP' => __( 'Philippine peso', 'posterno' ),
						'PKR' => __( 'Pakistani rupee', 'posterno' ),
						'PLN' => __( 'Polish z&#x142;oty', 'posterno' ),
						'PRB' => __( 'Transnistrian ruble', 'posterno' ),
						'PYG' => __( 'Paraguayan guaran&iacute;', 'posterno' ),
						'QAR' => __( 'Qatari riyal', 'posterno' ),
						'RON' => __( 'Romanian leu', 'posterno' ),
						'RSD' => __( 'Serbian dinar', 'posterno' ),
						'RUB' => __( 'Russian ruble', 'posterno' ),
						'RWF' => __( 'Rwandan franc', 'posterno' ),
						'SAR' => __( 'Saudi riyal', 'posterno' ),
						'SBD' => __( 'Solomon Islands dollar', 'posterno' ),
						'SCR' => __( 'Seychellois rupee', 'posterno' ),
						'SDG' => __( 'Sudanese pound', 'posterno' ),
						'SEK' => __( 'Swedish krona', 'posterno' ),
						'SGD' => __( 'Singapore dollar', 'posterno' ),
						'SHP' => __( 'Saint Helena pound', 'posterno' ),
						'SLL' => __( 'Sierra Leonean leone', 'posterno' ),
						'SOS' => __( 'Somali shilling', 'posterno' ),
						'SRD' => __( 'Surinamese dollar', 'posterno' ),
						'SSP' => __( 'South Sudanese pound', 'posterno' ),
						'STN' => __( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe dobra', 'posterno' ),
						'SYP' => __( 'Syrian pound', 'posterno' ),
						'SZL' => __( 'Swazi lilangeni', 'posterno' ),
						'THB' => __( 'Thai baht', 'posterno' ),
						'TJS' => __( 'Tajikistani somoni', 'posterno' ),
						'TMT' => __( 'Turkmenistan manat', 'posterno' ),
						'TND' => __( 'Tunisian dinar', 'posterno' ),
						'TOP' => __( 'Tongan pa&#x2bb;anga', 'posterno' ),
						'TRY' => __( 'Turkish lira', 'posterno' ),
						'TTD' => __( 'Trinidad and Tobago dollar', 'posterno' ),
						'TWD' => __( 'New Taiwan dollar', 'posterno' ),
						'TZS' => __( 'Tanzanian shilling', 'posterno' ),
						'UAH' => __( 'Ukrainian hryvnia', 'posterno' ),
						'UGX' => __( 'Ugandan shilling', 'posterno' ),
						'USD' => __( 'United States (US) dollar', 'posterno' ),
						'UYU' => __( 'Uruguayan peso', 'posterno' ),
						'UZS' => __( 'Uzbekistani som', 'posterno' ),
						'VEF' => __( 'Venezuelan bol&iacute;var', 'posterno' ),
						'VES' => __( 'Bol&iacute;var soberano', 'posterno' ),
						'VND' => __( 'Vietnamese &#x111;&#x1ed3;ng', 'posterno' ),
						'VUV' => __( 'Vanuatu vatu', 'posterno' ),
						'WST' => __( 'Samoan t&#x101;l&#x101;', 'posterno' ),
						'XAF' => __( 'Central African CFA franc', 'posterno' ),
						'XCD' => __( 'East Caribbean dollar', 'posterno' ),
						'XOF' => __( 'West African CFA franc', 'posterno' ),
						'XPF' => __( 'CFP franc', 'posterno' ),
						'YER' => __( 'Yemeni rial', 'posterno' ),
						'ZAR' => __( 'South African rand', 'posterno' ),
						'ZMW' => __( 'Zambian kwacha', 'posterno' ),
					)
				)
			);
		}

		return $currencies;

	}

	/**
	 * Get the symbol of a currency.
	 *
	 * @param string $currency the currency to get.
	 * @return string
	 */
	public static function get_currency_symbol( $currency = '' ) {

		if ( ! $currency ) {
			$currency = pno_get_option( 'pricing_currency' );
		}

		$symbols         = apply_filters(
			'pno_currency_symbols',
			array(
				'AED' => '&#x62f;.&#x625;',
				'AFN' => '&#x60b;',
				'ALL' => 'L',
				'AMD' => 'AMD',
				'ANG' => '&fnof;',
				'AOA' => 'Kz',
				'ARS' => '&#36;',
				'AUD' => '&#36;',
				'AWG' => 'Afl.',
				'AZN' => 'AZN',
				'BAM' => 'KM',
				'BBD' => '&#36;',
				'BDT' => '&#2547;&nbsp;',
				'BGN' => '&#1083;&#1074;.',
				'BHD' => '.&#x62f;.&#x628;',
				'BIF' => 'Fr',
				'BMD' => '&#36;',
				'BND' => '&#36;',
				'BOB' => 'Bs.',
				'BRL' => '&#82;&#36;',
				'BSD' => '&#36;',
				'BTC' => '&#3647;',
				'BTN' => 'Nu.',
				'BWP' => 'P',
				'BYR' => 'Br',
				'BYN' => 'Br',
				'BZD' => '&#36;',
				'CAD' => '&#36;',
				'CDF' => 'Fr',
				'CHF' => '&#67;&#72;&#70;',
				'CLP' => '&#36;',
				'CNY' => '&yen;',
				'COP' => '&#36;',
				'CRC' => '&#x20a1;',
				'CUC' => '&#36;',
				'CUP' => '&#36;',
				'CVE' => '&#36;',
				'CZK' => '&#75;&#269;',
				'DJF' => 'Fr',
				'DKK' => 'DKK',
				'DOP' => 'RD&#36;',
				'DZD' => '&#x62f;.&#x62c;',
				'EGP' => 'EGP',
				'ERN' => 'Nfk',
				'ETB' => 'Br',
				'EUR' => '&euro;',
				'FJD' => '&#36;',
				'FKP' => '&pound;',
				'GBP' => '&pound;',
				'GEL' => '&#x20be;',
				'GGP' => '&pound;',
				'GHS' => '&#x20b5;',
				'GIP' => '&pound;',
				'GMD' => 'D',
				'GNF' => 'Fr',
				'GTQ' => 'Q',
				'GYD' => '&#36;',
				'HKD' => '&#36;',
				'HNL' => 'L',
				'HRK' => 'kn',
				'HTG' => 'G',
				'HUF' => '&#70;&#116;',
				'IDR' => 'Rp',
				'ILS' => '&#8362;',
				'IMP' => '&pound;',
				'INR' => '&#8377;',
				'IQD' => '&#x639;.&#x62f;',
				'IRR' => '&#xfdfc;',
				'IRT' => '&#x062A;&#x0648;&#x0645;&#x0627;&#x0646;',
				'ISK' => 'kr.',
				'JEP' => '&pound;',
				'JMD' => '&#36;',
				'JOD' => '&#x62f;.&#x627;',
				'JPY' => '&yen;',
				'KES' => 'KSh',
				'KGS' => '&#x441;&#x43e;&#x43c;',
				'KHR' => '&#x17db;',
				'KMF' => 'Fr',
				'KPW' => '&#x20a9;',
				'KRW' => '&#8361;',
				'KWD' => '&#x62f;.&#x643;',
				'KYD' => '&#36;',
				'KZT' => 'KZT',
				'LAK' => '&#8365;',
				'LBP' => '&#x644;.&#x644;',
				'LKR' => '&#xdbb;&#xdd4;',
				'LRD' => '&#36;',
				'LSL' => 'L',
				'LYD' => '&#x644;.&#x62f;',
				'MAD' => '&#x62f;.&#x645;.',
				'MDL' => 'MDL',
				'MGA' => 'Ar',
				'MKD' => '&#x434;&#x435;&#x43d;',
				'MMK' => 'Ks',
				'MNT' => '&#x20ae;',
				'MOP' => 'P',
				'MRU' => 'UM',
				'MUR' => '&#x20a8;',
				'MVR' => '.&#x783;',
				'MWK' => 'MK',
				'MXN' => '&#36;',
				'MYR' => '&#82;&#77;',
				'MZN' => 'MT',
				'NAD' => 'N&#36;',
				'NGN' => '&#8358;',
				'NIO' => 'C&#36;',
				'NOK' => '&#107;&#114;',
				'NPR' => '&#8360;',
				'NZD' => '&#36;',
				'OMR' => '&#x631;.&#x639;.',
				'PAB' => 'B/.',
				'PEN' => 'S/',
				'PGK' => 'K',
				'PHP' => '&#8369;',
				'PKR' => '&#8360;',
				'PLN' => '&#122;&#322;',
				'PRB' => '&#x440;.',
				'PYG' => '&#8370;',
				'QAR' => '&#x631;.&#x642;',
				'RMB' => '&yen;',
				'RON' => 'lei',
				'RSD' => '&#x434;&#x438;&#x43d;.',
				'RUB' => '&#8381;',
				'RWF' => 'Fr',
				'SAR' => '&#x631;.&#x633;',
				'SBD' => '&#36;',
				'SCR' => '&#x20a8;',
				'SDG' => '&#x62c;.&#x633;.',
				'SEK' => '&#107;&#114;',
				'SGD' => '&#36;',
				'SHP' => '&pound;',
				'SLL' => 'Le',
				'SOS' => 'Sh',
				'SRD' => '&#36;',
				'SSP' => '&pound;',
				'STN' => 'Db',
				'SYP' => '&#x644;.&#x633;',
				'SZL' => 'L',
				'THB' => '&#3647;',
				'TJS' => '&#x405;&#x41c;',
				'TMT' => 'm',
				'TND' => '&#x62f;.&#x62a;',
				'TOP' => 'T&#36;',
				'TRY' => '&#8378;',
				'TTD' => '&#36;',
				'TWD' => '&#78;&#84;&#36;',
				'TZS' => 'Sh',
				'UAH' => '&#8372;',
				'UGX' => 'UGX',
				'USD' => '&#36;',
				'UYU' => '&#36;',
				'UZS' => 'UZS',
				'VEF' => 'Bs F',
				'VES' => 'Bs.S',
				'VND' => '&#8363;',
				'VUV' => 'Vt',
				'WST' => 'T',
				'XAF' => 'CFA',
				'XCD' => '&#36;',
				'XOF' => 'CFA',
				'XPF' => 'Fr',
				'YER' => '&#xfdfc;',
				'ZAR' => '&#82;',
				'ZMW' => 'ZK',
			)
		);
		$currency_symbol = isset( $symbols[ $currency ] ) ? $symbols[ $currency ] : '';

		/**
		 * Filter: allow developers to customize the symbols for currencies.
		 *
		 * @param string $currency_symbol the symbol found.
		 * @param string $currency the ID of the currency.
		 * @return string
		 */
		return apply_filters( 'pno_currency_symbol', $currency_symbol, $currency );

	}

	/**
	 * Get the price format depending on the currency position.
	 *
	 * @return string
	 */
	public static function get_price_format() {

		$currency_pos = pno_get_option( 'pricing_currency_position', 'left' );
		$format       = '%1$s%2$s';

		switch ( $currency_pos ) {
			case 'left':
				$format = '%1$s%2$s';
				break;
			case 'right':
				$format = '%2$s%1$s';
				break;
			case 'left_space':
				$format = '%1$s&nbsp;%2$s';
				break;
			case 'right_space':
				$format = '%2$s&nbsp;%1$s';
				break;
		}

		/**
		 * Filter: adjust the currency position.
		 *
		 * @param string $format formatted.
		 * @param string $currency_pos the position selected in the admin panel.
		 * @return string
		 */
		return apply_filters( 'pno_price_format', $format, $currency_pos );

	}

	/**
	 * Get the thousands separator.
	 *
	 * @return string
	 */
	public static function get_thousands_separator() {
		return stripslashes( pno_get_option( 'pricing_thousand_separator', ',' ) );
	}

	/**
	 * Get the decimal separator.
	 *
	 * @return string
	 */
	public static function get_decimal_separator() {
		$separator = pno_get_option( 'pricing_decimal_separator', '.' );

		return $separator ? stripslashes( $separator ) : '.';
	}

	/**
	 * Get the decimals for the pricing.
	 *
	 * @return int
	 */
	public static function get_decimals() {
		return absint( pno_get_option( 'pricing_decimals_number', '2' ) );
	}

	/**
	 * Trim trailing zeros off prices.
	 *
	 * @param string $price price to trim.
	 * @return string
	 */
	public static function trim_zeros( $price ) {
		return preg_replace( '/' . preg_quote( self::get_decimal_separator(), '/' ) . '0++$/', '', $price );
	}

	/**
	 * Format a price.
	 *
	 * @param string $price the price to format.
	 * @param array  $args arguments for the format.
	 * @return string
	 */
	public static function price( $price, $args = [] ) {

		$args = apply_filters(
			'pno_price_args',
			wp_parse_args(
				$args,
				array(
					'ex_tax_label'       => false,
					'currency'           => '',
					'decimal_separator'  => self::get_decimal_separator(),
					'thousand_separator' => self::get_thousands_separator(),
					'decimals'           => self::get_decimals(),
					'price_format'       => self::get_price_format(),
				)
			)
		);

		$unformatted_price = $price;
		$negative          = $price < 0;
		$price             = apply_filters( 'raw_pno_price', floatval( $negative ? $price * -1 : $price ) );
		$price             = apply_filters( 'formatted_pno_price', number_format( $price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator'] ), $price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator'] );

		if ( apply_filters( 'pno_price_trim_zeros', false ) && $args['decimals'] > 0 ) {
			$price = self::trim_zeros( $price );
		}

		$formatted_price = ( $negative ? '-' : '' ) . sprintf( $args['price_format'], '<span class="pno-Price-currencySymbol">' . self::get_currency_symbol( $args['currency'] ) . '</span>', $price );
		$return          = '<span class="pno-Price-amount amount">' . $formatted_price . '</span>';

		/**
		 * Filters the string of price markup.
		 *
		 * @param string $return            Price HTML markup.
		 * @param string $price             Formatted price.
		 * @param array  $args              Pass on the args.
		 * @param float  $unformatted_price Price as float to allow plugins custom formatting. Since 3.2.0.
		 */
		return apply_filters( 'pno_price', $return, $price, $args, $unformatted_price );

	}

}
