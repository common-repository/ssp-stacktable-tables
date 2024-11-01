(function( $ ) {
	'use strict';

	$(function() {

		let sspParams = document.getElementById('ssp-stacktable-params');
		let stType = 'stacktable';
		let stClass = '';
		let stIndex = 0;
		let showHeader = true;
		let options = {};
		let tables = $('table');
		if(typeof(sspParams) != 'undefined' && sspParams != null) {
			stType = $(sspParams).data('type');
			stClass = $(sspParams).data('class');
			stIndex = $(sspParams).data('headindex');
			if ('no' === $(sspParams).data('showheader')) {
				showHeader = false;
			}
		}
		if('' !== stClass) {
			options.myClass = stClass;
		}
		if(stIndex > 0) {
			options.headIndex = stIndex;
		}
		if(false === showHeader) {
			options.displayHeader = false;
		}
		switch (stType) {
			case "stacktable":
				tables.stacktable(options);
				break;
			case "cardtable":
				tables.cardtable(options);
				break;
			case "stackcolumns":
				tables.stackcolumns(options);
				break;
		}

	});

})( jQuery );
