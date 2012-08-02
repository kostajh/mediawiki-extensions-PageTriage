// Revision represents a single revision (aka historyItem)
// RevisionList is a collection of revisions for a single page
//
$( function() {
	if ( !mw.pageTriage ) {
		// make sure this object exists, since this might be run first.
		mw.pageTriage = {};
	}

	// sparse model because events don't work well when nesting these.
	mw.pageTriage.Revision = Backbone.Model.extend( {} );

	mw.pageTriage.RevisionList = Backbone.Collection.extend( {
		model: mw.pageTriage.Revision,

		apiParams: {
			rvprop: 'timestamp|user|parsedcomment|ids',
			rvlimit: 5
		},

		initialize: function( options ) {
			this.eventBus = options.eventBus;
			this.pageId = options.pageId;
			this.setParam( 'pageids', options.pageId ); // pass this to the api
		},

		url: function() {
			var url = mw.util.wikiScript( 'api' ) + '?action=query&prop=revisions&format=json&' + $.param( this.apiParams );
			return url;
		},

		parse: function( response ) {
			// extract the useful bits of json.
			return response.query.pages[this.pageId].revisions;
		},

		setParams: function( apiParams ) {
			this.apiParams = apiParams;
		},

		setParam: function( paramName, paramValue ) {
			this.apiParams[paramName] = paramValue;
		},

		getParam: function( key ) {
			return this.apiParams[key];
		}

	} );

} );
