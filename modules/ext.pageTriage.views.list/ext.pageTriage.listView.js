$( function() {
	// view for the article list

	// create an event aggregator
	var eventBus = _.extend( {}, Backbone.Events );

	// instantiate the collection of articles
	var articles = new mw.pageTriage.ArticleList( { eventBus: eventBus } );

	// grab pageTriage statistics
	var stats = new mw.pageTriage.Stats( { eventBus: eventBus } );

	// overall list view
	// currently, this is the main application view.
	mw.pageTriage.ListView = Backbone.View.extend( {

		initialize: function( options ) {
			this.eventBus = options.eventBus; // access the eventBus
			this.position = 0;

			// these events are triggered when items are added to the articles collection
			articles.bind( 'add', this.addOne, this );
			articles.bind( 'reset', this.addAll, this );
			stats.bind( 'change', this.addStats, this );

			// this event is triggered when the collection finishes loading.
			//articles.bind( 'all', this.render, this );

			// set up mechanism for loading more articles
			if ( mw.config.get( 'wgPageTriageInfiniteScrolling' ) ) {
				// replace more link with spinner in hidden div since we'll never need the more link
				$( '#mwe-pt-list-more' ).empty();
				$( '#mwe-pt-list-more' ).append( $.createSpinner( 'more-spinner' ) );
			} else {
				// bind manualLoadMore function to 'More' link
				var _this = this;
				$( '#mwe-pt-list-more-link' ).click( function() {
					_this.manualLoadMore();
					return false;
				} );
			}

			// Add a warning if we're using an old version version of Explorer
			if ( $.browser.msie && parseInt( $.browser.version ) < 8 ) {
				$( '#mwe-pt-list-warnings' ).append( '<div>'+mw.msg( 'pagetriage-warning-browser' )+'</div>' );
			}
			// Display whatever warnings we have
			$( '#mwe-pt-list-warnings' ).show();

			// on init, make sure to load the contents of the collection.
			articles.fetch();
			stats.fetch();
		},

		render: function() {
			// reset the position indicator
			this.position = 0;
			
			var controlNav = new mw.pageTriage.ListControlNav( { eventBus: this.eventBus, model: articles } );
			controlNav.render();
		},

		// load more method for infinite scrolling
		automaticLoadMore: function() {
			var _this = this;
			$( '#mwe-pt-list-load-more-anchor' ).waypoint( 'destroy' );
			$( '#mwe-pt-list-more' ).show(); // show spinner
			
			// set the offsets for the page fetch
			var lastArticle = articles.last(1);
			if( 0 in lastArticle ) {
				articles.apiParams.offset = lastArticle[0].attributes.creation_date;
				articles.apiParams.pageoffset = lastArticle[0].attributes.pageid;
			} else {
				articles.apiParams.offset = 0;
				articles.apiParams.pageoffset = 0;
			}
			
			// fetch more articles. we use a timeout to prevent double-loading.
			setTimeout(
				function() {
					// get another batch of articles
					articles.fetch( {
						add: true,
						success: function() {
							$( '#mwe-pt-list-more' ).hide(); // hide spinner
							$.waypoints( 'refresh' );
							_this.eventBus.trigger( "articleListChange" );
							if ( articles.moreToLoad ) {
								// create a new waypoint
								_this.createNewLoadMoreWaypoint();
							}
						}
					} );
				},
				100
			);
		},

		// insert a new waypoint for automatically loading more pages
		createNewLoadMoreWaypoint: function() {
			var _this = this;
			var opts = { offset: '100%' };
			$( '#mwe-pt-list-load-more-anchor' ).waypoint( function( event, direction ) {
				if ( direction == 'down' ) {
					_this.automaticLoadMore();
				}
				event.stopPropagation();
			}, opts );
		},

		// manual load more method (i.e. infinite scrolling turned off)
		manualLoadMore: function() {
			var _this = this;
			$( '#mwe-pt-list-more-link' ).hide();
			$( '#mwe-pt-list-more' ).append( $.createSpinner( 'more-spinner' ) );
			
			// set the offsets for the page fetch
			var lastArticle = articles.last(1);
			if( 0 in lastArticle ) {
				articles.apiParams.offset = lastArticle[0].attributes.creation_date;
				articles.apiParams.pageoffset = lastArticle[0].attributes.pageid;
			} else {
				articles.apiParams.offset = 0;
				articles.apiParams.pageoffset = 0;
			}

			articles.fetch( {
				add: true,
				success: function() {
					$.removeSpinner( 'more-spinner' );
					$( '#mwe-pt-list-more-link' ).show();
					if ( articles.moreToLoad ) {
						$( '#mwe-pt-list-more' ).show(); // show 'More' link
					} else {
						$( '#mwe-pt-list-more' ).hide(); // hide 'More' link
					}
					$.waypoints( 'refresh' );
					_this.eventBus.trigger( "articleListChange" );
				}
			} );
		},

		// add stats data to the navigation
		addStats: function( stats ) {
			var statsNav = new mw.pageTriage.ListStatsNav( { eventBus: this.eventBus, model: stats } );
			statsNav.render();
		},

		// add a single article to the list
		addOne: function( article ) {
			// define position, for making alternating background colors.
			// this is added at the last minute, so it gets updated when the sort changes.
			if( !this.position ) {
				this.position = 0;
			}
			this.position++;
			article.set( 'position', this.position );
			// pass in the specific article instance
			var view = new mw.pageTriage.ListItem( { eventBus: this.eventBus, model: article } );
			$( "#mwe-pt-list-view" ).append( view.render().el );
			$( ".mwe-pt-list-triage-button" ).button({
				label: mw.msg( 'pagetriage-triage' ),
				icons: { primary:'ui-icon-search' }
			});
		},

		// add all the items in the articles collection
		// this only gets executed when the article collection list is reset
		addAll: function() {

			// reset current position in the collection
			this.position = 0;

			// remove load more waypoint if it exists
			$( '#mwe-pt-list-load-more-anchor' ).waypoint( 'destroy' );

			// remove the spinner/wait message and any previously displayed articles before loading
			// new articles
			$( '#mwe-pt-list-view' ).empty();

			// remove any error messages and hide the div that contains them
			$( '#mwe-pt-list-errors' ).empty();
			$( '#mwe-pt-list-errors' ).hide();

			// hide the 'More' div if it is visible
			$( '#mwe-pt-list-more' ).hide();

			if ( articles.length ) {
				// load the new articles
				articles.forEach( this.addOne, this );
				if ( articles.moreToLoad ) {
					if ( mw.config.get( 'wgPageTriageInfiniteScrolling' ) ) {
						this.createNewLoadMoreWaypoint(); // create a new waypoint
					} else {
						$( '#mwe-pt-list-more' ).show(); // show 'More' link
					}
				}
			} else {
				// show an error message
				$( '#mwe-pt-list-errors' ).html( mw.msg( 'pagetriage-no-pages' ) );
				$( '#mwe-pt-list-errors' ).show();
			}
			
			// show the stats bar at the bottom
			$( '#mwe-pt-list-stats-nav' ).show();

			// refresh our waypoints since we've changed the DOM
			$.waypoints( 'refresh' );

			this.eventBus.trigger( 'articleListChange' );
	    }

	} );

	// create an instance of the list view, which makes everything go.
	var list = new mw.pageTriage.ListView( { eventBus: eventBus } );
	list.render();
} );
