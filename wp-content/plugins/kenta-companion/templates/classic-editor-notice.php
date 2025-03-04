<div class="kcmp-notice notice notice-error is-dismissible">
    <div class="kcmp-notice-logo">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 500 500">
            <defs>
                <style>.a {
                        clip-path: url(#b);
                    }</style>
                <clipPath id="b">
                    <rect width="500" height="500"/>
                </clipPath>
            </defs>
            <g id="a" class="a">
                <path
                        d="M-919,442a222.636,222.636,0,0,1-44.539-4.49,219.894,219.894,0,0,1-41.484-12.877,221.03,221.03,0,0,1-37.54-20.376,222.592,222.592,0,0,1-32.707-26.986,222.588,222.588,0,0,1-26.986-32.707,221.021,221.021,0,0,1-20.376-37.54,219.889,219.889,0,0,1-12.877-41.484A222.623,222.623,0,0,1-1140,221a222.626,222.626,0,0,1,4.49-44.539,219.894,219.894,0,0,1,12.877-41.484,221.023,221.023,0,0,1,20.376-37.54,222.6,222.6,0,0,1,26.986-32.707,222.6,222.6,0,0,1,32.707-26.986,221.027,221.027,0,0,1,37.54-20.376A219.9,219.9,0,0,1-963.539,4.49,222.636,222.636,0,0,1-919,0a222.635,222.635,0,0,1,44.539,4.49,219.892,219.892,0,0,1,41.484,12.877,221.016,221.016,0,0,1,37.54,20.376,222.586,222.586,0,0,1,32.707,26.986,222.594,222.594,0,0,1,26.986,32.707,221.026,221.026,0,0,1,20.376,37.54,219.889,219.889,0,0,1,12.877,41.484A222.637,222.637,0,0,1-698,221a222.634,222.634,0,0,1-4.49,44.539,219.887,219.887,0,0,1-12.877,41.484,221.024,221.024,0,0,1-20.376,37.54,222.585,222.585,0,0,1-26.986,32.707,222.581,222.581,0,0,1-32.707,26.986,221.019,221.019,0,0,1-37.54,20.376,219.9,219.9,0,0,1-41.484,12.877A222.635,222.635,0,0,1-919,442Zm.815-205.737,35.9,70.056h64.828l-64.828-106.071L-820.246,136h-69.94l-55.185,64.364V136H-998V306.319h52.629V264.727l27.185-28.463Z"
                        transform="translate(1169 29)"/>
            </g>
        </svg>
    </div>

    <div class="kcmp-notice-message">
        <h1><?php esc_html_e( 'Please Deactivate Classic Editor Plugin!', 'kenta-companion' ); ?></h1>
        <p>
			<?php
			esc_html_e( 'Kenta theme is based on the Gutenberg editor, it allows you to edit everything visually, please deactivate your classic editor plugin to try it out!', 'kenta-companion' );
			?>
        </p>

        <p>
            <a id="kcmp-deactivate-classic-editor"
               href="<?php echo esc_url( add_query_arg( array( 'action' => 'kcmp_deactivate_classic_editor' ), admin_url( 'admin.php' ) ) ) ?>"
               class="button button-primary"><?php esc_html_e( 'Deactivate Now', 'kenta-companion' ); ?></a>
        </p>
    </div>
</div>