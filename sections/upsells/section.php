<?php
/*
	Section: Upsells
	Author: Ellen Jane Moore
	Author URI: http://pagelines.ellenjanemoore.com
	Description: Upsells
	Class Name: PageLinesUpsells
	Workswith: product
	Version: 1.0
*/

/**
 * Related Products Section
 */
class PageLinesUpsells extends PageLinesSection {
		
	/**
	 * Section template.
	 */
	function section_template() { 

		?>
		<div class="post-footer">
			<div class="post-footer-pad">
			
				<?php woocommerce_upsell_display(); ?>
			
				<div class="clear"></div>
			</div>
		</div><?php
		
	}

}
