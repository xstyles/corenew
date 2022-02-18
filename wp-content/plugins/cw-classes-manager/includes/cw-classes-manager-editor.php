<?php

/**
 * CW Class Editor.
 *
 * Editor functions
 *
 * @package CW Class
 * @subpackage Editor
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Enqueues the CW Class editor scripts, css, settings and strings
 *
 * Inspired by wp_enqueue_media()
 *
 * @package CW Class
 * @subpackage Editor
 * @since CW Class (1.0.0)
 */
function cw_class_enqueue_editor($args = array())
{

  // Enqueue me just once per page, please.
  if (did_action('cw_class_enqueue_editor'))
    return;

  $defaults = array(
    'post'     => null,
    'user_id'  => bp_loggedin_user_id(),
    'callback' => null,
    'group_id' => null,
  );

  $args = wp_parse_args($args, $defaults);

  // We're going to pass the old thickbox media tabs to `media_upload_tabs`
  // to ensure plugins will work. We will then unset those tabs.
  $tabs = array(
    // handler action suffix => tab label
    'type'     => '',
    'type_url' => '',
    'gallery'  => '',
    'library'  => '',
  );

  $tabs = apply_filters('media_upload_tabs', $tabs);
  unset($tabs['type'], $tabs['type_url'], $tabs['gallery'], $tabs['library']);

  $props = array(
    'link'  => bp_get_option('image_default_link_type'), // db default is 'file'
    'align' => bp_get_option('image_default_align'), // empty default
    'size'  => bp_get_option('image_default_size'),  // empty default
  );

  $settings = array(
    'tabs'      => $tabs,
    'tabUrl'    => esc_url(add_query_arg(array('chromeless' => true), admin_url('admin-ajax.php'))),
    'mimeTypes' => false,
    'captions'  => !apply_filters('disable_captions', ''),
    'nonce'     => array(
      'sendToEditor' => wp_create_nonce('media-send-to-editor'),
      'rendezvous'   => wp_create_nonce('cw_class-editor')
    ),
    'post'    => array(
      'id' => 0,
    ),
    'defaultProps' => $props,
    'embedExts'    => false,
  );

  $post = $hier = null;
  $settings['user']     = intval($args['user_id']);
  $settings['group_id'] = intval($args['group_id']);

  if (!empty($args['callback'])) {
    $settings['callback'] = esc_url($args['callback']);
  }

  // Do we have member types ?
  $cw_class_member_types = array();
  $member_types = bp_get_member_types(array(), 'objects');
  if (!empty($member_types) && is_array($member_types)) {
    $cw_class_member_types['rdvMemberTypesAll'] = esc_html__('All member types', 'cw_class');
    foreach ($member_types as $type_key => $type) {
      $cw_class_member_types['rdvMemberTypes'][] = array('type' => $type_key, 'text' => esc_html($type->labels['singular_name']));
    }
  }

  if (!empty($cw_class_member_types)) {
    $settings = array_merge($settings, $cw_class_member_types);
  }

  $strings = array(
    // Generic
    'url'         => __('URL', 'cw_class'),
    'addMedia'    => __('Add Media', 'cw_class'),
    'search'      => __('Search', 'cw_class'),
    'select'      => __('Select', 'cw_class'),
    'cancel'      => __('Cancel', 'cw_class'),
    /* translators: This is a would-be plural string used in the media manager.
		   If there is not a word you can use in your language to avoid issues with the
		   lack of plural support here, turn it into "selected: %d" then translate it.
		 */
    'selected'    => __('%d selected', 'cw_class'),
    'dragInfo'    => __('Drag and drop to reorder images.', 'cw_class'),

    // Upload
    'uploadFilesTitle'  => __('Upload Files', 'cw_class'),
    'uploadImagesTitle' => __('Upload Images', 'cw_class'),

    // Library
    'mediaLibraryTitle'  => __('Media Library', 'cw_class'),
    'insertMediaTitle'   => __('Insert Media', 'cw_class'),
    'createNewGallery'   => __('Create a new gallery', 'cw_class'),
    'returnToLibrary'    => __('&#8592; Return to library', 'cw_class'),
    'allMediaItems'      => __('All media items', 'cw_class'),
    'noItemsFound'       => __('No items found.', 'cw_class'),
    'insertIntoPost'     => $hier ? __('Insert into page', 'cw_class') : __('Insert into post', 'cw_class'),
    'uploadedToThisPost' => $hier ? __('Uploaded to this page', 'cw_class') : __('Uploaded to this post', 'cw_class'),
    'warnDelete' =>      __("You are about to permanently delete this item.\n  'Cancel' to stop, 'OK' to delete.", 'cw_class'),

    // From URL
    'insertFromUrlTitle' => __('Insert from URL', 'cw_class'),

    // Featured Images
    'setFeaturedImageTitle' => __('Set Featured Image', 'cw_class'),
    'setFeaturedImage'    => __('Set featured image', 'cw_class'),

    // Gallery
    'createGalleryTitle' => __('Create Gallery', 'cw_class'),
    'editGalleryTitle'   => __('Edit Gallery', 'cw_class'),
    'cancelGalleryTitle' => __('&#8592; Cancel Gallery', 'cw_class'),
    'insertGallery'      => __('Insert gallery', 'cw_class'),
    'updateGallery'      => __('Update gallery', 'cw_class'),
    'addToGallery'       => __('Add to gallery', 'cw_class'),
    'addToGalleryTitle'  => __('Add to Gallery', 'cw_class'),
    'reverseOrder'       => __('Reverse order', 'cw_class'),
  );

  $cw_class_strings = apply_filters('cw_class_view_strings', array(
    // RendezVous
    'rdvMainTitle'      => _x('cw_class', 'RendezVous editor main title', 'cw_class'),
    'whatTab'           => _x('What?', 'RendezVous editor tab what name', 'cw_class'),
    'whenTab'           => _x('When?', 'RendezVous editor tab when name', 'cw_class'),
    'whoTab'            => _x('Who?', 'RendezVous editor tab who name', 'cw_class'),
    'rdvInsertBtn'      => __('Add to invites', 'cw_class'),
    'rdvNextBtn'        => __('Next', 'cw_class'),
    'rdvPrevBtn'        => __('Prev', 'cw_class'),
    'rdvSrcPlaceHolder' => __('Search', 'cw_class'),
    'invited'           => __('%d to invite', 'cw_class'),
    'removeInviteBtn'   => __('Remove Invite', 'cw_class'),
    'saveButton'        => __('Save cw_class', 'cw_class'),
  ));

  // Use the filter at your own risks!
  $cw_class_fields = array(
    'what' => apply_filters('cw_class_editor_core_fields', array(
      array(
        'id'          => 'title',
        'order'       => 0,
        'type'        => 'text',
        'placeholder' => esc_html__('What is this about ?', 'cw_class'),
        'label'       => esc_html__('Title', 'cw_class'),
        'value'       => '',
        'tab'         => 'what',
        'class'       => 'required'
      ),
      array(
        'id'          => 'venue',
        'order'       => 10,
        'type'        => 'text',
        'placeholder' => esc_html__('Where ?', 'cw_class'),
        'label'       => esc_html__('Venue', 'cw_class'),
        'value'       => '',
        'tab'         => 'what',
        'class'       => ''
      ),
      array(
        'id'          => 'description',
        'order'       => 20,
        'type'        => 'textarea',
        'placeholder' => esc_html__('Some details about this cw_class ?', 'cw_class'),
        'label'       => esc_html__('Description', 'cw_class'),
        'value'       => '',
        'tab'         => 'what',
        'class'       => ''
      ),
      array(
        'id'          => 'duration',
        'order'       => 30,
        'type'        => 'duree',
        'placeholder' => '00:00',
        'label'       => esc_html__('Duration', 'cw_class'),
        'value'       => '',
        'tab'         => 'what',
        'class'       => 'required'
      ),
      // array(
      // 	'id'          => 'privacy',
      // 	'order'       => 40,
      // 	'type'        => 'checkbox',
      // 	'placeholder' => esc_html__( 'Restrict to the selected members of the Who? tab', 'cw_class' ),
      // 	'label'       => esc_html__( 'Access', 'cw_class' ),
      // 	'value'       => '0',
      // 	'tab'         => 'what',
      // 	'class'       => ''
      // ),
      array(
        'id'          => 'utcoffset',
        'order'       => 50,
        'type'        => 'timezone',
        'placeholder' => '',
        'label'       => '',
        'value'       => '',
        'tab'         => 'what',
        'class'       => ''
      ),
    ))
  );

  // Do we have cw_class types ?
  if (cw_class_has_types()) {
    $cw_class_types_choices     = array();
    $cw_class_types_placeholder = array();

    foreach (cw_class()->types as $cw_class_type) {
      $cw_class_types_choices[]     = $cw_class_type->term_id;
      $cw_class_types_placeholder[] = $cw_class_type->name;
    }

    // Set the rendez-voys types field arg
    $cw_class_types_args = array(
      'id'          => 'type',
      'order'       => 15,
      'type'        => 'selectbox',
      'placeholder' => $cw_class_types_placeholder,
      'label'       => esc_html__('Type', 'cw_class'),
      'value'       => '',
      'tab'         => 'what',
      'class'       => '',
      'choices'     => $cw_class_types_choices
    );

    // Merge with other cw_class fields
    $cw_class_fields['what'] = array_merge($cw_class_fields['what'], array($cw_class_types_args));
  }

  /**
   * Use 'cw_class_editor_extra_fields' to add custom fields, you should be able
   * to save them using the 'cw_class_after_saved' action.
   */
  $cw_class_extra_fields = apply_filters('cw_class_editor_extra_fields', array());
  $cw_class_add_fields = array();

  if (!empty($cw_class_extra_fields) && is_array($cw_class_extra_fields)) {
    // Some id are restricted to the plugin usage
    $restricted = array(
      'title'       => true,
      'venue'       => true,
      'type'        => true,
      'description' => true,
      'duration'    => true,
      'privacy'     => true,
      'utcoffset'   => true,
    );

    foreach ($cw_class_extra_fields as $cw_class_extra_field) {
      // The id is required and some ids are restricted.
      if (empty($cw_class_extra_field['id']) || !empty($restricted[$cw_class_extra_field['id']])) {
        continue;
      }

      // Make sure all needed arguments have default values
      $cw_class_add_fields[] = wp_parse_args($cw_class_extra_field, array(
        'id'          => '',
        'order'       => 60,
        'type'        => 'text',
        'placeholder' => '',
        'label'       => '',
        'value'       => '',
        'tab'         => 'what',
        'class'       => ''
      ));
    }
  }

  if (!empty($cw_class_add_fields)) {
    $cw_class_fields['what'] = array_merge($cw_class_fields['what'], $cw_class_add_fields);
  }

  // Sort by the order key
  $cw_class_fields['what'] = bp_sort_by_key($cw_class_fields['what'], 'order', 'num');

  $cw_class_date_strings = array(
    'daynames'    => array(
      esc_html__('Sunday', 'cw_class'),
      esc_html__('Monday', 'cw_class'),
      esc_html__('Tuesday', 'cw_class'),
      esc_html__('Wednesday', 'cw_class'),
      esc_html__('Thursday', 'cw_class'),
      esc_html__('Friday', 'cw_class'),
      esc_html__('Saturday', 'cw_class'),
    ),
    'daynamesmin' => array(
      esc_html__('Su', 'cw_class'),
      esc_html__('Mo', 'cw_class'),
      esc_html__('Tu', 'cw_class'),
      esc_html__('We', 'cw_class'),
      esc_html__('Th', 'cw_class'),
      esc_html__('Fr', 'cw_class'),
      esc_html__('Sa', 'cw_class'),
    ),
    'monthnames'  => array(
      esc_html__('January', 'cw_class'),
      esc_html__('February', 'cw_class'),
      esc_html__('March', 'cw_class'),
      esc_html__('April', 'cw_class'),
      esc_html__('May', 'cw_class'),
      esc_html__('June', 'cw_class'),
      esc_html__('July', 'cw_class'),
      esc_html__('August', 'cw_class'),
      esc_html__('September', 'cw_class'),
      esc_html__('October', 'cw_class'),
      esc_html__('November', 'cw_class'),
      esc_html__('December', 'cw_class'),
    ),
    'format'      => _x('mm/dd/yy', 'cw_class date format', 'cw_class'),
    'firstday'    => intval(bp_get_option('start_of_week', 0)),
    'alert'       => esc_html__('You already selected this date', 'cw_class')
  );

  $settings = apply_filters('media_view_settings', $settings, $post);
  $strings  = apply_filters('media_view_strings',  $strings,  $post);
  $strings = array_merge($strings, array(
    'cw_class_strings'      => $cw_class_strings,
    'cw_class_fields'       => $cw_class_fields,
    'cw_class_date_strings' => $cw_class_date_strings
  ));

  $strings['settings'] = $settings;

  wp_localize_script('cw_class-media-views', '_wpMediaViewsL10n', $strings);

  wp_enqueue_script('cw_class-modal');
  wp_enqueue_style('cw_class-modal-style');
  cw_class_plupload_settings();

  require_once ABSPATH . WPINC . '/media-template.php';
  add_action('admin_footer', 'wp_print_media_templates');
  add_action('wp_footer', 'wp_print_media_templates');

  do_action('cw_class_enqueue_editor');
}

