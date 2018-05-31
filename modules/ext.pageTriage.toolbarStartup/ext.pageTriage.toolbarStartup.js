jQuery( function ( $ ) {
	var modules;
	// only show curation toolbar for enabled namespaces
	if ( $.inArray( mw.config.get( 'wgNamespaceNumber' ),
		mw.config.get( 'pageTriageNamespaces' ) ) === -1 ) {
		return true;
	}

	// Load the curation toolbar
	mw.loader.load( 'ext.pageTriage.views.toolbar' );

	// If the WikiLove module is activated, load WikiLove as well
	modules = mw.config.get( 'wgPageTriageCurationModules' );
	if ( typeof modules.wikiLove !== 'undefined' ) {
		mw.loader.load( 'ext.wikiLove.init' );
	}
} );
