jQuery( function( $ ) {
	$( '.es-admin-facet-section input:checkbox' ).click( function() {
		$( this ).closest( 'form' ).submit();
	});

	$( '#es-admin-filter-results' ).remove();

	$( '.es-admin-checkall' ).click( function() {
		var brand = $( this ).val();
		var checked = $( this ).prop("checked");
		$( '.' + brand ).each( function() {
			$( this).prop( 'checked', checked );
		});
	});
});