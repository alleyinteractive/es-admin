jQuery( function( $ ) {
	$( '.es-admin-facet-section input:checkbox' ).click( function() {
		$( this ).closest( 'form' ).submit();
	});

	$( '#es-admin-filter-results' ).remove();
});