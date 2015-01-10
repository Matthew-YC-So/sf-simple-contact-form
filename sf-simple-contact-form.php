<?php
/**
 * Plugin Name: SF Simple Contact Form
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: Simple Contact Form.  Enter [sitepoint_contact_form] on any page to display the contact form.
 * Version: 1.0.0
 * Author: Matthew So
 * Author URI: http://www.software-force.com
 * Text Domain: sf-simple-contact-form
 * Domain Path: /locale/
 * Network: true
 * License: GPL2
 */
 
/*  Show form */
function html_form_code() {

    global $sf_contact_form_information;
    $sf_contact_form_information = isset($sf_contact_form_information) ? $sf_contact_form_information : '' ;

    $sex_male = isset( $_POST["cf-sex"] ) && $_POST["cf-sex"] == "male" ? "checked=\"checked\"" : "";
    $sex_female = isset( $_POST["cf-sex"] ) && $_POST["cf-sex"] == "female" ? "checked=\"checked\"" : "";

    $hint_name = __('你的名字', 'sf-simple-contact-form');
    $hint_tel = __('你的聯絡電話', 'sf-simple-contact-form');
    $hint_email = __('你的電郵地址', 'sf-simple-contact-form');
    $hint_subject = __('今次聯絡主題', 'sf-simple-contact-form');
    $hint_message = __('今次聯絡內容', 'sf-simple-contact-form');

    $label_name = __('姓名', 'sf-simple-contact-form');
    $label_sex = __('性別', 'sf-simple-contact-form');
    $label_sex_female = __('女', 'sf-simple-contact-form');
    $label_sex_male = __('男', 'sf-simple-contact-form');
    $label_tel = __('電話', 'sf-simple-contact-form');
    $label_email = __('Email 電郵', 'sf-simple-contact-form');
    $label_subject = __('主題', 'sf-simple-contact-form');
    $label_message = __('問題', 'sf-simple-contact-form');
    $required = __('請填寫資料');
    $label_mandatory  = __('* 為必填欄位', 'sf-simple-contact-form');
    $submit = __('傳送', 'sf-simple-contact-form');
 
    $name = isset( $_POST["cf-name"] ) ? esc_attr( $_POST["cf-name"] ) : '' ;
    $email = isset( $_POST["cf-email"] ) ? esc_attr( $_POST["cf-email"] ) : '' ;
    $tel = isset( $_POST["cf-tel"] ) ? esc_attr( $_POST["cf-tel"] ) : '' ;
    $subject = isset( $_POST["cf-subject"] ) ? esc_attr( $_POST["cf-subject"] ) : '' ;
    $message = isset( $_POST["cf-message"] ) ? esc_attr( $_POST["cf-message"] ) : '' ;

    $html = file_get_contents(plugins_url( 'contact.html' , __FILE__ ));
    // get anything between <body> and </body> where <body can="have_as many" attributes="as required">
    if (preg_match('/(?:<body[^>]*>)(.*)<\/body>/isU', $html, $matches)) {
        $html = $matches[1];
    }

    echo strtr($html, array(
        '$label_name' => $label_name,
        '$label_sex' => $label_sex,
        '$label_sex_female' => $label_sex_female,
        '$label_sex_male' => $label_sex_male,
        '$label_email' => $label_email,
        '$label_tel' => $label_tel,
        '$label_subject' => $label_subject,
        
        '$label_message' => $label_message,
        
        '$name' => $name,
        '$sex_male' => $sex_male,
        '$sex_female' => $sex_female,
        '$email' => $email,
        '$subject' => $subject,
        '$tel' => $tel,
        '$message' => $message,
        '$hint_name' => $hint_name,
        '$hint_email' => $hint_email,
        '$hint_tel' => $hint_tel,
        '$hint_subject' => $hint_subject,
        '$hint_message' => $hint_message,
        '$required' => $required,
        '$label_mandatory' => $label_mandatory,
        '$action' => esc_url( $_SERVER['REQUEST_URI'] ),
        '$information' => $sf_contact_form_information,
        '$submit'=> $submit
        )
    );

}

