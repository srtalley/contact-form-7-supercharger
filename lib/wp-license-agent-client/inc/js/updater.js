// v1.5.5
jQuery(function($) {
    // Hook into the "notice-wpla" class we added to the notice to dismiss
    // those particular messages
    $( document ).on( 'click', '.notice-wpla .notice-dismiss', function () {
        // Read the "data-notice" information to track which notice
        // is being dismissed and send it via AJAX
        var update_slug = $( this ).closest( '.notice-wpla' ).data( 'update-slug' );
        var type = update_slug + $( this ).closest( '.notice-wpla' ).data( 'type' );
        // Make an AJAX call
        // Since WP 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        $.ajax( ajaxurl,
          {
            type: 'POST',
            data: {
              action: 'wpla_dismiss_admin_notice-' + update_slug,
              type: type,
            }
          } );
      } );
});