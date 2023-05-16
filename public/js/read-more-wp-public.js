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
function rmwpToggleReadMore( rmwpID, moreLabel, lessLabel ){

    // Define targets.
    var textToggle          = jQuery( '#rmwp-toggle-' + rmwpID );
    var ellipsis            = jQuery( '#ellipsis-' + rmwpID );
    var buttonWrap          = jQuery( '#rmwp-button-wrap-' + rmwpID );
    var buttonToggle        = buttonWrap.children( "button" );
    var buttonToggleText    = buttonToggle.text();

    // Get the element, i.e., the review text, associated with the clicked button.
    jQuery( textToggle ).each( function(){

        //
        if( jQuery( this ).hasClass( 'accordion' ) == true ){
            
            // Toggle the ellipsis visibility.
            ellipsis.fadeToggle( 300 );

            //
            buttonToggle.fadeToggle( 300, function() {

                //
                jQuery( textToggle ).slideToggle( 700, function() {
                    // Animation complete.

                    // Toggle the text wrapper's "open" class.
                    jQuery( this ).toggleClass( 'open' );
            
                    // Toggle the button's "open" class.
                    buttonToggle.toggleClass( 'open' );

                    // If the button text says "Continue"...
                    if( buttonToggleText == moreLabel ){

                        // Change the button text to "Less".
                        jQuery( buttonToggle ).text( lessLabel )

                        // Move button to the end of the toggled text.
                        buttonWrap.insertAfter( textToggle.next( '.rmwp-toggle-end' ) );

                    } else{

                        // Otherwise, change the button text to "More".
                        jQuery( buttonToggle ).text( moreLabel )

                        // Move button to the toggled text break point.
                        buttonWrap.insertAfter( ellipsis );
                    }

                    //
                    buttonToggle.fadeToggle( 300 );
                });
            });

        } else if( jQuery( this ).hasClass( 'fade' ) == true ){
            
            // Toggle the ellipsis visibility.
            ellipsis.fadeToggle( 200 );

            //
            buttonToggle.fadeToggle( 300, function() {

                //
                jQuery( textToggle ).fadeToggle( 700, function() {
                    // Animation complete.

                    // Toggle the text wrapper's "open" class.
                    jQuery( this ).toggleClass( 'open' );
            
                    // Toggle the button's "open" class.
                    buttonToggle.toggleClass( 'open' );

                    // If the button text says "Continue"...
                    if( buttonToggleText == moreLabel ){

                        // Change the button text to "Less".
                        jQuery( buttonToggle ).text( lessLabel )

                        // Move button to the end of the toggled text.
                        buttonWrap.insertAfter( textToggle.next( '.rmwp-toggle-end' ) );

                    } else{

                        // Otherwise, change the button text to "More".
                        jQuery( buttonToggle ).text( moreLabel )

                        // Move button to the toggled text break point.
                        buttonWrap.insertAfter( ellipsis );
                    }

                    //
                    buttonToggle.fadeToggle( 300 );
                });
            });
            
        } else{
    
            // Toggle its visibility.
            jQuery( textToggle ).toggle();

            // Toggle the text wrapper's "open" class.
            jQuery( this ).toggleClass( 'open' );

            // Toggle the button's "open" class.
            buttonToggle.toggleClass( 'open' );

            // If the button text says "Continue"...
            if( buttonToggleText == moreLabel ){

                // Change the button text to "Less".
                jQuery( buttonToggle ).text( lessLabel )

                // Move button to the end of the toggled text.
                buttonWrap.insertAfter( textToggle.next( '.rmwp-toggle-end' ) );

            } else{

                // Otherwise, change the button text to "More".
                jQuery( buttonToggle ).text( moreLabel )

                // Move button to the toggled text break point.
                buttonWrap.insertAfter( ellipsis );
            }
        }
    });
}
