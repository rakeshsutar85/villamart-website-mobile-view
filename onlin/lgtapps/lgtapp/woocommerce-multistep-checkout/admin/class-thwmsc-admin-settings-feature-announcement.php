<?php
/**
 * The admin display settings page functionality of the plugin.
 *
 * @link       https://themehigh.com
 * @since      1.0.0
 *
 * @package    woocommerce-multistep-checkout
 * @subpackage woocommerce-multistep-checkout/admin
 */
if(!defined('WPINC')){	die; } 

if(!class_exists('THWMSC_Admin_Settings_Feature_Announcement')):

class THWMSC_Admin_Settings_Feature_Announcement extends THWMSC_Admin_Settings {

    public function __construct() {
        parent::__construct('feature_announcement');
    }

    public function render_feature_popup1() {
        $admin_url  = 'admin.php?page=th_multi_step_checkout';
        $dismiss_url = $admin_url . '&thwmsc_feature_dismiss=true&thwmsc_feature_popup_nonce=' . wp_create_nonce( 'thwmsc_feature_popup_security');
        ?>

        <div class="thwmsc-feature-popup-overlay" style="display:none">
            <div class="thwmsc-feature-popup-wrapper">
                <div class="thwmsc-feature-popup-close">
                    <a class="thwmsc-feature-close-btn" href="<?php echo esc_url($dismiss_url); ?>"><img src="<?php echo THWMSC_URL ?>/admin/assets/images/feature-close.svg" /></a>
                </div>
                <div class="thwmsc-feature-popup-slide">
                    <div class="thwmsc-slider1-img"></div>
                    <div class="thwmsc-feature-list-wrapper">
                        <p class="thwmsc-feature-message" style="margin-top: 21px;">It's getting better!</p>
                        <p class="thwmsc-feature-highlight">The most awaited UI Update is finally here!</p>
                        <p class="thwmsc-feature-message" style="margin-left: 47px;margin-right: 47px; color: #797979;">The plugin just got a new look. Not to worry, no functionality gets changed. You will still have all those usual features with better customising ways.</p>
                    </div>
                </div>

                <div class="thwmsc-feature-popup-slide" style="display:none;">
                    <div class="thwmsc-slider2-img"></div>
                    <div class="thwmsc-feature-list-wrapper">
                        <p class="thwmsc-feature-message" style="margin-top: 21px;">The excitement doesn't end there.</p>
                        <p class="thwmsc-feature-highlight" style="color:#DF9037;">More Layouts & Progress Bar designs</p>
                        <p class="thwmsc-feature-message" style="margin-left: 47px;margin-right: 47px; color: #797979;">The best part of the most awaited update is you have access to more layouts and progress bar designs. Enjoy trying a new look to your checkout!</p>
                    </div>
                </div>

                <div class="thwmsc-manual-slider">
                    <span class="thwmsc-slider-dot active" onclick="prevSlide()"></span>
                    <span class="thwmsc-slider-dot" style="margin-left:4px;" onclick="showSlides()"></span>
                </div>
            </div>
        </div>
        <?php
    }
}

endif;