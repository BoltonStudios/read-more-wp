(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

    // Check if the document is ready.
    $( function() {

        // For each "read more" element, i.e., review...
        $( ".rmwp-button-wrap" ).each( function(){

            // Display the Read More button.
            $( this ).show();
        });

        // For each review (toggle element).
        $( ".rmwp-toggle" ).each( function(){

            // Hide the non-excerpt text.
            $( this ).hide();
        });
    });

})( jQuery );

// Define the onclick action event for the Read More button.
function rmwpToggleElements( textToggle, buttonWrap, buttonToggle, buttonToggleText, moreLabel, lessLabel, ellipsis ){

    // Toggle the text wrapper's "open" class.
    textToggle.toggleClass( 'open' );

    // Toggle the button's "open" class.
    buttonToggle.toggleClass( 'open' );

    // If the button text says 'Read More'...
    if( buttonToggleText == moreLabel ){

        // Change the button text to 'Read Less'.
        jQuery( buttonToggle ).text( lessLabel )

        // Move button to the end of the toggled text.
        buttonWrap.insertAfter( textToggle.next( '.rmwp-toggle-end' ) );

    } else{

        // Otherwise, change the button text to 'Read More'.
        jQuery( buttonToggle ).text( moreLabel )

        // Move button to the toggled text break point.
        buttonWrap.insertAfter( ellipsis );
    }

    // If the textToggle lacks the "animation-none" class (i.e., it is animated)...
    if( !textToggle.hasClass( 'animation-none' ) ){

        // Fade the button back in.
        buttonToggle.fadeToggle( 300 );
    }
}

// Define the onclick action event for the Read More button.
function rmwpButtonAction( rmwpID, moreLabel, lessLabel ){

    // Define targets.
    var textToggle          = jQuery( '#rmwp-toggle-' + rmwpID );
    var ellipsis            = jQuery( '#ellipsis-' + rmwpID );
    var buttonWrap          = jQuery( '#rmwp-button-wrap-' + rmwpID );
    var buttonToggle        = buttonWrap.children( "button" );
    var buttonToggleText    = buttonToggle.text();

     // If the textToggle has the "animation-none" class (i.e., it is not animated)...
     if( !textToggle.hasClass( 'animation-none' ) ){

        // Toggle the text visibility.
        jQuery( textToggle ).toggle();

        // Toggle the other elements.
        rmwpToggleElements(
            textToggle,
            buttonWrap,
            buttonToggle,
            buttonToggleText,
            moreLabel,
            lessLabel,
            ellipsis
        )
    }
}
