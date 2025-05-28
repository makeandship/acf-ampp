<?php

use Medicines\MedicinesClient;

// exit if accessed directly
if (!defined('ABSPATH')) {
    exit();
}

// check if class already exists
if (!class_exists('acf_field_ampp')):
    class acf_field_ampp extends acf_field
    {
        /*
         *  __construct
         *
         *  This function will setup the field type data
         *
         *  @type    function
         *  @date    5/03/2014
         *  @since    5.0.0
         *
         *  @param    n/a
         *  @return    n/a
         */

        public function __construct($settings)
        {
            $this->name = 'ampp';
            $this->label = __('AMPP', 'acf-ampp');
            $this->category = 'choice';
            $this->defaults = [
                'post_type' => [],
                'taxonomy' => [],
                'allow_null' => 0,
                'multiple' => 0,
                'automatically_update_title' => 1,
            ];
            $this->api = new MedicinesClient();

            /*
             *  l10n (array) Array of strings that are used in JavaScript. This allows JS strings to be translated in PHP and loaded via:
             *  var message = acf._e('ampp', 'error');
             */

            $this->l10n = [
                'error' => __('Error! Please enter a higher value', 'acf-ampp'),
            ];

            /*
             *  settings (array) Store plugin settings (url, path, version) as a reference for later use with assets
             */
            $this->settings = $settings;

            add_action('wp_ajax_acf/fields/ampp/query', [$this, 'ajax_query']);
            add_action('wp_ajax_nopriv_acf/fields/ampp/query', [$this, 'ajax_query']);

            // do not delete!
            parent::__construct();
        }

        public function create_query($args, $field)
        {
            $query = [];
            $query['name'] = $args['s'];

            // full response for AMPP
            $query['scheme'] = 'core';
            $query['per'] = 100;

            $settings_filter_field = $field['filter_field'];
            $settings_filter_field_name = $field['filter_field_name'];

            if (
                !empty($settings_filter_field) &&
                !empty($settings_filter_field_name) &&
                (!array_key_exists('key', $this->settings) || $this->settings['filter_field'] !== 'None')
            ) {
                if (array_key_exists($settings_filter_field_name, $args)) {
                    $query[$settings_filter_field] = $args[$settings_filter_field_name];
                }
            }

            return $query;
        }

        /*
         *  find_ampps
         *
         *  load matches at AMPP level from DM+D given a partial
         *  virtual therapeutic moiety name
         *
         *  @type    function
         *  @date    01/06/2016
         *  @since    1.0.0
         *
         *  @param    $args post args (s contains the query)
         *  @return    $results array of vtm -> [ampp] results
         */
        public function find_ampps($query)
        {
            $results = $this->api->ampps($query);

            return $results;
        }

        /**
         * Transform a set of API results into id / text pairs
         */
        public function transform($matches)
        {
            $transformed = [];

            foreach ($matches as $match) {
                $entry = [
                    'id' => strval($match['id']),
                    'text' => $match['name'],
                ];

                array_push($transformed, $entry);
            }

            return $transformed;
        }

        /*
         *  ajax_query
         *
         *  description
         *
         *  @type    function
         *  @date    24/10/13
         *  @since    5.0.0
         *
         *  @param    $post_id (int)
         *  @return    $post_id (int)
         */

        public function ajax_query()
        {
            // validate
            if (!acf_verify_ajax()) {
                die();
            }

            // options
            $options = acf_parse_args($_POST, [
                'post_id' => 0,
                's' => '',
                'field_key' => '',
                'nonce' => '',
            ]);

            // load field
            $field = acf_get_field($options['field_key']);

            // get choices
            $query = $this->create_query($_POST, $field);
            $matches = $this->find_ampps($query);
            $choices = $this->transform($matches);

            // validate
            if (!$choices) {
                die();
            }

            // return JSON
            $json = json_encode(['results' => $choices]);

            echo $json;
            die();
        }

        /*
         *  render_field_settings()
         *
         *  Create extra settings for your field. These are visible when editing a field
         *
         *  @type    action
         *  @since    3.6
         *  @date    23/01/13
         *
         *  @param    $field (array) the $field being edited
         *  @return    n/a
         */

        public function render_field_settings($field)
        {
            /*
             *  acf_render_field_setting
             *
             *  This function will create a setting for your field. Simply pass the $field parameter and an array of field settings.
             *  The array of settings does not require a `value` or `prefix`; These settings are found from the $field array.
             *
             *  More than one setting can be added by copy/paste the above code.
             *  Please note that you must also have a matching $defaults value for the field name (font_size)
             */

            acf_render_field_setting($field, [
                'label' => __('Set Title Automatically?', 'acf-ampp'),
                'type' => 'radio',
                'name' => 'automatically_update_title',
                'choices' => [
                    1 => __('Yes', 'acf'),
                    0 => __('No', 'acf'),
                ],
                'layout' => 'horizontal',
            ]);

            acf_render_field_setting($field, [
                'label' => __('Filter?', 'acf-ampp'),
                'type' => 'select',
                'name' => 'filter_field',
                'choices' => [
                    'None' => __('No filter', 'acf'),
                    'vtm_id' => __('VTM', 'acf'),
                    'vmp_id' => __('VMP', 'acf'),
                    'amp_id' => __('AMP', 'acf'),
                    'vmpp_id' => __('VMPP', 'acf'),
                ],
                'layout' => 'horizontal',
            ]);

            acf_render_field_setting($field, [
                'label' => __('Filter field name', 'acf-ampp'),
                'type' => 'text',
                'name' => 'filter_field_name',
                'layout' => 'horizontal',
            ]);
        }

        /*
         *  render_field()
         *
         *  Create the HTML interface for your field
         *
         *  @param    $field (array) the $field being rendered
         *
         *  @type    action
         *  @since    3.6
         *  @date    23/01/13
         *
         *  @param    $field (array) the $field being edited
         *  @return    n/a
         */

        public function render_field($field)
        {
            $field['type'] = 'select';
            $field['ui'] = 1;
            $field['ajax'] = 1;
            $field['choices'] = [];

            if (!empty($field['value'])) {
                $name = '';

                // get the medicine
                $query = [
                    'scheme' => 'full',
                ];
                $ampp = $this->api->ampp($field['value'], $query);
                if ($ampp) {
                    if (array_key_exists('name', $ampp)) {
                        $name = $ampp['name'];
                    }
                }

                // populate the choices
                $field['choices'][$field['value']] = $name;
            }

            acf_render_field($field);
            /*
         *  Create a simple text input using the 'font_size' setting.

        ?>
        <input type="text" name="<?php echo esc_attr($field['name']) ?>" value="<?php echo esc_attr($field['value']) ?>" style="font-size:<?php echo $field['font_size'] ?>px;" />
        <?php
         */
        }

        /*
         *  input_admin_enqueue_scripts()
         *
         *  This action is called in the admin_enqueue_scripts action on the edit screen where your field is created.
         *  Use this action to add CSS + JavaScript to assist your render_field() action.
         *
         *  @type    action (admin_enqueue_scripts)
         *  @since    3.6
         *  @date    23/01/13
         *
         *  @param    n/a
         *  @return    n/a
         */

        public function input_admin_enqueue_scripts()
        {
            // vars
            $url = $this->settings['url'];
            $version = $this->settings['version'];

            // register & include JS
            wp_register_script('acf-input-ampp', "{$url}assets/js/input.js", ['acf-input'], $version);
            wp_enqueue_script('acf-input-ampp');

            // register & include CSS
            wp_register_style('acf-input-ampp', "{$url}assets/css/input.css", ['acf-input'], $version);
            wp_enqueue_style('acf-input-ampp');
        }

        /*
         *  input_admin_head()
         *
         *  This action is called in the admin_head action on the edit screen where your field is created.
         *  Use this action to add CSS and JavaScript to assist your render_field() action.
         *
         *  @type    action (admin_head)
         *  @since    3.6
         *  @date    23/01/13
         *
         *  @param    n/a
         *  @return    n/a
         */

        /*

        function input_admin_head() {

        }

         */

        /*
         *  input_form_data()
         *
         *  This function is called once on the 'input' page between the head and footer
         *  There are 2 situations where ACF did not load during the 'acf/input_admin_enqueue_scripts' and
         *  'acf/input_admin_head' actions because ACF did not know it was going to be used. These situations are
         *  seen on comments / user edit forms on the front end. This function will always be called, and includes
         *  $args that related to the current screen such as $args['post_id']
         *
         *  @type    function
         *  @date    6/03/2014
         *  @since    5.0.0
         *
         *  @param    $args (array)
         *  @return    n/a
         */

        /*

        function input_form_data( $args ) {

        }

         */

        /*
         *  input_admin_footer()
         *
         *  This action is called in the admin_footer action on the edit screen where your field is created.
         *  Use this action to add CSS and JavaScript to assist your render_field() action.
         *
         *  @type    action (admin_footer)
         *  @since    3.6
         *  @date    23/01/13
         *
         *  @param    n/a
         *  @return    n/a
         */

        /*

        function input_admin_footer() {

        }

         */

        /*
         *  field_group_admin_enqueue_scripts()
         *
         *  This action is called in the admin_enqueue_scripts action on the edit screen where your field is edited.
         *  Use this action to add CSS + JavaScript to assist your render_field_options() action.
         *
         *  @type    action (admin_enqueue_scripts)
         *  @since    3.6
         *  @date    23/01/13
         *
         *  @param    n/a
         *  @return    n/a
         */

        /*

        function field_group_admin_enqueue_scripts() {

        }

         */

        /*
         *  field_group_admin_head()
         *
         *  This action is called in the admin_head action on the edit screen where your field is edited.
         *  Use this action to add CSS and JavaScript to assist your render_field_options() action.
         *
         *  @type    action (admin_head)
         *  @since    3.6
         *  @date    23/01/13
         *
         *  @param    n/a
         *  @return    n/a
         */

        /*

        function field_group_admin_head() {

        }

         */

        /*
         *  load_value()
         *
         *  This filter is applied to the $value after it is loaded from the db
         *
         *  @type    filter
         *  @since    3.6
         *  @date    23/01/13
         *
         *  @param    $value (mixed) the value found in the database
         *  @param    $post_id (mixed) the $post_id from which the value was loaded
         *  @param    $field (array) the field array holding all the field options
         *  @return    $value
         */
        /*
        function load_value( $value, $post_id, $field ) {

        return $value;

        }*/

        /*
         *  update_value()
         *
         *  This filter is applied to the $value before it is saved in the db
         *
         *  @type    filter
         *  @since    3.6
         *  @date    23/01/13
         *
         *  @param    $value (mixed) the value found in the database
         *  @param    $post_id (mixed) the $post_id from which the value was loaded
         *  @param    $field (array) the field array holding all the field options
         *  @return    $value
         */
        /*
        function update_value( $value, $post_id, $field ) {

        return $value;

        }
         */

        /*
         *  format_value()
         *
         *  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
         *
         *  @type    filter
         *  @since    3.6
         *  @date    23/01/13
         *
         *  @param    $value (mixed) the value which was loaded from the database
         *  @param    $post_id (mixed) the $post_id from which the value was loaded
         *  @param    $field (array) the field array holding all the field options
         *
         *  @return    $value (mixed) the modified value
         */
        /*
        function format_value( $value, $post_id, $field ) {

        // bail early if no value
        if( empty($value) ) {

        return $value;

        }

        // apply setting
        if( $field['font_size'] > 12 ) {

        // format the value
        // $value = 'something';

        }

        // return
        return $value;
        }
         */

        /*
         *  validate_value()
         *
         *  This filter is used to perform validation on the value prior to saving.
         *  All values are validated regardless of the field's required setting. This allows you to validate and return
         *  messages to the user if the value is not correct
         *
         *  @type    filter
         *  @date    11/02/2014
         *  @since    5.0.0
         *
         *  @param    $valid (boolean) validation status based on the value and the field's required setting
         *  @param    $value (mixed) the $_POST value
         *  @param    $field (array) the field array holding all the field options
         *  @param    $input (string) the corresponding input name for $_POST value
         *  @return    $valid
         */
        /*
        function validate_value( $valid, $value, $field, $input ){
        $valid = true;

        // Basic usage
        if( $value < $field['custom_minimum_setting'] )
        {
        $valid = false;
        }

        // Advanced usage
        if( $value < $field['custom_minimum_setting'] )
        {
        $valid = __('The value is too little!','acf-ampp');
        }

        // return
        return $valid;

        }
         */

        /*
         *  delete_value()
         *
         *  This action is fired after a value has been deleted from the db.
         *  Please note that saving a blank value is treated as an update, not a delete
         *
         *  @type    action
         *  @date    6/03/2014
         *  @since    5.0.0
         *
         *  @param    $post_id (mixed) the $post_id from which the value was deleted
         *  @param    $key (string) the $meta_key which the value was deleted
         *  @return    n/a
         */
        /*
        function delete_value( $post_id, $key ) {

        }
         */

        /*
         *  load_field()
         *
         *  This filter is applied to the $field after it is loaded from the database
         *
         *  @type    filter
         *  @date    23/01/2013
         *  @since    3.6.0
         *
         *  @param    $field (array) the field array holding all the field options
         *  @return    $field
         */
        /*
        function load_field( $field ) {

        return $field;

        }
         */

        /*
         *  update_field()
         *
         *  This filter is applied to the $field before it is saved to the database
         *
         *  @type    filter
         *  @date    23/01/2013
         *  @since    3.6.0
         *
         *  @param    $field (array) the field array holding all the field options
         *  @return    $field
         */
        /*
        function update_field( $field ) {

        return $field;

        }
         */

        /*
         *  delete_field()
         *
         *  This action is fired after a field is deleted from the database
         *
         *  @type    action
         *  @date    11/02/2014
         *  @since    5.0.0
         *
         *  @param    $field (array) the field array holding all the field options
         *  @return    n/a
         */
        /*
    function delete_field( $field ) {

    }
     */
    }

    // initialize
    new acf_field_ampp($this->settings);

    // class_exists check
endif;
