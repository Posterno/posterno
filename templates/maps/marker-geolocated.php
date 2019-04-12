<?php
/**
 * The template for displaying the maps marker.
 *
 * This template can be overridden by copying it to yourtheme/posterno/maps/marker-geolocated.php
 *
 * HOWEVER, on occasion PNO will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @version 1.0.0
 * @package posterno
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<div class="pno-map-marker geolocated-marker">
	<svg height="16px" viewBox="0 0 16 16" width="16px" xmlns="http://www.w3.org/2000/svg" xmlns:sketch="http://www.bohemiancoding.com/sketch/ns" xmlns:xlink="http://www.w3.org/1999/xlink"><title/><defs/><g fill="none" fill-rule="evenodd" id="Icons with numbers" stroke="none" stroke-width="1"><g fill="#ef3652" id="Group" transform="translate(-240.000000, -192.000000)"><path d="M247,201 L249,201 L249,207 L248,208 L247,207 Z M248,200 C245.790861,200 244,198.209139 244,196 C244,193.790861 245.790861,192 248,192 C250.209139,192 252,193.790861 252,196 C252,198.209139 250.209139,200 248,200 Z M247,196 C247.552285,196 248,195.552285 248,195 C248,194.447715 247.552285,194 247,194 C246.447715,194 246,194.447715 246,195 C246,195.552285 246.447715,196 247,196 Z M247,196" id="Rectangle 160"/></g></g></svg>
	<span class="pno-map-marker__shadow"></span>
</div>
