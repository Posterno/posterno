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
						'AED' => __( 'United Arab Emirates dirham', 'pno' ),
						'AFN' => __( 'Afghan afghani', 'pno' ),
						'ALL' => __( 'Albanian lek', 'pno' ),
						'AMD' => __( 'Armenian dram', 'pno' ),
						'ANG' => __( 'Netherlands Antillean guilder', 'pno' ),
						'AOA' => __( 'Angolan kwanza', 'pno' ),
						'ARS' => __( 'Argentine peso', 'pno' ),
						'AUD' => __( 'Australian dollar', 'pno' ),
						'AWG' => __( 'Aruban florin', 'pno' ),
						'AZN' => __( 'Azerbaijani manat', 'pno' ),
						'BAM' => __( 'Bosnia and Herzegovina convertible mark', 'pno' ),
						'BBD' => __( 'Barbadian dollar', 'pno' ),
						'BDT' => __( 'Bangladeshi taka', 'pno' ),
						'BGN' => __( 'Bulgarian lev', 'pno' ),
						'BHD' => __( 'Bahraini dinar', 'pno' ),
						'BIF' => __( 'Burundian franc', 'pno' ),
						'BMD' => __( 'Bermudian dollar', 'pno' ),
						'BND' => __( 'Brunei dollar', 'pno' ),
						'BOB' => __( 'Bolivian boliviano', 'pno' ),
						'BRL' => __( 'Brazilian real', 'pno' ),
						'BSD' => __( 'Bahamian dollar', 'pno' ),
						'BTC' => __( 'Bitcoin', 'pno' ),
						'BTN' => __( 'Bhutanese ngultrum', 'pno' ),
						'BWP' => __( 'Botswana pula', 'pno' ),
						'BYR' => __( 'Belarusian ruble (old)', 'pno' ),
						'BYN' => __( 'Belarusian ruble', 'pno' ),
						'BZD' => __( 'Belize dollar', 'pno' ),
						'CAD' => __( 'Canadian dollar', 'pno' ),
						'CDF' => __( 'Congolese franc', 'pno' ),
						'CHF' => __( 'Swiss franc', 'pno' ),
						'CLP' => __( 'Chilean peso', 'pno' ),
						'CNY' => __( 'Chinese yuan', 'pno' ),
						'COP' => __( 'Colombian peso', 'pno' ),
						'CRC' => __( 'Costa Rican col&oacute;n', 'pno' ),
						'CUC' => __( 'Cuban convertible peso', 'pno' ),
						'CUP' => __( 'Cuban peso', 'pno' ),
						'CVE' => __( 'Cape Verdean escudo', 'pno' ),
						'CZK' => __( 'Czech koruna', 'pno' ),
						'DJF' => __( 'Djiboutian franc', 'pno' ),
						'DKK' => __( 'Danish krone', 'pno' ),
						'DOP' => __( 'Dominican peso', 'pno' ),
						'DZD' => __( 'Algerian dinar', 'pno' ),
						'EGP' => __( 'Egyptian pound', 'pno' ),
						'ERN' => __( 'Eritrean nakfa', 'pno' ),
						'ETB' => __( 'Ethiopian birr', 'pno' ),
						'EUR' => __( 'Euro', 'pno' ),
						'FJD' => __( 'Fijian dollar', 'pno' ),
						'FKP' => __( 'Falkland Islands pound', 'pno' ),
						'GBP' => __( 'Pound sterling', 'pno' ),
						'GEL' => __( 'Georgian lari', 'pno' ),
						'GGP' => __( 'Guernsey pound', 'pno' ),
						'GHS' => __( 'Ghana cedi', 'pno' ),
						'GIP' => __( 'Gibraltar pound', 'pno' ),
						'GMD' => __( 'Gambian dalasi', 'pno' ),
						'GNF' => __( 'Guinean franc', 'pno' ),
						'GTQ' => __( 'Guatemalan quetzal', 'pno' ),
						'GYD' => __( 'Guyanese dollar', 'pno' ),
						'HKD' => __( 'Hong Kong dollar', 'pno' ),
						'HNL' => __( 'Honduran lempira', 'pno' ),
						'HRK' => __( 'Croatian kuna', 'pno' ),
						'HTG' => __( 'Haitian gourde', 'pno' ),
						'HUF' => __( 'Hungarian forint', 'pno' ),
						'IDR' => __( 'Indonesian rupiah', 'pno' ),
						'ILS' => __( 'Israeli new shekel', 'pno' ),
						'IMP' => __( 'Manx pound', 'pno' ),
						'INR' => __( 'Indian rupee', 'pno' ),
						'IQD' => __( 'Iraqi dinar', 'pno' ),
						'IRR' => __( 'Iranian rial', 'pno' ),
						'IRT' => __( 'Iranian toman', 'pno' ),
						'ISK' => __( 'Icelandic kr&oacute;na', 'pno' ),
						'JEP' => __( 'Jersey pound', 'pno' ),
						'JMD' => __( 'Jamaican dollar', 'pno' ),
						'JOD' => __( 'Jordanian dinar', 'pno' ),
						'JPY' => __( 'Japanese yen', 'pno' ),
						'KES' => __( 'Kenyan shilling', 'pno' ),
						'KGS' => __( 'Kyrgyzstani som', 'pno' ),
						'KHR' => __( 'Cambodian riel', 'pno' ),
						'KMF' => __( 'Comorian franc', 'pno' ),
						'KPW' => __( 'North Korean won', 'pno' ),
						'KRW' => __( 'South Korean won', 'pno' ),
						'KWD' => __( 'Kuwaiti dinar', 'pno' ),
						'KYD' => __( 'Cayman Islands dollar', 'pno' ),
						'KZT' => __( 'Kazakhstani tenge', 'pno' ),
						'LAK' => __( 'Lao kip', 'pno' ),
						'LBP' => __( 'Lebanese pound', 'pno' ),
						'LKR' => __( 'Sri Lankan rupee', 'pno' ),
						'LRD' => __( 'Liberian dollar', 'pno' ),
						'LSL' => __( 'Lesotho loti', 'pno' ),
						'LYD' => __( 'Libyan dinar', 'pno' ),
						'MAD' => __( 'Moroccan dirham', 'pno' ),
						'MDL' => __( 'Moldovan leu', 'pno' ),
						'MGA' => __( 'Malagasy ariary', 'pno' ),
						'MKD' => __( 'Macedonian denar', 'pno' ),
						'MMK' => __( 'Burmese kyat', 'pno' ),
						'MNT' => __( 'Mongolian t&ouml;gr&ouml;g', 'pno' ),
						'MOP' => __( 'Macanese pataca', 'pno' ),
						'MRU' => __( 'Mauritanian ouguiya', 'pno' ),
						'MUR' => __( 'Mauritian rupee', 'pno' ),
						'MVR' => __( 'Maldivian rufiyaa', 'pno' ),
						'MWK' => __( 'Malawian kwacha', 'pno' ),
						'MXN' => __( 'Mexican peso', 'pno' ),
						'MYR' => __( 'Malaysian ringgit', 'pno' ),
						'MZN' => __( 'Mozambican metical', 'pno' ),
						'NAD' => __( 'Namibian dollar', 'pno' ),
						'NGN' => __( 'Nigerian naira', 'pno' ),
						'NIO' => __( 'Nicaraguan c&oacute;rdoba', 'pno' ),
						'NOK' => __( 'Norwegian krone', 'pno' ),
						'NPR' => __( 'Nepalese rupee', 'pno' ),
						'NZD' => __( 'New Zealand dollar', 'pno' ),
						'OMR' => __( 'Omani rial', 'pno' ),
						'PAB' => __( 'Panamanian balboa', 'pno' ),
						'PEN' => __( 'Sol', 'pno' ),
						'PGK' => __( 'Papua New Guinean kina', 'pno' ),
						'PHP' => __( 'Philippine peso', 'pno' ),
						'PKR' => __( 'Pakistani rupee', 'pno' ),
						'PLN' => __( 'Polish z&#x142;oty', 'pno' ),
						'PRB' => __( 'Transnistrian ruble', 'pno' ),
						'PYG' => __( 'Paraguayan guaran&iacute;', 'pno' ),
						'QAR' => __( 'Qatari riyal', 'pno' ),
						'RON' => __( 'Romanian leu', 'pno' ),
						'RSD' => __( 'Serbian dinar', 'pno' ),
						'RUB' => __( 'Russian ruble', 'pno' ),
						'RWF' => __( 'Rwandan franc', 'pno' ),
						'SAR' => __( 'Saudi riyal', 'pno' ),
						'SBD' => __( 'Solomon Islands dollar', 'pno' ),
						'SCR' => __( 'Seychellois rupee', 'pno' ),
						'SDG' => __( 'Sudanese pound', 'pno' ),
						'SEK' => __( 'Swedish krona', 'pno' ),
						'SGD' => __( 'Singapore dollar', 'pno' ),
						'SHP' => __( 'Saint Helena pound', 'pno' ),
						'SLL' => __( 'Sierra Leonean leone', 'pno' ),
						'SOS' => __( 'Somali shilling', 'pno' ),
						'SRD' => __( 'Surinamese dollar', 'pno' ),
						'SSP' => __( 'South Sudanese pound', 'pno' ),
						'STN' => __( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe dobra', 'pno' ),
						'SYP' => __( 'Syrian pound', 'pno' ),
						'SZL' => __( 'Swazi lilangeni', 'pno' ),
						'THB' => __( 'Thai baht', 'pno' ),
						'TJS' => __( 'Tajikistani somoni', 'pno' ),
						'TMT' => __( 'Turkmenistan manat', 'pno' ),
						'TND' => __( 'Tunisian dinar', 'pno' ),
						'TOP' => __( 'Tongan pa&#x2bb;anga', 'pno' ),
						'TRY' => __( 'Turkish lira', 'pno' ),
						'TTD' => __( 'Trinidad and Tobago dollar', 'pno' ),
						'TWD' => __( 'New Taiwan dollar', 'pno' ),
						'TZS' => __( 'Tanzanian shilling', 'pno' ),
						'UAH' => __( 'Ukrainian hryvnia', 'pno' ),
						'UGX' => __( 'Ugandan shilling', 'pno' ),
						'USD' => __( 'United States (US) dollar', 'pno' ),
						'UYU' => __( 'Uruguayan peso', 'pno' ),
						'UZS' => __( 'Uzbekistani som', 'pno' ),
						'VEF' => __( 'Venezuelan bol&iacute;var', 'pno' ),
						'VES' => __( 'Bol&iacute;var soberano', 'pno' ),
						'VND' => __( 'Vietnamese &#x111;&#x1ed3;ng', 'pno' ),
						'VUV' => __( 'Vanuatu vatu', 'pno' ),
						'WST' => __( 'Samoan t&#x101;l&#x101;', 'pno' ),
						'XAF' => __( 'Central African CFA franc', 'pno' ),
						'XCD' => __( 'East Caribbean dollar', 'pno' ),
						'XOF' => __( 'West African CFA franc', 'pno' ),
						'XPF' => __( 'CFP franc', 'pno' ),
						'YER' => __( 'Yemeni rial', 'pno' ),
						'ZAR' => __( 'South African rand', 'pno' ),
						'ZMW' => __( 'Zambian kwacha', 'pno' ),
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
