<?php

if (!defined('WPINC')) {
    die;
}

class spbcta_Plugin
{
    private $version;
    private $page_slug;
    private $page_hook;
    private $base_url;
    private $base_dir;
    private $db;
    private $user_caps;

    public function __construct($_version, $_base_url = false, $base_dir)
    {
        require_once 'spbcta-plugin-db.php';
        $this->version = $_version;
        $this->page_slug = 'spbcta_plugin';
        $this->user_caps = 'manage_categories';
        $this->db = spbcta_DB_Table::get_instance();
        $this->base_dir = $base_dir;
        add_action('admin_menu', array($this, 'spbcta_add_menu_items'));
        add_action('admin_enqueue_scripts', array($this, 'spbcta_backend_enqueue'));
        add_action('wp_enqueue_scripts', array($this, 'spbcta_frontend_enqueue'));
        add_action('current_screen', array($this, 'spbcta_eventHandler'));
        add_shortcode('spbcta_sc', array($this, 'spbcta_handleSC'));
        add_shortcode('spbcta_sc_all', array($this, 'spbcta_handleSC_all'));
        add_action('admin_init', array($this, 'spbcta_spbThemesNotification'), 9);

        if (!$_base_url) {
            $this->base_url = plugins_url('', dirname(__FILE__));
        } else {
            $this->base_url = $_base_url;
        }
    }
    public function spbcta_spbThemesNotification()
    {
        $notifications = include($this->base_dir . '/admin_notification/Autoload.php');
        $options = array("delay" => "+3 days");
        $notifications->Add("spbcta_admin_notification", "Unlock All Features with Coupon Reveal Button Premium", "
		
            Take advantage of the up to <span style='font-weight:bold;'>40% discount</span> and unlock all features for Coupon Reveal Button Premium. 
            The discount is only available for a limited time.
    
            <div>
            <a style='margin-bottom:15px;' class='button button-large button-secondary' target='_blank' href='https://superbthemes.com/plugins/reveal-buttons/'>Read more</a> <a style='margin-bottom:15px;' class='button button-large button-primary' target='_blank' href='https://superbthemes.com/plugins/reveal-buttons/'>Buy now</a>
            </div>
    
            ", "info", $options);
        $notifications->Boot();
    }


    public function spbcta_add_menu_items()
    {
        $user_caps = apply_filters('spbcta_user_capabilities', $this->user_caps);
        $has_menu = menu_page_url('spbhlpr', false);
        if ($has_menu) {
            $this->page_hook = add_submenu_page('spbhlpr', 'Coupon Reveal Button', 'Reveal Button', $user_caps, $this->page_slug, array($this, "spbcta_print_page"), 1);
        } else {
            $this->page_hook = add_menu_page('Coupon Reveal Button', 'Reveal Button', $user_caps, $this->page_slug, array($this, 'spbcta_print_page'), $this->base_url . "/img/icon.png");
        }
    }

    public function spbcta_backend_enqueue($hook)
    {
        if ($this->page_hook != $hook) {
            return;
        }
        wp_enqueue_style('spbcta-stylesheet', $this->base_url . '/css/spbcta-stylesheet.css', false, $this->version, 'all');
        wp_enqueue_style('spbcta-stylesheet-front', $this->base_url . '/css/spbcta-stylesheet-front.css', false, $this->version, 'all');
        wp_enqueue_script('spbcta-script', $this->base_url . '/js/spbcta-plugin.js', array('jquery'), $this->version);
        wp_enqueue_script('spbcta-nm-script', $this->base_url . '/js/spbcta-nm.js');
        wp_enqueue_script('jquery-ui-dialog');
    }

    public function spbcta_frontend_enqueue()
    {
        wp_enqueue_style('spbcta-stylesheet-front', $this->base_url . '/css/spbcta-stylesheet-front.css', false, $this->version, 'all');
        wp_enqueue_script('spbcta-nm-script', $this->base_url . '/js/spbcta-nm.js', array('jquery'), $this->version);
    }


    public function spbcta_print_page()
    { ?>
        <div class="review-banner">
            <p><span>&#128075;</span> Hi there! We sincerely hope you're enjoying our coupon reveal button. Please consider <a href="https://wordpress.org/support/plugin/coupon-reveal-button/reviews/" target="_blank">reviewing it here</a>. It means the world to us!</p>
        </div>


        <?php
        if (isset($_GET['func']) && $_GET['func'] == 'add_cta') {

            // New button no data yet
            echo '
			<div class="spbctawrap">

			<h2 class="spbcta_backend_headline edit-button-view-headline">Coupon Reveal Button</h2>
			';
            // 'ADD TABLE' UI
            $this->spbcta_setup_UI('', '', '', '', '', '', '', '');
        } elseif (isset($_GET['func']) && isset($_GET['editnum']) && $_GET['func'] == 'edit_cta' && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'edit_cta')) {
            // Edit button
            echo '
			<div class="spbctawrap">

			<h2 class="spbcta_backend_headline edit-button-view-headline">Coupon Reveal Button</h2>
			';

            // 'EDIT TABLE' UI
            $table = $this->db->get(intval($_GET['editnum']));
            if ($table) {
                $this->spbcta_setup_UI($table['name'], $table['color'], $table['utext'], $table['ureveal'], $table['link'], $table['blank'], $table['nofollow'], $table['style']);
            }
        } else {

            //Buttons Overview
            echo '
			<div class="spbctawrap">
			<div class="get-all-features-wrapper buttons-overview-getallfeatures">
			<span class="get-all-features">Unlock all features with <strong>Premium</strong></span>
			<a href="https://superbthemes.com/plugins/reveal-buttons/" rel="nofollow" class="overview-get-premium" target="_blank">View Premium Version</a>
			</div>
			<h2 class="spbcta_backend_headline buttons-overview-headline">Coupon Reveal Button</h2>
			';

            // 'LIST TABLES' UI
            printf('<div class="top-buttons-overview"><a class="spbcta_btn spbcta_btn_new_table" href="%s">%s</a>', admin_url('admin.php?page=' . $this->page_slug . "&func=add_cta"), 'Add New Button');
            if (isset($_GET['deletedCTA'])) {
                echo '<p class="spbcta_removed">The button "' . esc_attr($_GET['deletedCTA']) . '" has successfully been deleted.</p>';
            }
            echo "
			<div class='spbcta_tip overview_tip'><span>Tip:</span> Copy & Paste shortcodes in your post/page to show the button.</div></div>
			<div class='spbcta_backend-table-outer'>
			<table class='spbcta_backend-table'> 
			<tr>
			<th>Button Name</th>
			<th class='shortcode-explanation'>Button Shortcode</th>
			<th>Edit Button</th>
			<th>Copy Button</th>
			<th>Delete Button</th>
			</tr>";
            $all = $this->db->get_all();
            if ($all) {
                $tableCount = count($all);
                for ($i = 0; $i < $tableCount; $i++) {
                    printf('<tr>
						<td>%s</td>
						<td>
						<input type="text" class="spbcta_shortcode" value="[spbcta_sc id=%s]" readonly>
						</td>
						<td>
						<a class="spbcta_btn" href="%s">%s</a>
						</td>
						<td>
						<a class="spbcta_btn" href="%s">%s</a>
						</td>
						<td>
						<a class="spbcta_btn delete_btn" href="%s">%s</a>
						</td>
						</tr>', $all[$i]['name'], $all[$i]['id'], wp_nonce_url(admin_url('admin.php?page=' . $this->page_slug . "&func=edit_cta" . '&editnum=' . $all[$i]['id']), 'edit_cta'), 'Edit Button', wp_nonce_url(admin_url('admin.php?page=' . $this->page_slug . "&func=copy_CTA&CTAName=" . $all[$i]['name'] . '&copyNum=' . $all[$i]['id']), 'copy_CTA'), 'Copy Button', wp_nonce_url(admin_url('admin.php?page=' . $this->page_slug . "&func=delete_CTA&CTAName=" . $all[$i]['name'] . '&deleteNum=' . $all[$i]['id']), 'delete_CTA'), 'Delete Button');
                }
            }
            echo "</table></div>";

            if ($all !== false && count($all) > 0) {
        ?>
                <div class="spbcta-shortcode-all-wrapper">
                    <input type="text" class="spbcta_shortcode" value="[spbcta_sc_all]" readonly>
                    <p><?php esc_html_e("Use this shortcode to display all your buttons at once!"); ?></p>
                </div>
        <?php
            }
        }

        ?>
        </div>
    <?php
    }

    private function spbcta_setup_UI($CTAName, $CTAColor, $CTAutext, $CTAureveal, $CTAlink, $CTABlank, $CTAnofollow, $CTAStyle)
    {
        $CTAName = $CTAName ? $CTAName : '';
        $CTAutext = $CTAutext ? $CTAutext : '';
        $CTAureveal = $CTAureveal ? $CTAureveal : '';
        $CTAlink = $CTAlink ? $CTAlink : '';
        $CTAColor = $CTAColor ? $CTAColor : ['#00c6a5', '#ffffff', '#e8e8e8', '#6f6f6f', '#00c6a5'];
        $CTAStyle = $CTAStyle ? $CTAStyle : 'simple';
        $CTABlank = (int)$CTABlank ? $CTABlank : 0;


        printf('<a class="spbcta_btn btn_topright" href="%s">%s</a>', admin_url('admin.php?page=' . $this->page_slug), 'View All Buttons'); ?>

        <div class="get-all-features-wrapper edit-button-page">
            <span class="get-all-features">Unlock all features with <strong>Premium</strong></span>
            <a href="https://superbthemes.com/plugins/reveal-buttons/" rel="nofollow" class="overview-get-premium" target="_blank">View Premium Version</a>
        </div>


        <form method="post" name="saveCTA">
            <input class="spbcta_btn top-save-button" type="submit" value="Save Button" />
            <?php if (isset($_GET['editnum'])) { ?>
                <input name="CTAId" type="hidden" value=<?php echo "'" . intval($_GET['editnum']) . "'" ?> />
            <?php } ?>
            <input name="CTABlank" type="hidden" value=<?php echo intval($CTABlank); ?> />
            <!-- SHOW SHORTCODE IF EDIT -->
            <div class="spbcta_tableshortcode spbcta_tableshortcode-viewer">
                <?php if (isset($_GET['editnum'])) { ?><div class="spbcta_shortcodewrapper"><span class="spbcta_shortcodetext	">Shortcode</span><input type="text" class="spbcta_shortcode shortcodeedittable" value="[spbcta_sc id=<?php echo intval($_GET['editnum']); ?>]" readonly> </div><?php } ?>
                <div class="spbcta_tip button-designer"><span>Tip:</span> Copy & Paste shortcodes in your post/page to show the button.</div>
            </div>


            <!-- Tabs script -->
            <script>
                jQuery(document).ready(function($) {
                    (function($) {
                        $('.tab ul.tabs').addClass('active').find('> li:eq(0)').addClass('current');

                        $('.tab ul.tabs li a').click(function(g) {
                            var tab = $(this).closest('.tab'),
                                index = $(this).closest('li').index();

                            tab.find('ul.tabs > li').removeClass('current');
                            $(this).closest('li').addClass('current');

                            tab.find('.tab_content').find('div.tabs_item').not('div.tabs_item:eq(' + index + ')').slideUp();
                            tab.find('.tab_content').find('div.tabs_item:eq(' + index + ')').slideDown();

                            g.preventDefault();
                        });
                    })(jQuery);

                });
            </script>
            <!-- Tabs end -->

            <!-- Tabs -->
            <div class="edit-button-overview-tab-wrapper">
                <div class="tab edit-button-overview-tab">

                    <ul class="tabs">
                        <li><a href="#">Button Text</a></li>
                        <li><a href="#">Button Design</a></li>
                        <li><a href="#">Button Link</a></li>
                        <li><a href="#">Extended Shortcodes</a></li>
                    </ul> <!-- / tabs -->

                    <div class="tab_content">

                        <div class="tabs_item">
                            <table class="table-options">
                                <tr>
                                    <td>
                                        <div class="table-options-column-innner">
                                            <span class="table-options-info">Button Name</span><br>
                                            <input id="spbcta_CTAName" placeholder="Insert Name" type="text" name="CTAName" value="<?php echo esc_attr($CTAName); ?>" required="required">
                                        </div>
                                    </td>
                                    <td>
                                        <span class="table-options-info">Button Text</span><br>
                                        <input type="text" name="CTAutext" placeholder="Insert button text" value="<?php echo esc_attr($CTAutext); ?>" required="required">
                                    </td>
                                    <td>
                                        <span class="table-options-info">Reveal Text</span><br>
                                        <input type="text" name="CTAureveal" placeholder="Insert reveal text" value="<?php echo esc_attr($CTAureveal); ?>"> <!-- Only required="required" if no reveal disabled -->
                                    </td>


                                </tr>
                            </table>
                        </div>

                        <div class="tabs_item">
                            <div class="spbcta_backend_tablewrapper">
                                <table class="table-options">
                                    <tr>
                                        <td>
                                            <span class="table-options-info">Color Scheme</span><br>
                                            <input class="color-select" type="color" name="color[0]" id="colorSelect-bg" value="<?php echo esc_attr($CTAColor[0]); ?>">
                                            <input class="color-select" type="color" name="color[1]" id="colorSelect-f" value="<?php echo esc_attr($CTAColor[1]); ?>">
                                            <input class="color-select" type="color" name="color[2]" id="colorSelect-hbg" value="<?php echo esc_attr($CTAColor[2]); ?>">
                                            <input class="color-select" type="color" name="color[4]" id="colorSelect-bor" value="<?php echo esc_attr($CTAColor[4]); ?>">
                                            <input class="color-select" type="color" name="color[3]" id="colorSelect-hf" value="<?php echo esc_attr($CTAColor[3]); ?>">
                                        </td>
                                        <td>
                                            <span>Alternative styles available in Premium</span><br>
                                            <a style="display: inline-block;" href="https://superbthemes.com/plugins/reveal-buttons/" target="_blank" rel="nofollow">
                                                <span class="table-options-info">Premium Design</span><br>
                                                <img style="max-width:152px;height:auto;" src="<?php echo WP_PLUGIN_URL . '/coupon-reveal-button/img/p-design-2.png'; ?>" alt="Premium Design">
                                            </a>
                                            <a style="display: inline-block;" href="https://superbthemes.com/plugins/reveal-buttons/" target="_blank" rel="nofollow">
                                                <span class="table-options-info">Premium Design</span><br>
                                                <img style="max-width:120px;height:auto;" src="<?php echo WP_PLUGIN_URL . '/coupon-reveal-button/img/p-design-1.png'; ?>" alt="Premium Design">
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="tabs_item">
                            <div class="spbcta_backend_tablewrapper">
                                <table class="table-options">
                                    <tr>
                                        <td>
                                            <span class="table-options-info">Link</span><br>
                                            <input type="text" name="CTAlink" placeholder="Insert link" value="<?php echo esc_url($CTAlink); ?>">
                                        </td>
                                        <td>
                                            <span class="table-options-info">Set link to target blank</span><br>
                                            <input type="checkbox" id="spbcta_blank"> Activate target="_blank"
                                        </td>
                                        <td>
                                            <span>Available in Premium</span><br>
                                            <span class="table-options-info">Set link to nofollow</span><br>
                                            <a style="display: inline-block;" href="https://superbthemes.com/plugins/reveal-buttons/" target="_blank" rel="nofollow">
                                                <img style="width: 150px;height:auto;" src="<?php echo WP_PLUGIN_URL . '/coupon-reveal-button/img/nofollow-img.png'; ?>" alt="Premium Design">
                                            </a>
                                        </td>

                                    </tr>
                                </table>
                            </div>
                        </div> <!-- / tab_content -->

                        <div class="tabs_item">
                            <div class="spbcta_backend_tablewrapper">
                                <span class="table-options-info">Available in Premium</span><br>
                                <span>Create a single button and customize it on the fly with Extended Shortcodes. </span><br>
                                <span>Example:</span><br>
                                <input type="text" class="spbcta_shortcode" value='[spbcta_sc id=1 link="#newlink" text="Example Text" reveal="NewReveal"]' style="width:600px !important;" readonly>
                            </div>
                        </div>
                    </div> <!-- / tab -->
                    <!-- Tabs -->

                </div>
            </div>
            <div class="text-align-center" style="text-align:center;">
                <div class="spbcta-preview-wrapper-center">
                    <div class="spbcta-button-preview">
                        <span class="table-options-info">Button Preview</span>
                    </div>
                    <div class="spbcta-preview-wrapper">

                        <div class="reveal__button__wrapper reveal__button__<?php echo esc_attr($CTAStyle); ?>__design">
                            <a href="#spbcta_btn_preview" class="reveal__button__link">
                                <span class="reveal__button__text" style="background:<?php echo esc_attr($CTAColor[0]); ?> !important; color:<?php echo esc_attr($CTAColor[1]); ?> !important;"><?php echo esc_attr($CTAutext); ?></span>
                                <span class="reveal__button__hidden__content" style="color:<?php echo esc_attr($CTAColor[3]); ?> !important;border:2px dashed <?php echo esc_attr($CTAColor[4]); ?> !important;background-color:<?php echo esc_attr($CTAColor[2]); ?> !important;"><?php echo mb_substr(esc_attr($CTAureveal), -3) ?></span>
                            </a>
                        </div>
                        <div class="spbcta-preview-reset-wrapper" style="margin-top:50px;">
                            <input id="spbcta-preview-reset" type="button" class="spbcta_btn btn_topright" value="Reset Preview" style="background-color:#DEDEDE;">
                        </div>
                    </div>
                </div>
            </div>
            <div class="plugin-savebutton-wrapper">
                <!-- SHOW SAVE SUCCESS-->
                <?php
                if (isset($_GET['savedCTA'])) {
                    echo '<p class="spbcta_success">Your button "' . esc_attr($_GET['savedCTA']) . '" has been saved and is ready for use with the shortcode provided above.</p>';
                } ?>
                <!-- SAVE SUCCESS END -->

                <br>
                <?php wp_nonce_field('spbcta_submit', '_wpnonce'); ?>
                <input class="spbcta_btn" id="spbcta_saveNew" type="submit" value="Save Button" />
        </form>
        </div>






        <?php
    }

    private function spbcta_validateSanitize($input, $type)
    {
        if ($type == 'id') {
            $return = intval($input);
        }
        if ($type == 'string') {
            $return = sanitize_text_field($input);
        }
        if ($type == 'link') {
            $return = esc_url_raw($input);
        }
        if ($type == 'hex') {
            if (count(preg_grep('/^#[a-f0-9]{6}$/i', $input)) == 5) {
                $return = array_map('sanitize_hex_color', $input);
            } else {
                $return = ['#00c6a5', '#ffffff', '#e8e8e8', '#6f6f6f', '#00c6a5'];
            }
        }
        if ($type == 'bit') {
            if (strlen($input) > 1) {
                $return = 0;
            } else {
                $return = intval($input);
            }
        }
        if ($type == 'style') {
            if ($input != 'simple') {
                $return = 'simple';
            } else {
                $return = sanitize_text_field($input);
            }
        }
        return $return;
    }


    public function spbcta_eventHandler($current_screen)
    {
        $user_caps = apply_filters('spbcta_user_capabilities', $this->user_caps);
        if (current_user_can($user_caps)) {
            if (isset($_GET['func']) && $_GET['func'] == 'add_cta') {
                if (isset($_POST['CTAutext']) && isset($_POST['CTAureveal']) && isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'spbcta_submit')) {
                    $result = $this->db->add($this->spbcta_validateSanitize($_POST['CTAName'], 'string'), $this->spbcta_validateSanitize($_POST['CTAutext'], 'string'), $this->spbcta_validateSanitize($_POST['CTAureveal'], 'string'), $this->spbcta_validateSanitize($_POST['CTAlink'], 'link'), $this->spbcta_validateSanitize($_POST['color'], 'hex'), $this->spbcta_validateSanitize($_POST['CTABlank'], 'bit'), 0, $this->spbcta_validateSanitize('simple', 'style'));
                    if ($result) {
                        $sendback = add_query_arg(array('page' => $_GET['page'], 'savedCTA' => urlencode($_POST['CTAName']), 'success' => true), '');
                        wp_redirect($sendback);
                    }
                }
            }

            if (isset($_GET['func']) && isset($_GET['editnum']) && $_GET['func'] == 'edit_cta') {
                if (isset($_POST['CTAutext']) && isset($_POST['CTAureveal']) && isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'spbcta_submit')) {
                    $result = $this->db->update($this->spbcta_validateSanitize($_POST['CTAId'], 'id'), $this->spbcta_validateSanitize($_POST['CTAName'], 'string'), $this->spbcta_validateSanitize($_POST['CTAutext'], 'string'), $this->spbcta_validateSanitize($_POST['CTAureveal'], 'string'), $this->spbcta_validateSanitize($_POST['CTAlink'], 'link'), $this->spbcta_validateSanitize($_POST['color'], 'hex'), $this->spbcta_validateSanitize($_POST['CTABlank'], 'bit'), 0, $this->spbcta_validateSanitize('simple', 'style'));
                    if ($result) {
                        $sendback = add_query_arg(array('page' => $_GET['page'], 'func' => 'edit_cta', 'editnum' => $_GET['editnum'], 'savedCTA' => urlencode($_POST['CTAName']), 'success' => true, '_wpnonce' => wp_create_nonce('edit_cta')), '');
                        wp_redirect($sendback);
                    }
                }
            }

            if (isset($_GET['func']) && isset($_GET['copyNum']) && isset($_GET['CTAName']) && $_GET['func'] == 'copy_CTA' && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'copy_CTA')) {
                $newName = $_GET['CTAName'] . " copy";
                $result = $this->db->copy(intval($_GET['copyNum']), sanitize_text_field($newName));
                if ($result) {
                    $sendback = add_query_arg(array('page' => $_GET['page'], 'savedCTA' => urlencode($newName), 'success' => true), '');
                    wp_redirect($sendback);
                }
            }

