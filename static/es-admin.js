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

	$( '.es-admin-site-section .additional_blog_ids' ).click( function() {
		brand = $( this ).attr('class').replace( 'additional_blog_ids ', '');
		checkall = true;
		$( '.' + brand ).each( function() {
			if ( ! $( this).prop( 'checked' ) ) {
				checkall = false;
			}
			$( 'input[value="' + brand + '"]').prop('checked', checkall);
		});
	});
});