/*  Send email */
function deliver_mail() {

    if (isset( $_POST['cf-submitted'] )) {
        $mandatory_fields = array('cf-name' => __('姓名'), 'cf-sex' => __('性別'), 'cf-email' => __('電郵'), 'cf-subject' => __('主題'), 'cf-message' => __('問題')); 
        $missing_fields = [];
        $all_entered = TRUE;
        foreach($mandatory_fields as $key=>$value) {
            if (!isset($_POST[$key]) ){
              $missing_fields[$key] = $value;
              $all_entered = FALSE;
            }
        }

        // if OK
        if ($all_entered) {
 
            // sanitize form values
            $name    = sanitize_text_field( $_POST["cf-name"] );
            $email   = sanitize_email( $_POST["cf-email"] );
            $sex   = sanitize_text_field( $_POST["cf-sex"] );
            $tel  = sanitize_text_field( $_POST["cf-tel"] );
            $subject = sanitize_text_field( $_POST["cf-subject"] );
            $message = esc_textarea( $_POST["cf-message"] );

            $body_template = '<style>
                .form-data { 
                    border-collapse: collapse ; 
                    border-style:  none;         
                    font-family: Calibri Verdana Arial;
                }
                .form-data td {
                    vertical-align:  top;
                    border: 1px solid #999;
                }
                .caption { font-weight: bold; }
            
            </style>
            <table class="form-data">
                <tbody>
                    <tr><td class="caption">Name :</td><td class="entry">{Name}</td></tr>
                    <tr><td class="caption">Sex :</td><td class="entry">{Sex}</td></tr>
                    <tr><td class="caption">Email :</td><td class="entry">{Email}</td></tr>
                    <tr><td class="caption">Tel :</td><td class="entry">{Tel}</td></tr>
                    <tr><td class="caption">Message :</td><td class="entry message">{Message}</td></tr>
                </tbody>
            </table>';
        
            $body = $body_template;
            $body = str_ireplace("{Name}",$name, $body );
            $body = str_ireplace("{Sex}",$sex, $body );
            $body = str_ireplace("{Email}",$email, $body );
            $body = str_ireplace("{Tel}", isset($tel) ? $tel : '', $body );
            $body = str_ireplace("{Message}",$message, $body );

 
            // get the blog administrator's email address
            $to = get_option( 'admin_email' );
 
            $headers = "From: $name <$email>" . "\r\n";
 
            // HTML body
            add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));

            // If email has been process for sending, display a success message
            if ( wp_mail( $to, $subject, $body, $headers ) ) {

                // echo __("<div class='information'><p>" .__('感謝你的查詢！ 我們將盡快回覆你!', 'sf-simple-contact-form'). "</p></div>");
                global $sf_contact_form_information ;
                $sf_contact_form_information = __('感謝你的查詢！ 我們將盡快回覆你!', 'sf-simple-contact-form');

                foreach($_POST as $key=>$value)
                {
                    if (substr($key, 0, 3) === "cf-" )
                       $_POST[$key] = "";
                }

            } else {
                echo 'An unexpected error occurred';
            }
        }
        else {
            $missing_fieldnames = '';
            foreach ($missing_fields as $key=>$value) {
              $missing_fieldnames = $missing_fieldnames.'<li>'.$value.'</li>' ;
              
            }
            // echo "<div class='error'>" . __('以下資料不齊全', 'sf-simple-contact-form')  . "<ul>$missing_fieldnames</ul></div>";
            $sf_contact_form_information = "<div class='error'>" . __('以下資料不齊全', 'sf-simple-contact-form')  . "<ul>$missing_fieldnames</ul></div>";
        }
    }    
}

/*  Hook up function */
function cf_shortcode() {
    ob_start();
    deliver_mail();
    html_form_code();
 
    return ob_get_clean();
}

/* Use [sitepoint_contact_form] as hook up string */
add_shortcode( 'sitepoint_contact_form', 'cf_shortcode' );

$sf_contact_form_information = '';

/**
 * Register style sheet.
 */
function register_plugin_styles() {
	wp_register_style( 'sf-simple-contact-form', plugins_url( 'style.css',  __FILE__) );
	wp_enqueue_style( 'sf-simple-contact-form' );
}
add_action( 'wp_enqueue_scripts', 'register_plugin_styles' );

/**
 * Javascript
 */
function my_scripts_method() {
	wp_enqueue_script(
		'sf-simple-contact-form-script',
		plugins_url( 'sf-simple-contact-form.js' , __FILE__ ),
		array( 'jquery' ), false, true
	);  // $ver  = false, $in_footer = true
}
add_action( 'wp_enqueue_scripts', 'my_scripts_method');

?>
