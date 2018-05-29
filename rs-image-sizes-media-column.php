<?php
/*
 * Plugin Name: RS Image Sizes Media Column
 * Plugin URI: http://radleysustaire.com/
 * Description: Adds a column to the Media screen which shows the media URL and all generated sizes for every image.
 * Version: 1.0.0
 * Author: Radley Sustaire
 * Author URI: http://radleysustaire.com/
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 *
 * Requires at least: 3.2
 * Tested up to: 4.9.6
 */

if ( !defined( 'ABSPATH' ) ) exit; // Do not allow direct access

// Adds a media URL column to the Media post type in list view.
function rs_ismc_media_admin_column( $cols ) {
	// Insert [media_url] into array in the 3rd position.
	$cols =
		array_slice( $cols, 0, 3, true ) +
		array( 'media_url' => 'Media URL' ) +
		array_slice( $cols, 3, count( $cols ) - 1, true );
	
	return $cols;
}

add_filter( 'manage_media_columns', 'rs_ismc_media_admin_column' );

// Display the URL and thumbnail sizes for each media item in the media screen, list view.
function rs_ismc_media_admin_value( $column_name, $id ) {
	if ( $column_name != 'media_url' ) return;
	
	$meta = wp_get_attachment_metadata( $id );
	$url = wp_get_attachment_url( wp_get_attachment_url( $id ) );
	
	if ( $url ) {
		?>
		<p>
			<input type="text" onfocus="var $me=this;setTimeout(function(){$me.select();},60);" readonly="readonly" value="<?php echo esc_attr( $url ); ?>" class="code" style="width: 100%; box-sizing: border-box; direction: rtl;">
		</p>
		<?php
	}
	
	if ( $meta ) {
		echo '<p class="description">';
		
		if ( $meta['width'] && $meta['height'] ) {
			echo sprintf( 'Original Size: %s&times;%s', $meta['width'], $meta['height'] );
		}
		
		if ( $meta['sizes'] ) {
			
			$size_array = array();
			
			foreach( $meta['sizes'] as $size => $size_meta ) {
				// Warns about undefined index "full", silence with an @ symbol.
				$src = wp_get_attachment_image_src( $id, $size );
				$sized_url = $src ? $src[0] : false;
				if ( !$sized_url || $sized_url == $url ) continue;
				
				$size_array[] = sprintf(
					'<a href="%s" target="_blank" title="Image resolution: %sx%s">%s</a>',
					esc_attr( $sized_url ),
					$size_meta['width'],
					$size_meta['height'],
					esc_html( $size )
				);
			}
			
			if ( $size_array && $meta['width'] && $meta['height'] ) echo "<br/>";
			
			if ( $size_array ) echo "Sizes: " . implode( ', ', $size_array );
		}
		
		echo '</p>';
	}
}
add_action( 'manage_media_custom_column', 'rs_ismc_media_admin_value', 10, 2 );