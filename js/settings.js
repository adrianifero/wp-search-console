( function () {
	'use strict';

	var select = document.getElementById( 'at-sc-resource-id' );
	var custom = document.getElementById( 'at-sc-custom-resource-id' );
	var testLink = document.getElementById( 'at-sc-test-property' );
	var preview = document.getElementById( 'at-sc-preview-resource-id' );
	var advancedRow = document.getElementById( 'at-sc-advanced-row' );
	var advancedToggle = document.getElementById( 'at-sc-advanced-toggle' );
	var base = window.atSearchConsoleSettings && window.atSearchConsoleSettings.performanceBase;

	if ( ! base || ! select || ! testLink ) {
		return;
	}

	function effectiveResourceId() {
		var customValue = custom && custom.value ? custom.value.trim() : '';
		if ( customValue ) {
			return customValue;
		}
		return select.value || '';
	}

	function buildTestUrl( resourceId ) {
		var url = new URL( base );
		url.searchParams.set( 'resource_id', resourceId );
		url.searchParams.set( 'metrics', 'CLICKS,IMPRESSIONS,CTR,POSITION' );
		url.searchParams.set( 'num_of_months', '16' );
		return url.toString();
	}

	function update() {
		var resourceId = effectiveResourceId();
		if ( preview ) {
			preview.textContent = resourceId;
		}
		if ( resourceId ) {
			testLink.href = buildTestUrl( resourceId );
		}
	}

	function setAdvancedOpen( open ) {
		if ( ! advancedRow || ! advancedToggle ) {
			return;
		}
		advancedRow.style.display = open ? '' : 'none';
		advancedToggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		advancedToggle.textContent = open ? 'Advanced \u00ab' : 'Advanced \u00bb';
	}

	if ( advancedRow && advancedToggle ) {
		setAdvancedOpen( custom && custom.value ? !! custom.value.trim() : false );
		advancedToggle.addEventListener( 'click', function () {
			setAdvancedOpen( advancedRow.style.display === 'none' );
		} );
	}

	select.addEventListener( 'change', update );
	if ( custom ) {
		custom.addEventListener( 'input', update );
	}
	update();
}() );