/**
 * Trick to make the media-views works without plupload loaded
 *
 * @package CW Class
 * @subpackage Editor
 * @since CW Class (1.0.0)
 *
 * @global $wp_scripts
 */
function cw_class_plupload_settings()
{
  global $wp_scripts;

  $data = $wp_scripts->get_data('cw_class-plupload', 'data');

  if ($data && false !== strpos($data, '_wpPluploadSettings'))
    return;

  $settings = array(
    'defaults' => array(),
    'browser'  => array(
      'mobile'    => false,
      'supported' => false,
    ),
    'limitExceeded' => false
  );

  $script = 'var _wpPluploadSettings = ' . json_encode($settings) . ';';

  if ($data)
    $script = "$data\n$script";

  $wp_scripts->add_data('cw_class-plupload', 'data', $script);
}


/**
 * The template needed for the CW Class editor
 *
 * @package CW Class
 * @subpackage Editor
 * @since CW Class (1.0.0)
 */
function rendezvous_media_templates()
{
?>
  <script type="text/html" id="tmpl-what">
    <# if ( 'text'===data.type ) { #>
      <p>
        <label for="{{data.id}}">{{data.label}}</label>
        <input type="text" id="{{data.id}}" placeholder="{{data.placeholder}}" value="{{data.value}}" class="rdv-input-what {{data.class}}" />
      </p>
      <# } else if ( 'time'===data.type ) { #>
        <p>
          <label for="{{data.id}}">{{data.label}}</label>
          <input type="time" id="{{data.id}}" placeholder="{{data.placeholder}}" value="{{data.value}}" class="rdv-input-what {{data.class}}" />
        </p>
        <# } else if ( 'duree'===data.type ) { #>
          <p>
            <label for="{{data.id}}">{{data.label}}</label>
            <input type="text" id="{{data.id}}" placeholder="{{data.placeholder}}" value="{{data.value}}" class="rdv-input-what duree {{data.class}}" />
          </p>
          <# } else if ( 'checkbox'===data.type ) { #>
            <p>
              <label for="{{data.id}}">{{data.label}} </label>
              <input type="checkbox" id="{{data.id}}" value="1" class="rdv-check-what {{data.class}}" <# if ( data.value==1 ) { #>checked<# } #>/> {{data.placeholder}}
            </p>
            <# } else if ( 'timezone'===data.type || 'hidden'===data.type ) { #>
              <input type="hidden" id="{{data.id}}" value="{{data.value}}" class="rdv-hidden-what" />
              <# } else if ( 'textarea'===data.type ) { #>
                <p>
                  <label for="{{data.id}}">{{data.label}}</label>
                  <textarea id="{{data.id}}" placeholder="{{data.placeholder}}" class="rdv-input-what {{data.class}}">{{data.value}}</textarea>
                </p>

                <# } else if ( 'selectbox'===data.type ) { #>

                  <# if ( typeof data.placeholder=='object' && typeof data.choices=='object' ) { #>

                    <p>
                      <label for="{{data.id}}">{{data.label}} </label>
                      <select id="{{data.id}}" class="rdv-select-what">
                        <option value="">---</option>
                        <# for ( i in data.placeholder ) { #>
                          <option value="{{data.choices[i]}}" <# if ( data.value==data.choices[i] ) { #>selected<# } #>>{{data.placeholder[i]}}</option>
                          <# } #>
                      </select>
                    </p>

                    <# } #>

                      <# } else { #>
                        <strong>Oops</strong>
                        <# } #>
  </script>

  <script type="text/html" id="tmpl-when">
    <# if ( 1===data.intro ) { #>
      <div class="use-calendar">
        <h3 class="calendar-instructions"><?php esc_html_e('Use the calendar in the right sidebar to add dates', 'cw_class'); ?></h3>
      </div>
      <# } else { #>
        <fieldset>
          <legend class="dayth">
            <a href="#" class="trashday"><span data-id="{{data.id}}"></span></a> <strong>{{data.day}}</strong>
          </legend>
          <div class="daytd">
            <label for="{{data.id}}-hour1"><?php esc_html_e('Define 1 to 3 hours for this day, please respect the format HH:MM', 'cw_class'); ?></label>

            <!-- <input type="time" value="{{data.hour1}}" id="{{data.id}}-hour1" placeholder="00:00" class="rdv-input-when">&nbsp;
						<input type="time" value="{{data.hour2}}" id="{{data.id}}-hour2" placeholder="00:00" class="rdv-input-when">&nbsp;
						<input type="time" value="{{data.hour3}}" id="{{data.id}}-hour3" placeholder="00:00" class="rdv-input-when">&nbsp; -->
            <div style="display: flex; flex-direction: row; justify-items: space-between;">
              <input type="time" value="{{data.hour1}}" id="{{data.id}}-hour1" placeholder="00:00" class="rdv-input-when">
              <!-- &nbsp; -->
              <!-- <input type="time" value="{{data.hour2}}" id="{{data.id}}-hour2" placeholder="00:00" class="rdv-input-when">&nbsp;
							<input type="time" value="{{data.hour3}}" id="{{data.id}}-hour3" placeholder="00:00" class="rdv-input-when">&nbsp; -->
            </div>
          </div>
        </fieldset>
        <# } #>
  </script>

  <script type="text/html" id="tmpl-when-add-slot">
    <input type="time" value="{{data.hour}}" id="{{data.id}}-hour3" placeholder="00:00" class="rdv-input-when">&nbsp;
  </script>

  <script type="text/html" id="tmpl-cw_class">
    <# if ( 1===data.notfound ) { #>
      <div id="cw_class-error">
        <p><?php _e('No users found', 'cw_class'); ?></p>
      </div>
      <# } else { #>
        <div id="user-{{ data.id }}" class="attachment-preview user type-image" data-id="{{ data.id }}">
          <div class="thumbnail">
            <div class="avatar">
              <img src="{{data.avatar}}" draggable="false" />
            </div>
            <div class="displayname">
              <strong>{{data.name}}</strong>
            </div>
          </div>
        </div>
        <a id="user-check-{{ data.id }}" class="check" href="#" title="<?php _e('Deselect', 'cw_class'); ?>" data-id="{{ data.id }}">
          <div class="media-modal-icon"></div>
        </a>
        <# } #>
  </script>

  <script type="text/html" id="tmpl-user-selection">
    <div class="selection-info">
      <span class="count"></span>
      <# if ( data.clearable ) { #>
        <a class="clear-selection" href="#"><?php _e('Clear', 'cw_class'); ?></a>
        <# } #>
    </div>
    <div class="selection-view">
      <ul></ul>
    </div>
  </script>
<?php
}
add_action('print_media_templates', 'rendezvous_media_templates');
