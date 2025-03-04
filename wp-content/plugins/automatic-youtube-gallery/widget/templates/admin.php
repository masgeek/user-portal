<?php

/**
 * Widget admin form.
 *
 * @link    https://plugins360.com
 * @since   2.1.0
 *
 * @package Automatic_YouTube_Gallery
 */
?>

<div class="ayg-widget-field ayg-widget-field-title">
	<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'automatic-youtube-gallery' ); ?></label> 
	<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" class="widefat" value="<?php echo esc_attr( $instance['title'] ); ?>" />
</div>

<div class="ayg-editor ayg-editor-field-type-<?php echo esc_attr( $instance['type'] ); ?> ayg-editor-field-theme-<?php echo esc_attr( $instance['theme'] ); ?> ayg-editor-field-pagination_type-<?php echo esc_attr( $instance['pagination_type'] ); ?>">
	<?php foreach ( $fields as $key => $value ) : ?>	
		<div class="ayg-editor-section ayg-editor-section-<?php echo esc_attr( $key ); ?> <?php if ( 'source' == $key ) echo 'ayg-active'; ?>">
			<div class="ayg-editor-section-header">            
				<span class="dashicons-before dashicons-plus"></span>
				<span class="dashicons-before dashicons-minus"></span>
				<?php echo esc_html( $value['label'] ); ?>
			</div>

			<div class="ayg-editor-controls" <?php if ( 'source' != $key ) echo 'style="display: none;"'; ?>>
				<?php
				foreach ( $value['fields'] as $field ) :					
					if ( ! isset( $field['placeholder'] ) ) {
						$field['placeholder'] = '';
					}

					$field['value'] = $instance[ $field['name'] ];
					?>
					<div class="ayg-editor-control ayg-editor-control-<?php echo esc_attr( $field['name'] ); ?>">								
						<?php if ( 'text' == $field['type'] || 'url' == $field['type'] || 'number' == $field['type'] ) : ?>
							<label><?php echo esc_html( $field['label'] ); ?></label>
							<input type="text" name="<?php echo esc_attr( $this->get_field_name( $field['name'] ) ); ?>" class="ayg-editor-field ayg-editor-field-<?php echo esc_attr( $field['name'] ); ?> widefat" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" value="<?php echo esc_attr( $field['value'] ); ?>" />
						<?php elseif ( 'textarea' == $field['type'] ) : ?>						
							<label><?php echo esc_html( $field['label'] ); ?></label>
							<textarea name="<?php echo esc_attr( $this->get_field_name( $field['name'] ) ); ?>" rows="8" class="ayg-editor-field ayg-editor-field-<?php echo esc_attr( $field['name'] ); ?> widefat" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>"><?php echo esc_textarea( $field['value'] ); ?></textarea>
						<?php elseif ( 'select' == $field['type'] || 'radio' == $field['type'] ) : ?>							
							<label><?php echo esc_html( $field['label'] ); ?></label> 
							<select name="<?php echo esc_attr( $this->get_field_name( $field['name'] ) ); ?>" class="ayg-editor-field ayg-editor-field-<?php echo esc_attr( $field['name'] ); ?> widefat">								<?php
								foreach ( $field['options'] as $value => $label ) {
									printf( 
										'<option value="%s"%s>%s</option>', 
										esc_attr( $value ), 
										selected( $value, $field['value'], false ), 
										esc_html( $label ) 
									);
								}
								?>
							</select>						
						<?php elseif ( 'checkbox' == $field['type'] ) : ?>						
							<label>				
								<input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( $field['name'] ) ); ?>" class="ayg-editor-field ayg-editor-field-<?php echo esc_attr( $field['name'] ); ?>" value="1" <?php checked( $field['value'] ); ?> />
								<?php echo esc_html( $field['label'] ); ?>
							</label>							
						<?php elseif ( 'color' == $field['type'] ) : ?>						
							<label><?php echo esc_html( $field['label'] ); ?></label>
							<input type="text" name="<?php echo esc_attr( $this->get_field_name( $field['name'] ) ); ?>" class="ayg-editor-field ayg-editor-field-<?php echo esc_attr( $field['name'] ); ?> ayg-color-picker widefat" value="<?php echo esc_attr( $field['value'] ); ?>" />						
						<?php endif; ?>
						
						<!-- Hint -->
						<?php if ( ! empty( $field['description'] ) ) : ?>                            
							<p class="description"><?php echo wp_kses_post( $field['description'] ); ?></p>                        
						<?php endif; ?>											
					</div>    
					<?php
				endforeach;
				?>
			</div>
		</div>		
	<?php endforeach; ?>
</div>
