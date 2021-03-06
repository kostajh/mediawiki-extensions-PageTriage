// Move to the next page

$( function () {
	var
		// create an event aggregator
		eventBus = _.extend( {}, Backbone.Events ),
		// instantiate the collection of articles
		nextArticles = new mw.pageTriage.ArticleList( { eventBus: eventBus } );

	mw.pageTriage.NextView = mw.pageTriage.ToolView.extend( {
		id: 'mwe-pt-next',
		icon: 'icon_skip.png', // the default icon
		tooltip: 'pagetriage-next-tooltip',

		apiParams: nextArticles.apiParams,

		initialize: function ( options ) {
			this.eventBus = options.eventBus;
		},

		setParams: function () {
			// these settings are not overwritable
			this.apiParams.limit = 1;
			this.apiParams.action = 'pagetriagelist';
			this.apiParams.offset = this.model.get( 'creation_date_utc' );
			this.apiParams.pageoffset = this.model.get( 'pageid' );
		},

		click: function () {
			var page, that = this;

			// find the next page.
			this.eventBus.trigger( 'showTool', this );

			// set the parameters for retrieving the next article
			this.setParams();

			// attempt to get the next page
			new mw.Api().get( this.apiParams )
				.done( function ( result ) {
					var url;
					if (
						result.pagetriagelist && result.pagetriagelist.result === 'success' && result.pagetriagelist.pages[ 0 ]
					) {
						page = result.pagetriagelist.pages[ 0 ];
						if ( page.title ) {
							url = new mw.Uri( mw.config.get( 'wgArticlePath' ).replace(
								'$1', mw.util.wikiUrlencode( page.title )
							) );
							if ( page.is_redirect === '1' ) {
								url.query.redirect = 'no';
							}
							url.query.showcurationtoolbar = 1;
							window.location.href = url.toString();
						} else {
							that.disable();
						}
					} else {
						that.disable();
					}
				} )
				.fail( function () {
					that.disable();
				} );
		}
	} );

} );
