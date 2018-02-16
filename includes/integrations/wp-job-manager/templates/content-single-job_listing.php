<?php global $post;

    if ( ! class_exists( 'WP_Job_Manager' ) ) {
        return;
    }

    $listing = new CASE27\Classes\Listing( $post );

    if ( ! $listing->type ) {
        return;
    }

    // Get the preview template options for the listing type of the current listing.
    $options = c27()->get_listing_type_options(
                    c27()->get_job_listing_type(get_the_ID()),
                    ['single', 'fields']
                );

    // Get the layout blocks for the single listing page.
    $layout = $options['single'];
    $fields = $options['fields'];

    // Possible cover button styles.
    $button_styles = [
        'primary' => 'button-primary',
        'secondary' => 'button-secondary',
        'outline' => 'button-outlined',
        'plain' => 'button-plain',
        'none' => 'button-plain',
    ];

    $listing_logo = job_manager_get_resized_image( $listing->get_field( 'job_logo' ), 'medium' );
?>

<!-- SINGLE LISTING PAGE -->
<div class="single-job-listing <?php echo ! $listing_logo ? 'listing-no-logo' : '' ?>" id="c27-single-listing">
    <input type="hidden" id="case27-post-id" value="<?php echo esc_attr( get_the_ID() ) ?>">
    <input type="hidden" id="case27-author-id" value="<?php echo esc_attr( get_the_author_meta('ID') ) ?>">

    <!-- LISTING COVER IMAGE -->
    <?php if ($layout['cover']['type'] == 'image'): ?>
        <?php if ( $image = job_manager_get_resized_image( $listing->get_field( 'job_cover' ) , 'full' ) ): ?>
            <section class="featured-section profile-cover parallax-bg"
                     style="background-image: url('<?php echo esc_url( $image ) ?>')"
                     data-bg="<?php echo esc_url( $image ) ?>">
                <div class="overlay" style="
                    background-color: <?php echo esc_attr( c27()->get_setting('single_listing_cover_overlay_color', '#242429') ); ?>;
                    opacity: <?php echo esc_attr( c27()->get_setting('single_listing_cover_overlay_opacity', '0.5') ); ?>;
                    "></div>
        <?php else: ?>
            <section class="featured-section profile-cover profile-cover-no-img">
            <div class="overlay" style="
                background-color: <?php echo esc_attr( c27()->get_setting('single_listing_cover_overlay_color', '#242429') ); ?>;
                opacity: <?php echo esc_attr( c27()->get_setting('single_listing_cover_overlay_opacity', '0.5') ); ?>;
                "></div>
        <?php endif ?>
    <?php endif ?>

    <!-- LISTING COVER GALLERY -->
    <?php if ($layout['cover']['type'] == 'gallery'): ?>
        <?php if ( $gallery = $listing->get_field( 'job_gallery' ) ): ?>
        <section class="featured-section profile-cover featured-section-gallery">
            <div class="header-gallery-carousel owl-carousel zoom-gallery">
                <?php foreach ($gallery as $gallery_image): ?>
                	<?php if ($image = job_manager_get_resized_image($gallery_image, 'large')): ?>
                		<a class="item"
                			href="<?php echo esc_url( job_manager_get_resized_image($gallery_image, 'full') ? : $image ) ?>"
                			style="background-image: url(<?php echo esc_url( $image ) ?>);">
                			<div class="overlay" style="
                                background-color: <?php echo esc_attr( c27()->get_setting('single_listing_cover_overlay_color', '#242429') ); ?>;
                                opacity: <?php echo esc_attr( c27()->get_setting('single_listing_cover_overlay_opacity', '0.5') ); ?>;
                                "></div>
                		</a>
                	<?php endif ?>
                <?php endforeach ?>
            </div>
        <?php else: ?>
            <section class="featured-section profile-cover profile-cover-no-img">
            <div class="overlay" style="
                background-color: <?php echo esc_attr( c27()->get_setting('single_listing_cover_overlay_color', '#242429') ); ?>;
                opacity: <?php echo esc_attr( c27()->get_setting('single_listing_cover_overlay_opacity', '0.5') ); ?>;
                "></div>
        <?php endif ?>
    <?php endif ?>

    <!-- LISTING NO COVER -->
    <?php if ($layout['cover']['type'] == 'none'): ?>
        <section class="featured-section profile-cover profile-cover-no-img">
            <div class="overlay" style="
                background-color: <?php echo esc_attr( c27()->get_setting('single_listing_cover_overlay_color', '#242429') ); ?>;
                opacity: <?php echo esc_attr( c27()->get_setting('single_listing_cover_overlay_opacity', '0.5') ); ?>;
                "></div>
    <?php endif ?>

    <!-- COVER BUTTONS -->
        <div class="profile-cover-content reveal">
            <div class="container">
                <div class="cover-buttons">
                    <ul v-pre>
                        <?php if ($layout['buttons']): ?>
                            <?php foreach ($layout['buttons'] as $button):
                                $buttonID = uniqid() . '__cover_button';
                                $button_classes = '';
                                $button_style = isset($button['style']) && in_array($button['style'], array_keys($button_styles)) ? $button_styles[$button['style']] : 'button-outlined';
                                $button['style'] = $button_style;
                                if (isset($button['label']) && isset($button['icon']) && $button['icon']) {
                                    $button['label'] = c27()->get_icon_markup($button['icon']) . '<span class="button-label">' . $button['label'] . '</span>';
                                }
                                ?>

                                <?php if ($button['action'] == 'custom-field' && ( $meta_value = $listing->get_field( $button['custom_field'] ) ) ): ?>
                                    <li>
                                        <?php

                                        $meta_value = apply_filters( 'case27\listing\cover\field\\' . $button['custom_field'], $meta_value, $button, $listing );

                                        if ( is_array( $meta_value ) ) {
                                            $meta_value = join( ', ', $meta_value );
                                        }

                                        $GLOBALS['c27_active_shortcode_content'] = $meta_value;
                                        $btn_content = str_replace( '[[field]]', $meta_value, do_shortcode( $button['label'] ) );

                                        if ( has_shortcode( $button['label'], '27-format') ) {
                                            $button_classes.= ' formatted ';

                                            preg_match('/\[27-format.*type="(?<format_type>[^"]+)"/', $button['label'], $matches);

                                            if (isset($matches['format_type']) && $matches['format_type']) {
                                                $button_classes .= ' ' . $matches['format_type'] . ' ';
                                            }
                                        }
                                        ?>

                                        <?php if (trim($meta_value) && trim($btn_content)): ?>
                                            <div class="buttons medium <?php echo esc_attr( $button_style ) ?> <?php echo esc_attr( $button_classes ) ?>">
                                                <?php echo $btn_content ?>
                                            </div>
                                        <?php endif ?>
                                    </li>
                                <?php endif ?>

                                <?php if ($button['action'] == 'display-rating' && ($listing_rating = CASE27_Integrations_Review::get_listing_rating_optimized(get_the_ID())) ): ?>
                                     <li>
                                         <div class="inside-rating listing-rating <?php echo esc_attr( $button_style ) ?>">
                                             <span class="value"><?php echo esc_html( $listing_rating ) ?></span>
                                             <sup class="out-of">/<?php echo CASE27_Integrations_Review::max_rating( get_the_ID() ); ?></sup>
                                         </div>
                                     </li>
                                <?php endif ?>

                                <?php if ($button['action'] == 'bookmark'): ?>
                                    <li>
                                        <a href="#" data-listing-id="<?php echo esc_attr( get_the_ID() ) ?>" data-nonce="<?php echo esc_attr( wp_create_nonce('c27_bookmark_nonce') ) ?>"
                                           class="buttons <?php echo esc_attr( $button_style ) ?> medium bookmark c27-bookmark-button <?php echo CASE27_Integrations_Bookmark::instance()->is_bookmarked(get_the_ID(), get_current_user_id()) ? 'bookmarked' : '' ?>">
                                            <?php echo do_shortcode($button['label']) ?>
                                        </a>
                                    </li>
                                <?php endif ?>

                                <?php if ($button['action'] == 'book'): ?>
                                    <li>
                                        <a href="#book-now" class="buttons <?php echo esc_attr( $button_style ) ?> medium book-now c27-book-now">
                                            <?php echo do_shortcode($button['label']) ?>
                                        </a>
                                    </li>
                                <?php endif ?>

                                <?php if ($button['action'] == 'add-review'): ?>
                                   <li>
                                       <a href="#add-review" class="buttons <?php echo esc_attr( $button_style ) ?> medium add-review c27-add-listing-review">
                                           <?php echo do_shortcode($button['label']) ?>
                                       </a>
                                   </li>
                                <?php endif ?>

                                <?php if ($button['action'] == 'share'): ?>
                                    <?php $links = mylisting()->sharer()->get_links([
                                        'permalink' => get_permalink(),
                                        'image' => job_manager_get_resized_image( $listing->get_field( 'job_logo' ), 'large'),
                                        'title' => get_the_title(),
                                        'description' => get_the_content(),
                                        ]) ?>

                                        <?php if ($links): ?>
                                            <li class="dropdown">
                                                <a href="#" class="buttons <?php echo esc_attr( $button_style ) ?> medium show-dropdown sn-share" type="button" id="<?php echo esc_attr( $buttonID ) ?>" data-toggle="dropdown">
                                                    <?php echo do_shortcode( $button['label'] ) ?>
                                                </a>
                                                <ul class="i-dropdown share-options dropdown-menu" aria-labelledby="<?php echo esc_attr( $buttonID ) ?>">
                                                    <?php foreach ( $links as $link ): ?>
                                                        <li><?php mylisting()->sharer()->print_link( $link ) ?></li>
                                                    <?php endforeach ?>
                                                </ul>
                                            </li>
                                        <?php endif ?>
                                <?php endif ?>

                                <?php do_action( "case27\listing\cover\buttons\\{$button['action']}", $button, $listing ) ?>
                            <?php endforeach ?>
                        <?php endif ?>

                        <li class="dropdown">
                            <a href="#" class="buttons button-outlined medium show-dropdown c27-listing-actions" type="button" id="more-actions" data-toggle="dropdown">
                                <i class="mi more_vert"></i>
                            </a>
                            <ul class="i-dropdown share-options dropdown-menu" aria-labelledby="more-actions">
                                <?php
                                if ( job_manager_user_can_edit_job( $post->ID ) && function_exists( 'wc_get_account_endpoint_url' ) ) :
                                    $endpoint = wc_get_account_endpoint_url( 'my-listings' );
                                    $edit_link = add_query_arg([
                                        'action' => 'edit',
                                        'job_id' => $post->ID
                                        ], $endpoint);
                                    ?>
                                    <li><a href="<?php echo esc_url( $edit_link ) ?>"><?php _e( 'Edit Listing', 'my-listing' ) ?></a></li>
                                <?php endif ?>
                                <li><a href="#" data-toggle="modal" data-target="#report-listing-modal"><?php _e( 'Report this Listing', 'my-listing' ) ?></a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <div class="profile-header">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div v-pre>
                        <?php if ( $listing_logo ):
                            $listing_logo_large = job_manager_get_resized_image( $listing->get_field( 'job_logo' ), 'full'); ?>
                            <a class="profile-avatar open-photo-swipe"
                               href="<?php echo esc_url( $listing_logo_large ) ?>"
                               style="background-image: url('<?php echo esc_url( $listing_logo ) ?>')"
                               >
                            </a>
                        <?php endif ?>
                    </div>
                    <div class="profile-name" v-pre>
                        <h1 class="case27-primary-text"><?php the_title() ?></h1>
                        <?php if ( $listing->get_field('job_tagline') ): ?>
                            <h2><?php echo esc_html( $listing->get_field('job_tagline') ) ?></h2>
                        <?php elseif ( $listing->get_field('job_description') ): ?>
                            <h2><?php echo c27()->the_text_excerpt( wp_kses( $listing->get_field('job_description'), [] ), 77 ) ?></h2>
                        <?php endif ?>
                    </div>
                    <div class="cover-details" v-pre>
                        <ul></ul>
                    </div>
                    <div class="profile-menu">
                        <ul role="tablist">
                            <?php $i = 0;
                            foreach ((array) $layout['menu_items'] as $key => $menu_item): $i++;
                                if (
                                    $menu_item['page'] == 'bookings' &&
                                    $menu_item['provider'] == 'timekit' &&
                                    ! $listing->get_field( $menu_item['field'] )
                                ) { continue; }

                                ?><li class="<?php echo ($i == 1) ? 'active' : '' ?>">
                                    <a href="<?php echo "#_tab_{$i}" ?>" aria-controls="<?php echo esc_attr( "_tab_{$i}" ) ?>" data-section-id="<?php echo esc_attr( "_tab_{$i}" ) ?>"
                                       role="tab" class="tab-reveal-switch <?php echo esc_attr( "toggle-tab-type-{$menu_item['page']}" ) ?>">
                                        <?php echo esc_html( $menu_item['label'] ) ?>

                                        <?php if ($menu_item['page'] == 'comments'): ?>
                                            <span class="items-counter"><?php echo get_comments_number() ?></span>
                                        <?php endif ?>

                                        <?php if (in_array($menu_item['page'], ['related_listings', 'store'])):
                                            $vue_data_keys = ['related_listings' => 'related_listings', 'store' => 'products'];
                                            ?>
                                            <span class="items-counter" v-if="<?php echo esc_attr( $vue_data_keys[$menu_item['page']] ) ?>['_tab_<?php echo esc_attr( $i ) ?>'].loaded" v-cloak>
                                                {{ <?php echo $vue_data_keys[$menu_item['page']] ?>['_tab_<?php echo $i ?>'].count }}
                                            </span>
                                            <span v-else class="c27-tab-spinner">
                                                <i class="fa fa-circle-o-notch fa-spin"></i>
                                            </span>
                                        <?php endif ?>
                                    </a>
                                </li><?php
                            endforeach; ?>
                            <div id="border-bottom"></div>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-content">
        <?php $i = 0; ?>
        <?php foreach ((array) $layout['menu_items'] as $key => $menu_item): $i++; ?>
            <section class="tab-pane profile-body <?php echo ($i == 1) ? 'active' : '' ?> <?php echo esc_attr( "tab-type-{$menu_item['page']}" ) ?>" id="<?php echo esc_attr( "_tab_{$i}" ) ?>" role="tabpanel">

                <?php if ($menu_item['page'] == 'main' || $menu_item['page'] == 'custom'): ?>
                    <div class="container" v-pre>
                        <div class="row grid reveal">

                            <?php foreach ($menu_item['layout'] as $block):
                                $block_wrapper_class = 'col-md-6 col-sm-12 col-xs-12 grid-item';

                                if ( ! empty( $block['type'] ) ) {
                                    $block_wrapper_class .= ' block-type-' . esc_attr( $block['type'] );
                                }

                                if ( ! empty( $block['show_field'] ) ) {
                                    $block_wrapper_class .= ' block-field-' . esc_attr( $block['show_field'] );
                                }

                                if (
                                    $listing->type && ! empty( $block['show_field'] ) &&
                                    $listing->get_field( $block['show_field'] ) &&
                                    $listing->type->get_field( $block['show_field'] )
                                ) {
                                    $field = $listing->type->get_field( $block['show_field'] );
                                } else {
                                    $field = null;
                                }

                                // Text Block.
                                if ( $block['type'] == 'text' && ( $block_content = $listing->get_field( $block['show_field'] ) ) ) {
                                    $escape_html = true;
                                    $allow_shortcodes = false;
                                    if ( $field ) {
                                        if ( ! empty( $field['type'] ) && $field['type'] == 'wp-editor' ) {
                                            $escape_html = false;
                                        }

                                        if ( ! empty( $field['type'] ) && $field['type'] == 'texteditor' ) {
                                            $escape_html = empty( $field['editor-type'] ) || $field['editor-type'] == 'textarea';
                                            $allow_shortcodes = ! empty( $field['allow-shortcodes'] ) && $field['allow-shortcodes'] && ! $escape_html;
                                        }
                                    }

                                    c27()->get_section('content-block', [
                                        'ref' => 'single-listing',
                                        'icon' => 'material-icons://view_headline',
                                        'title' => $block['title'],
                                        'content' => $block_content,
                                        'wrapper_class' => $block_wrapper_class,
                                        'escape_html' => $escape_html,
                                        'allow-shortcodes' => $allow_shortcodes,
                                        ]);
                                }


                                // Gallery Block.
                                if ( $block['type'] == 'gallery' && ( $gallery_items = (array) $listing->get_field( $block['show_field'] ) ) ) {
                                    $gallery_type = 'carousel';
                                    foreach ((array) $block['options'] as $option) {
                                        if ($option['name'] == 'gallery_type') $gallery_type = $option['value'];
                                    }

                                    if ( array_filter( $gallery_items ) ) {
                                        c27()->get_section('gallery-block', [
                                            'ref' => 'single-listing',
                                            'icon' => 'material-icons://insert_photo',
                                            'title' => $block['title'],
                                            'gallery_type' => $gallery_type,
                                            'wrapper_class' => $block_wrapper_class,
                                            'gallery_items' => array_filter( $gallery_items ),
                                            'gallery_item_interface' => 'CASE27_JOB_MANAGER_ARRAY',
                                            ]);
                                    }
                                }

                                // Files Block.
                                if ( $block['type'] == 'file' && ( $files = (array) $listing->get_field( $block['show_field'] ) ) ) {
                                    if ( array_filter( $files ) ) {
                                        c27()->get_section('files-block', [
                                            'ref' => 'single-listing',
                                            'icon' => 'material-icons://attach_file',
                                            'title' => $block['title'],
                                            'wrapper_class' => $block_wrapper_class,
                                            'items' => array_filter( $files ),
                                            ]);
                                    }
                                }

                                // Categories Block.
                                if ( $block['type'] == 'categories' && ( $terms = $listing->get_field( 'job_category' ) ) ) {
                                    c27()->get_section('listing-categories-block', [
                                        'ref' => 'single-listing',
                                        'icon' => 'material-icons://view_module',
                                        'title' => $block['title'],
                                        'terms' => $terms,
                                        'wrapper_class' => $block_wrapper_class,
                                        ]);
                                }

                                // Tags Block.
                                if ( $block['type'] == 'tags' && ( $terms = $listing->get_field( 'job_tags' ) ) ) {
                                    c27()->get_section('list-block', [
                                        'ref' => 'single-listing',
                                        'icon' => 'material-icons://view_module',
                                        'title' => $block['title'],
                                        'items' => $terms,
                                        'item_interface' => 'WP_TERM',
                                        'wrapper_class' => $block_wrapper_class,
                                        ]);
                                }

                                if ( $block['type'] == 'terms' ) {
                                    // Keys = taxonomy name
                                    // Value = taxonomy field name
                                    $taxonomies = [
                                        'job_listing_category' => 'job_category',
                                        'case27_job_listing_tags' => 'job_tags',
                                        'region' => 'region',
                                    ];

                                    $taxonomy = 'job_listing_category';
                                    $template = 'listing-categories-block';

                                    if ( isset( $block['options'] ) ) {
                                        foreach ((array) $block['options'] as $option) {
                                            if ($option['name'] == 'taxonomy') $taxonomy = $option['value'];
                                            if ($option['name'] == 'style') $template = $option['value'];
                                        }
                                    }

                                    if ( ! isset( $taxonomies[ $taxonomy ] ) ) {
                                        continue;
                                    }

                                    if ( $terms = $listing->get_field( $taxonomies[ $taxonomy ] ) ) {
                                        if ( $template == 'list-block' ) {
                                            c27()->get_section('list-block', [
                                                'ref' => 'single-listing',
                                                'icon' => 'material-icons://view_module',
                                                'title' => $block['title'],
                                                'items' => $terms,
                                                'item_interface' => 'WP_TERM',
                                                'wrapper_class' => $block_wrapper_class,
                                            ]);
                                        } else {
                                            c27()->get_section('listing-categories-block', [
                                                'ref' => 'single-listing',
                                                'icon' => 'material-icons://view_module',
                                                'title' => $block['title'],
                                                'terms' => $terms,
                                                'wrapper_class' => $block_wrapper_class,
                                            ]);
                                        }
                                    }
                                }


                                // Location Block.
                                if ( $block['type'] == 'location' && isset( $block['show_field'] ) && ( $block_location = $listing->get_field( $block['show_field'] ) ) ) {
                                    $listing_logo = c27()->image('27.jpg');
                                    if ( $listing->get_field( 'job_logo' ) ) {
                                        $listing_logo = job_manager_get_resized_image( $listing->get_field( 'job_logo' ), 'thumbnail' );
                                    }

                                    $location_arr = [
                                        'address' => $block_location,
                                        'marker_image' => ['url' => $listing_logo],
                                    ];

                                    if ( $block['show_field'] == 'job_location' && ( $lat = $listing->get_data('geolocation_lat') ) && ( $lng = $listing->get_data('geolocation_long') ) ) {
                                        $location_arr = [
                                            'marker_lat' => $lat,
                                            'marker_lng' => $lng,
                                            'marker_image' => ['url' => $listing_logo],
                                        ];
                                    }

                                    $map_skin = 'skin1';
                                    if ( ! empty( $block['options'] ) ) {
                                        foreach ((array) $block['options'] as $option) {
                                            if ($option['name'] == 'map_skin') $map_skin = $option['value'];
                                        }
                                    }

                                    c27()->get_section('map', [
                                        'ref' => 'single-listing',
                                        'icon' => 'material-icons://map',
                                        'title' => $block['title'],
                                        'wrapper_class' => $block_wrapper_class,
                                        'template' => 'block',
                                        'options' => [
                                            'locations' => [ $location_arr ],
                                            'zoom' => 11,
                                            'draggable' => false,
                                            'skin' => $map_skin,
                                        ],
                                    ]);
                                }


                                // Contact Form Block.
                                if ($block['type'] == 'contact_form') {
                                    $contact_form_id = false;
                                    $email_to = ['job_email'];
                                    $recipients = [];
                                    foreach ((array) $block['options'] as $option) {
                                        if ($option['name'] == 'contact_form_id') $contact_form_id = $option['value'];
                                        if ($option['name'] == 'email_to') $email_to = $option['value'];
                                    }

                                    foreach ($email_to as $email_field) {
                                        if ( ( $email = $listing->get_field( $email_field ) ) && is_email( $email ) ) {
                                            $recipients[] = $email;
                                        }
                                    }

                                    if ( $contact_form_id && count( array_filter( $recipients ) ) ) {
                                        c27()->get_section('content-block', [
                                            'ref' => 'single-listing',
                                            'icon' => 'material-icons://email',
                                            'title' => $block['title'],
                                            'content' => str_replace('%case27_recipients%', join(',', $recipients), do_shortcode("[contact-form-7 id=\"{$contact_form_id}\"]")),
                                            'wrapper_class' => $block_wrapper_class,
                                            'escape_html' => false,
                                        ]);
                                    }
                                }


                                // Host Block.
                                if ($block['type'] == 'related_listing' && ( $related_listing = $listing->get_field( 'related_listing' ) ) ) {
                                    c27()->get_section('related-listing-block', [
                                        'ref' => 'single-listing',
                                        'icon' => 'material-icons://layers',
                                        'title' => $block['title'],
                                        'related_listing' => $related_listing,
                                        'wrapper_class' => $block_wrapper_class,
                                    ]);
                                }


                                // Countdown Block.
                                if ($block['type'] == 'countdown' && ( $countdown_date = $listing->get_field( $block['show_field'] ) ) ) {
                                    c27()->get_section('countdown-block', [
                                        'ref' => 'single-listing',
                                        'icon' => 'material-icons://av_timer',
                                        'title' => $block['title'],
                                        'countdown_date' => $countdown_date,
                                        'wrapper_class' => $block_wrapper_class,
                                    ]);
                                }

                                // Video Block.
                                if ($block['type'] == 'video' && ( $video_url = $listing->get_field( $block['show_field'] ) ) ) {
                                    c27()->get_section('video-block', [
                                        'ref' => 'single-listing',
                                        'icon' => 'material-icons://videocam',
                                        'title' => $block['title'],
                                        'video_url' => $video_url,
                                        'wrapper_class' => $block_wrapper_class,
                                    ]);
                                }

                                if ( in_array( $block['type'], [ 'table', 'accordion', 'tabs', 'details' ] ) ) {
                                    $rows = [];

                                    foreach ((array) $block['options'] as $option) {
                                        if ($option['name'] == 'rows') {
                                            foreach ((array) $option['value'] as $row) {
                                                if ( ! is_array( $row ) || empty( $row['show_field'] ) ) {
                                                    continue;
                                                }

                                                if ( ! ( $row_field = $listing->get_field( $row['show_field'] ) ) ) {
                                                    continue;
                                                }

                                                if ( is_array( $row_field ) ) {
                                                    $row_field = join( ', ', $row_field );
                                                }

                                                $rows[] = [
                                                    'title' => $row['label'],
                                                    'content' => $listing->compile_field_string( $row['content'], $row_field ),
                                                    'icon' => isset( $row['icon'] ) ? $row['icon'] : '',
                                                ];
                                            }
                                        }
                                    }
                                }

                                // Table Block.
                                if ( $block['type'] == 'table' && count( $rows ) ) {
                                    c27()->get_section('table-block', [
                                        'ref' => 'single-listing',
                                        'icon' => 'material-icons://view_module',
                                        'title' => $block['title'],
                                        'rows' => $rows,
                                        'wrapper_class' => $block_wrapper_class,
                                        ]);
                                }


                                // Details Block.
                                if ( $block['type'] == 'details' && count( $rows ) ) {
                                    c27()->get_section('list-block', [
                                        'ref' => 'single-listing',
                                        'icon' => 'material-icons://view_module',
                                        'title' => $block['title'],
                                        'item_interface' => 'CASE27_DETAILS_ARRAY',
                                        'items' => $rows,
                                        'wrapper_class' => $block_wrapper_class,
                                        ]);
                                }


                                // Accordion Block.
                                if ( $block['type'] == 'accordion' && count( $rows ) ) {
                                    c27()->get_section('accordion-block', [
                                        'ref' => 'single-listing',
                                        'icon' => 'material-icons://view_module',
                                        'title' => $block['title'],
                                        'rows' => $rows,
                                        'wrapper_class' => $block_wrapper_class,
                                        ]);
                                }

                                // Tabs Block.
                                if ( $block['type'] == 'tabs' && count( $rows ) ) {
                                    c27()->get_section('tabs-block', [
                                        'ref' => 'single-listing',
                                        'icon' => 'material-icons://view_module',
                                        'title' => $block['title'],
                                        'rows' => $rows,
                                        'wrapper_class' => $block_wrapper_class,
                                        ]);
                                }

                                // Work Hours Block.
                                if ($block['type'] == 'work_hours' && ( $work_hours = $listing->get_field( 'work_hours' ) ) ) {
                                    c27()->get_section('work-hours-block', [
                                        'wrapper_class' => $block_wrapper_class . ' open-now sl-zindex',
                                        'ref' => 'single-listing',
                                        'title' => $block['title'],
                                        'icon' => 'material-icons://alarm',
                                        'hours' => (array) $work_hours,
                                    ]);
                                }

                                // Social Networks (Links) Block.
                                if ( $block['type'] == 'social_networks' && ( $links = $listing->get_field('links') ) ) {
                                    c27()->get_section('list-block', [
                                        'ref' => 'single-listing',
                                        'icon' => 'material-icons://view_module',
                                        'title' => $block['title'],
                                        'item_interface' => 'CASE27_LINK_ARRAY',
                                        'items' => array_filter( array_map(function( $link ) {
                                            if ( ! is_array( $link ) || empty( $link['network'] ) || empty( $link['url'] ) ) {
                                                return false;
                                            }

                                            return ['title' => $link['network'], 'content' => $link['url']];
                                            }, (array) $links) ),
                                        'wrapper_class' => $block_wrapper_class,
                                    ]);
                                }

                                // Author Block.
                                if ($block['type'] == 'author') {
                                    c27()->get_section('author-block', [
                                        'icon' => 'material-icons://account_circle',
                                        'ref' => 'single-listing',
                                        'author_id' => get_the_author_meta( 'ID' ),
                                        'title' => $block['title'],
                                        'wrapper_class' => $block_wrapper_class,
                                    ]);
                                }

                                // Code block.
                                if ( $block['type'] == 'code' && ! empty( $block['content'] ) ) {
                                    if ( ( $content = $listing->compile_string( $block['content'] ) ) ) {
                                        c27()->get_section('raw-block', [
                                            'icon' => 'material-icons://view_module',
                                            'ref' => 'single-listing',
                                            'title' => $block['title'],
                                            'wrapper_class' => $block_wrapper_class,
                                            'content' => $content,
                                            'do_shortcode' => true,
                                        ]);
                                    }
                                }

                                // Raw content block.
                                if ( $block['type'] == 'raw' ) {
                                    $content = '';
                                    foreach ((array) $block['options'] as $option) {
                                        if ($option['name'] == 'content') $content = $option['value'];
                                    }

                                    if ( $content ) {
                                        c27()->get_section('raw-block', [
                                            'icon' => 'material-icons://view_module',
                                            'ref' => 'single-listing',
                                            'title' => $block['title'],
                                            'wrapper_class' => $block_wrapper_class,
                                            'content' => $content,
                                        ]);
                                    }
                                }

                                do_action( "case27/listing/blocks/{$block['type']}", $block );

                            endforeach ?>
                        </div>
                    </div>
                <?php endif ?>

                <?php if ($menu_item['page'] == 'comments'): ?>
                    <div v-pre>
                        <?php $GLOBALS['case27_reviews_allow_rating'] = $listing->type->is_rating_enabled() ?>
                        <?php comments_template() ?>
                    </div>
                <?php endif ?>

                <?php if ($menu_item['page'] == 'related_listings'): ?>
                    <input type="hidden" class="case27-related-listing-type" value="<?php echo esc_attr( $menu_item['related_listing_type'] ) ?>">
                    <div class="container c27-related-listings-wrapper reveal">
                        <div class="row listings-loading" v-show="related_listings['<?php echo esc_attr( "_tab_{$i}" ) ?>'].loading">
                            <div class="loader-bg">
                                <?php c27()->get_partial('spinner', [
                                    'color' => '#777',
                                    'classes' => 'center-vh',
                                    'size' => 28,
                                    'width' => 3,
                                    ]); ?>
                            </div>
                        </div>
                        <div class="row section-body i-section" v-show="!related_listings['<?php echo esc_attr( "_tab_{$i}" ) ?>'].loading">
                            <div class="c27-related-listings" v-html="related_listings['<?php echo esc_attr( "_tab_{$i}" ) ?>'].html" :style="!related_listings['<?php echo esc_attr( "_tab_{$i}" ) ?>'].show ? 'opacity: 0;' : ''"></div>
                        </div>
                        <div class="row">
                            <div class="c27-related-listings-pagination" v-html="related_listings['<?php echo esc_attr( "_tab_{$i}" ) ?>'].pagination"></div>
                        </div>
                    </div>
                <?php endif ?>

                <?php if ($menu_item['page'] == 'store'):
                    $selected_ids = isset($menu_item['field']) && $listing->get_field( $menu_item['field'] ) ? (array) $listing->get_field( $menu_item['field'] ) : [];
                    ?>
                    <input type="hidden" class="case27-store-products-ids" value="<?php echo json_encode(array_map('absint', (array) $selected_ids)) ?>">
                    <div class="container c27-products-wrapper woocommerce reveal">
                        <div class="row listings-loading" v-show="products['<?php echo esc_attr( "_tab_{$i}" ) ?>'].loading">
                            <div class="loader-bg">
                                <?php c27()->get_partial('spinner', [
                                    'color' => '#777',
                                    'classes' => 'center-vh',
                                    'size' => 28,
                                    'width' => 3,
                                    ]); ?>
                            </div>
                        </div>
                        <div class="section-body" v-show="!products['<?php echo esc_attr( "_tab_{$i}" ) ?>'].loading">
                            <ul class="c27-products products" v-html="products['<?php echo esc_attr( "_tab_{$i}" ) ?>'].html" :style="!products['<?php echo esc_attr( "_tab_{$i}" ) ?>'].show ? 'opacity: 0;' : ''"></ul>
                        </div>
                        <div class="row">
                            <div class="c27-products-pagination" v-html="products['<?php echo esc_attr( "_tab_{$i}" ) ?>'].pagination"></div>
                        </div>
                    </div>
                <?php endif ?>

                <?php if ($menu_item['page'] == 'bookings'): ?>
                    <div class="container" v-pre>
                        <div class="row">
                            <?php // Contact Form Block.
                            if ($menu_item['provider'] == 'basic-form') {
                                $contact_form_id = $menu_item['contact_form_id'];
                                $email_to = [$menu_item['field']];
                                $recipients = [];

                                foreach ($email_to as $email_field) {
                                    if ( ($email = $listing->get_field( $email_field ) ) && is_email( $email ) ) {
                                        $recipients[] = $email;
                                    }
                                }

                                c27()->get_section('content-block', [
                                    'ref' => 'single-listing',
                                    'icon' => 'material-icons://email',
                                    'title' => 'Book now',
                                    'content' => str_replace('%case27_recipients%', join(',', $recipients), do_shortcode("[contact-form-7 id=\"{$contact_form_id}\"]")),
                                    'wrapper_class' => 'col-md-6 col-md-push-3 col-sm-8 col-sm-push-2 col-xs-12 grid-item',
                                    'escape_html' => false,
                                    ]);
                            }
                            ?>

                            <?php // TimeKit Widget.
                            if ($menu_item['provider'] == 'timekit' && ( $timekitID = $listing->get_field( $menu_item['field'] ) ) ): ?>
                                <div class="col-md-8 col-md-push-2 c27-timekit-wrapper">
                                    <iframe src="https://my.timekit.io/<?php echo esc_attr( $timekitID ) ?>" frameborder="0"></iframe>
                                </div>
                            <?php endif ?>

                        </div>
                    </div>
                <?php endif ?>

            </section>
        <?php endforeach; ?>
    </div>

    <?php c27()->get_partial('report-modal', ['listing' => $post]) ?>
</div>
