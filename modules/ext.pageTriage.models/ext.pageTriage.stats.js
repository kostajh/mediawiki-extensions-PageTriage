// Stats represents the dashboard data for pagetriage
$( function() {
	if ( !mw.pageTriage ) {
		mw.pageTriage = {};
	}
	mw.pageTriage.Stats = Backbone.Model.extend( {
		defaults: {
			title: 'PageTriage Dashboard Data',
			pageid: ''
		},
		
		initialize: function() {
			this.bind( 'change', this.formatMetadata, this );
		},
		
		formatMetadata: function ( stats ) {
			stats.set( 'ptr_untriaged_article_count', stats.get( 'untriagedarticle' )['count'] );
			stats.set( 'ptrAverage', 
					stats.get( 'untriagedarticle' )['age-50th-percentile'] ? 
					stats.get( 'untriagedarticle' )['age-50th-percentile'] : '' );
			stats.set( 'ptrOldest', 
					stats.get( 'untriagedarticle' )['age-100th-percentile'] ? 
					stats.get( 'untriagedarticle' )['age-100th-percentile'] : '' );
		},
		url: mw.util.wikiScript( 'api' ) + '?action=pagetriagestats&format=json',

		parse: function( response ) {
			// extract the useful bits of json.
			return response.pagetriagestats.stats;
		}
	} );
} );