            if (isset($_GET['func']) && isset($_GET['deleteNum']) && isset($_GET['CTAName']) && $_GET['func'] == 'delete_CTA' && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'delete_CTA')) {
                $result = $this->db->delete(intval($_GET['deleteNum']));
                if ($result) {
                    $sendback = add_query_arg(array('page' => $_GET['page'], 'deletedCTA' => urlencode($_GET['CTAName']), 'success' => true), '');
                    wp_redirect($sendback);
                }
            }
        }
    }

    public function spbcta_handleSC_all()
    {
        $buttons = $this->db->get_all();
        if ($buttons !== false && count($buttons) > 0) {
            ob_start();
            foreach ($buttons as &$single_button) {
                echo do_shortcode('[spbcta_sc id=' . $single_button['id'] . ']');
            }
            return ob_get_clean();
        }
        return;
    }


    public function spbcta_handleSC($atts)
    {

        // Attributes
        $atts = shortcode_atts(
            array(
                'id' => '',
            ),
            $atts,
            'spbcta_sc'
        );

        if (!$atts['id']) {
            return;
        }

        $table = $this->db->get((int)$atts['id']);
        if ($table) {
            $CTAId = (int)$atts['id'];
            $CTAColor = $table['color'];
            $CTAStyle = $table['style'] ? $table['style'] : 'simple';
            $CTAutext = $table['utext'];
            $CTAureveal = $table['ureveal'];
            $CTAlink = $table['link'];
            if (!$CTAlink) {
                $CTAlink = '#spbcta_ph' . $CTAId;
            }
            $CTABlank = (int)$table['blank'];

            ob_start(); ?>
            <div class="reveal__button__wrapper reveal__button__<?php echo esc_attr($CTAStyle); ?>__design">
                <!-- Coupon Reveal Button plugin -->
                <a id="spbcta<?php echo $CTAId ?>" href="<?php echo esc_url($CTAlink); ?>" <?php if ($CTABlank == 1) { ?>target="_blank" <?php } ?> class="reveal__button__link" onclick="spbctaNM.func.spbcta_pass('<?php echo base64_encode(esc_attr($CTAureveal)); ?>',this,'<?php echo esc_url($CTAlink); ?>',<?php echo $CTABlank ?>);">
                    <span class="reveal__button__text" style="background:<?php echo $CTAColor[0] ?> !important; color:<?php echo $CTAColor[1] ?> !important;"><?php echo esc_attr($CTAutext); ?></span>
                    <?php if ($CTAureveal) { ?><span class="reveal__button__hidden__content" style="color:<?php echo $CTAColor[3] ?> !important;border:dotted 2px <?php echo $CTAColor[4] ?> !important;background-color:<?php echo $CTAColor[2] ?> !important;"><?php echo mb_substr(esc_attr($CTAureveal), -3) ?></span><?php } ?>
                </a>
            </div>
<?php
            return ob_get_clean();
        }
    }


    public function spbcta_initialize()
    {
        $this->db->create_table();
    }

    public function spbcta_rollback()
    {
        $table = spbcta_DB_Table::get_instance();
        $table->drop_table();
    }
}
?>