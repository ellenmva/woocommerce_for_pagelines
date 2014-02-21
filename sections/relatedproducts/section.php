<?php
/*
	Section: RelatedProducts
	Author: Mike Jolley
	Author URI: http://mikejolley.com
	Description: Related Products
	Class Name: PageLinesRelatedProducts
	Workswith: product
	Version: 1.0
*/

/**
 * Related Products Section
 */
class PageLinesRelatedProducts extends PageLinesSection {
		
	/**
	 * Section template.
	 */
	function section_template() { 
		$args = array(
				'posts_per_page' => 3,
				'columns'        => 3,
				
			);
		
		 // Display 3 products in rows of 3
		?>
		<div class="post-footer">
			<div class="post-footer-pad">
			
				<?php woocommerce_related_products($args); ?>
			
				<div class="clear"></div>
			</div>
		</div><?php
		
	}

}
