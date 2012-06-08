$( function() {
	// view for the floating toolbar

	// create an event aggregator
	var eventBus = _.extend( {}, Backbone.Events );

	// the current article
	var article = new mw.pageTriage.Article( {
		eventBus: eventBus,
		pageId: mw.config.get( 'wgArticleId' ),
		includeHistory: true
	} );
	article.fetch();

	// array of tool instances
	var tools;

	// overall toolbar view
	// currently, this is the main application view.
	mw.pageTriage.ToolbarView = Backbone.View.extend( {
		template: mw.pageTriage.viewUtil.template( { 'view': 'toolbar', 'template': 'toolbarView.html' } ),

		initialize: function() {
			// An array of tool instances to put on the bar, ordered top-to-bottom
			tools = new Array;

			// TODO: decide here which tools to put on the bar, based on namespace, status, etc.
			// if we someday want this configurable on-wiki, this could load some js from
			// the MediaWiki namespace that generates the tools array instead, or we could make
			// some sort of config file thing

			// article information
			tools.push( new mw.pageTriage.ArticleInfoView( { eventBus: eventBus, model: article } ) );

			// and mark as reviewed
			tools.push( new mw.pageTriage.MarkView( { eventBus: eventBus } ) );

			// add tags
			tools.push( new mw.pageTriage.TagsView( { eventBus: eventBus } ) );

			// delete
			tools.push( new mw.pageTriage.DeleteView( { eventBus: eventBus } ) );

			// next article
			tools.push( new mw.pageTriage.NextView( { eventBus: eventBus } ) );
		},

		render: function() {
			// build the bar and insert into the page.

			// insert the empty toolbar into the document.
			$('body').append( this.template() );

			_.each( tools, function( tool ) {
				// append the individual tool template to the toolbar's big tool div part
				// this is the icon and hidden div. (the actual tool content)
				$( '#mwe-pt-toolbar-main' ).append( tool.place() );
			} );
			
			// make it draggable
			$( '#mwe-pt-toolbar' ).draggable();
		}
	} );

	// create an instance of the toolbar view
	var toolbar = new mw.pageTriage.ToolbarView( { eventBus: eventBus } );
	toolbar.render();
} );
