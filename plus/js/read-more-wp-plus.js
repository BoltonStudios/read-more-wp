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
    });

})( jQuery );

// Define the onclick action event for the Read More button.
function rmwpPlusToggleElements( textToggle, buttonWrap, buttonToggle, buttonToggleText, moreLabel, lessLabel, ellipsis ){

    // Toggle the text wrapper's "open" class.
    textToggle.toggleClass( 'open' );

    // Toggle the button's "open" class.
    buttonToggle.toggleClass( 'open' );

    // If the button text says 'Read More'...
    if( buttonToggleText == moreLabel ){

        // Change the button text to 'Read Less'.
        jQuery( buttonToggle ).text( lessLabel )

        // If the textToggle is a pop-up...
        if( textToggle.hasClass( 'animation-popUp' ) ){

            // Add the read-less button within the toggle text element (i.e., the pop-up window).
            textToggle.prepend( buttonWrap );

        } else{

            // Move button to the end of the toggled text.
            textToggle.next( '.rmwp-toggle-end' ).append( buttonWrap );
        }

    } else{

        // Otherwise, change the button text to 'Read More'.
        jQuery( buttonToggle ).text( moreLabel )

        // Move button to the toggled text break point.
        buttonWrap.insertAfter( ellipsis );
    }

    // If the textToggle lacks the "animation-none" class (i.e., it is animated)...
    if( !textToggle.hasClass( 'animation-none' ) && !textToggle.hasClass( 'animation-' ) ){
        
        // Fade the button back in.
        buttonToggle.fadeToggle();
    }
}

// Define the onclick action event for the Read More button.
function rmwpPlusButtonAction( rmwpID, moreLabel, lessLabel, speed ){

    // Define targets.
    var textToggle          = jQuery( '#rmwp-toggle-' + rmwpID );
    var ellipsis            = jQuery( '#ellipsis-' + rmwpID );
    var buttonWrap          = jQuery( '#rmwp-button-wrap-' + rmwpID );
    var buttonToggle        = buttonWrap.children( "button" );
    var buttonToggleText    = buttonToggle.text();

    // Handle the animations.
    if( textToggle.hasClass( 'animation-slide' ) == true ){

        // Handle the slide animation.
        
        // Toggle the ellipsis visibility with the fade animation.
        ellipsis.fadeToggle( 300 );

        // Toggle the button visibility with the fade animation.
        buttonToggle.fadeToggle( 300, function() {

            // Toggle the text visibility with the slide animation.
            jQuery( textToggle ).slideToggle( 
                speed, // Speed
                rmwpPlusToggleElements(
                    textToggle,
                    buttonWrap,
                    buttonToggle,
                    buttonToggleText,
                    moreLabel,
                    lessLabel,
                    ellipsis
                ) // Animation complete.
            );
        });

    } else if( textToggle.hasClass( 'animation-fade' ) == true ){

        // Handle the fade animation.

        // Toggle the ellipsis visibility with the fade animation.
        ellipsis.fadeToggle( 300 );

        // Toggle the button visibility with the fade animation.
        buttonToggle.fadeToggle( 300, function() {

            // Toggle the text visibility with the slide animation.
            jQuery( textToggle ).fadeToggle( 
                speed, // Speed
                rmwpPlusToggleElements(
                    textToggle,
                    buttonWrap,
                    buttonToggle,
                    buttonToggleText,
                    moreLabel,
                    lessLabel,
                    ellipsis
                ) // Animation complete.
            );
        });   
    } else if( textToggle.hasClass( 'animation-fold' ) == true ){

        // Handle the fold animation.

        // Toggle the ellipsis visibility with the fade animation.
        ellipsis.fadeToggle( 300 );

        // Toggle the button visibility with the fade animation.
        buttonToggle.fadeToggle( 300, function(){

            // Toggle the text visibility with the fold animation.
            jQuery( textToggle ).toggle( 
                speed, // Speed
                rmwpPlusToggleElements(
                    textToggle,
                    buttonWrap,
                    buttonToggle,
                    buttonToggleText,
                    moreLabel,
                    lessLabel,
                    ellipsis
                ) // Animation complete.
            );
        });   
    } else if( textToggle.hasClass( 'animation-popUp' ) == true ){

        // Handle the pop-up animation.

        // Toggle the ellipsis visibility with the fade animation.
        ellipsis.fadeToggle( 300 );

        // Toggle the button visibility with the fade animation.
        buttonToggle.fadeToggle( 300, function(){

            // If the toggled text is "open" (expanded)...
            if( textToggle.hasClass( 'open' ) ){

                // Fade out the pop-up background overlay.
                jQuery( '#rmwp-animation-popUp-overlay-' + rmwpID ).fadeOut();

                // Toggle the pop-up element off instantly.
                jQuery( textToggle ).toggle( 
                    0, // Speed
                    rmwpPlusToggleElements(
                        textToggle,
                        buttonWrap,
                        buttonToggle,
                        buttonToggleText,
                        moreLabel,
                        lessLabel,
                        ellipsis
                    ) // Animation complete.
                );
            } else {

                // Fade in the pop-up background overlay.
                jQuery( '#rmwp-animation-popUp-overlay-' + rmwpID ).fadeIn( speed, function(){
                    
                    // Toggle the element with a fade animation at the user-selected speed.
                    jQuery( textToggle ).fadeIn( 
                        speed, // Speed
                        rmwpPlusToggleElements(
                            textToggle,
                            buttonWrap,
                            buttonToggle,
                            buttonToggleText,
                            moreLabel,
                            lessLabel,
                            ellipsis
                        ) // Animation complete.
                    );
                });
            }
        });   
    } else {

        // If the textToggle is not animated...

        // Toggle the text visibility.
        jQuery( textToggle ).toggle();

        // Toggle the ellipsis visibility with the fade animation.
        ellipsis.toggle();

        // Toggle the other elements.
        rmwpPlusToggleElements(
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
