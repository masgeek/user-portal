<?php

/**
 * Dashboard: Shortcode Builder.
 *
 * @link    https://plugins360.com
 * @since   1.3.0
 *
 * @package Automatic_YouTube_Gallery
 */

$fields = ayg_get_editor_fields(); 

$pagination_type = 'more';
foreach ( $fields['gallery']['fields'] as $field ) {
    if ( 'pagination_type' == $field['name'] ) {
        $pagination_type = $field['value'];
        break;
    }
}
?>

<div id="ayg-shortcode-builder">
    <!-- Shortcode Builder -->
    <div class="ayg-left-col">
        <div class="ayg-col-content">
            <div class="ayg-editor ayg-editor-field-type-playlist ayg-editor-field-theme-classic ayg-editor-field-pagination_type-<?php echo esc_attr( $pagination_type ); ?>">              
                <?php
                foreach ( $fields as $key => $value ) : 
                    ?>
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
                                ?>
                                <div class="ayg-editor-control ayg-editor-control-<?php echo esc_attr( $field['name'] ); ?>">                                                
                                    <?php if ( 'text' == $field['type'] || 'url' == $field['type'] || 'number' == $field['type'] ) : ?>                                        
                                        <label><?php echo esc_html( $field['label'] ); ?></label>
                                        <input type="text" name="<?php echo esc_attr( $field['name'] ); ?>" class="ayg-editor-field ayg-editor-field-<?php echo esc_attr( $field['name'] ); ?> widefat" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" value="<?php echo esc_attr( $field['value'] ); ?>" data-default="<?php echo esc_attr( $field['value'] ); ?>" />
                                    <?php elseif ( 'textarea' == $field['type'] ) : ?>
                                        <label><?php echo esc_html( $field['label'] ); ?></label>
                                        <textarea name="<?php echo esc_attr( $field['name'] ); ?>" rows="8" class="ayg-editor-field ayg-editor-field-<?php echo esc_attr( $field['name'] ); ?> widefat" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" data-default="<?php echo esc_attr( $field['value'] ); ?>"><?php echo esc_textarea( $field['value'] ); ?></textarea>
                                    <?php elseif ( 'select' == $field['type'] || 'radio' == $field['type'] ) : ?>
                                        <label><?php echo esc_html( $field['label'] ); ?></label> 
                                        <select name="<?php echo esc_attr( $field['name'] ); ?>" class="ayg-editor-field ayg-editor-field-<?php echo esc_attr( $field['name'] ); ?> widefat" data-default="<?php echo esc_attr( $field['value'] ); ?>">
                                            <?php
                                            foreach ( $field['options'] as $value => $label ) {
                                                printf( '<option value="%s"%s>%s</option>', esc_attr( $value ), selected( $value, $field['value'], false ), esc_html( $label ) );
                                            }
                                            ?>
                                        </select>                                        
                                    <?php elseif ( 'checkbox' == $field['type'] ) : ?>                                        
                                        <label>				
                                            <input type="checkbox" name="<?php echo esc_attr( $field['name'] ); ?>" class="ayg-editor-field ayg-editor-field-<?php echo esc_attr( $field['name'] ); ?>" value="1" data-default="<?php echo esc_attr( $field['value'] ); ?>" <?php checked( $field['value'] ); ?> />
                                            <?php echo esc_html( $field['label'] ); ?>
                                        </label>                                            
                                    <?php elseif ( 'color' == $field['type'] ) : ?>                                        
                                        <label><?php echo esc_html( $field['label'] ); ?></label>
                                        <input type="text" name="<?php echo esc_attr( $field['name'] ); ?>" class="ayg-editor-field ayg-editor-field-<?php echo esc_attr( $field['name'] ); ?> ayg-color-picker widefat" value="<?php echo esc_attr( $field['value'] ); ?>" data-default="<?php echo esc_attr( $field['value'] ); ?>" />
                                    <?php endif; ?>
                                    
                                    <!-- Hint -->
                                    <?php if ( isset( $field['description'] ) && ! empty( $field['description'] ) ) : ?>                            
                                        <p class="description"><?php echo wp_kses_post( $field['description'] ); ?></p>                        
                                    <?php endif; ?>                                                            
                                </div>    
                                <?php
                            endforeach;
                            ?>
                        </div>

                    </div>
                    
                    <?php
                endforeach;
                ?>

                <p>            
                    <a href="#ayg-shortcode-modal" id="ayg-generate-shortcode" class="ayg-modal-button button button-primary button-hero">
                        <?php esc_attr_e( 'Generate Shortcode', 'automatic-youtube-gallery' ); ?>
                    </a>
                </p>
            </div>
        </div>
    </div>

    <!-- Notes -->
    <div class="ayg-right-col">
        <div class="ayg-col-content">
            <p class="about-description"><?php esc_html_e( '"Automatic YouTube Gallery" provides several methods to build your gallery. Choose one of the following methods best suited for you,', 'automatic-youtube-gallery' ); ?></p>
            <p><span class="dashicons dashicons-arrow-left-alt"></span> <?php esc_html_e( 'Use the shortcode builder in this page to build your gallery shortcode, then add it in your POST/PAGE.', 'automatic-youtube-gallery' ); ?></p>
            <p>2. <?php printf( __( 'Use our "Automatic YouTube Gallery" <a href="%s" target="_blank">Gutenberg block</a> to build the gallery directly in your POST/PAGE.', 'automatic-youtube-gallery' ), 'https://plugins360.com/automatic-youtube-gallery/building-youtube-gallery-using-gutenberg/' ); ?></p>
            <p>3. <?php esc_html_e( 'Use our "Automatic YouTube Gallery" widget to add the gallery in your website sidebars.', 'automatic-youtube-gallery' ); ?></p>
        </div>
    </div>
</div>

<!-- Shortcode Modal -->
<div id="ayg-shortcode-modal" class="ayg-modal mfp-hide">
    <div class="ayg-modal-body">
        <p><?php esc_html_e( 'Congrats! copy the shortcode below and paste it in your POST/PAGE where you need the gallery,', 'automatic-youtube-gallery' ); ?></p>
        <textarea id="aiovg-shortcode" class="widefat code" autofocus="autofocus" onfocus="this.select()"></textarea>
    </div>
</div>
