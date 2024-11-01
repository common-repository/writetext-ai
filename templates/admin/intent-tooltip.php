<?php
/**
 * Intent tooltip template
 *
 * @package WriteText
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<p>
	<strong><?php echo wp_kses_post( __( 'Intent guide:', 'writetext-ai' ) ); ?></strong><br>
	<?php echo wp_kses_post( __( '<i>Navigational</i> - Users have a specific website or web page in mind and are using search engines to navigate there directly.', 'writetext-ai' ) ); ?><br>
	<?php echo wp_kses_post( __( '<i>Informational</i> - Users seek information on a specific topic, intending to learn something.', 'writetext-ai' ) ); ?><br>
	<?php echo wp_kses_post( __( '<i>Transactional</i> - Users aim to complete a transaction, such as making a purchase or signing up for a service.', 'writetext-ai' ) ); ?><br>
	<?php echo wp_kses_post( __( '<i>Commercial</i> - Users are researching products or services with the intention of making a purchase decision soon.', 'writetext-ai' ) ); ?><br>
</p>
