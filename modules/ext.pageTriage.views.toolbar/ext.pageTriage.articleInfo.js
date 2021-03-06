// view for displaying all the article metadata

$( function () {
	mw.pageTriage.ArticleInfoView = mw.pageTriage.ToolView.extend( {
		id: 'mwe-pt-info',
		icon: 'icon_info.png', // the default icon
		title: mw.msg( 'pagetriage-info-title' ),
		tooltip: 'pagetriage-info-tooltip',
		template: mw.template.get( 'ext.pageTriage.views.toolbar', 'articleInfo.underscore' ),

		badgeCount: function () {
			this.enumerateProblems();
			return this.problemCount;
		},

		render: function () {
			var bylineMessage, articleByline, stats, history,
				url = new mw.Uri( mw.util.getUrl( mw.config.get( 'wgPageName' ) ) ),
				that = this;

			this.enumerateProblems();
			// set the history link
			url.query.action = 'history';
			this.model.set(
				'history_link',
				url.toString()
			);

			// creator information
			if ( this.model.get( 'user_name' ) ) {
				// show new editor message only if the user is not anonymous and not autoconfirmed
				if ( this.model.get( 'user_id' ) > '0' && this.model.get( 'user_autoconfirmed' ) === '0' ) {
					bylineMessage = 'pagetriage-articleinfo-byline-new-editor';
				} else {
					bylineMessage = 'pagetriage-articleinfo-byline';
				}

				// put it all together in the byline
				articleByline = mw.msg(
					bylineMessage,
					Date.parseExact(
						this.model.get( 'creation_date' ),
						'yyyyMMddHHmmss'
					).toString(
						mw.msg( 'pagetriage-info-timestamp-date-format' )
					),
					this.model.buildLinkTag(
						this.model.get( 'creator_user_page_url' ),
						this.model.get( 'user_name' ),
						this.model.get( 'creator_user_page_exist' )
					),
					this.model.buildLinkTag(
						this.model.get( 'creator_user_talk_page_url' ),
						mw.msg( 'sp-contributions-talk' ),
						this.model.get( 'creator_user_talk_page_exist' )
					),
					mw.msg( 'pipe-separator' ),
					this.model.buildLinkTag(
						this.model.get( 'creator_contribution_page_url' ),
						mw.msg( 'contribslink' ),
						true
					)
				);
				this.model.set( 'articleByline', articleByline );
			}

			stats = [
				mw.msg( 'pagetriage-bytes', this.model.get( 'page_len' ) ),
				mw.msg( 'pagetriage-edits', this.model.get( 'rev_count' ) ),
				mw.msg( 'pagetriage-categories', this.model.get( 'category_count' ) )
			];
			this.model.set( 'articleStat', mw.msg( 'pagetriage-articleinfo-stat', stats.join( mw.msg( 'pagetriage-dot-separator' ) ) ) );

			this.$tel.html( this.template( this.model.toJSON() ) );
			history = new mw.pageTriage.ArticleInfoHistoryView( { eventBus: this.eventBus, model: this.model.revisions } );
			this.$tel.find( '#mwe-pt-info-history-container' ).append( history.render().$el );

			// set the Learn More link URL
			$( '#mwe-pt-info .mwe-pt-flyout-help-link' ).attr( 'href', mw.config.get( 'wgPageTriageCurationModules' ).articleInfo.helplink );

			// bind down here so it doesn't happen before the first render
			this.model.unbind( 'change:patrol_status', function () { that.render(); } );
			this.model.bind( 'change:patrol_status', function () { that.render(); } );

			return this;
		},

		formatProblem: function ( problem ) {
			// Give grep a chance to find the usages:
			// pagetriage-info-problem-non-autoconfirmed, pagetriage-info-problem-blocked,
			// pagetriage-info-problem-no-categories, pagetriage-info-problem-orphan,
			// pagetriage-info-problem-no-references,
			// pagetriage-info-problem-non-autoconfirmed-desc, pagetriage-info-problem-blocked-desc,
			// pagetriage-info-problem-no-categories-desc, pagetriage-info-problem-orphan-desc,
			// pagetriage-info-problem-no-references-desc
			return '<li class="mwe-pt-info-problem"><span class="mwe-pt-info-problem-name">' +
				mw.msg( 'pagetriage-info-problem-' + problem ) +
				'</span> - <span class="mwe-pt-info-problem-desc">' +
				mw.msg( 'pagetriage-info-problem-' + problem + '-desc' ) +
				'</span></li>';
		},

		enumerateProblems: function () {
			var problems = '';
			this.problemCount = 0;

			// Give grep a chance to find the usages:
			// pagetriage-info-problem-blocked, pagetriage-info-problem-no-categories,
			// pagetriage-info-problem-orphan, pagetriage-info-problem-no-references
			if ( this.model.get( 'user_block_status' ) === 1 ) {
				this.problemCount++;
				problems += this.formatProblem( 'blocked' );
			}
			if ( this.model.get( 'category_count' ) < 1 && this.model.get( 'is_redirect' ) === 0 ) {
				this.problemCount++;
				problems += this.formatProblem( 'no-categories' );
			}
			if ( this.model.get( 'linkcount' ) < 1 && this.model.get( 'is_redirect' ) === 0 ) {
				this.problemCount++;
				problems += this.formatProblem( 'orphan' );
			}
			if ( typeof this.model.get( 'reference' ) !== 'undefined' &&
				this.model.get( 'reference' ) === '0' &&
				this.model.get( 'is_redirect' ) === 0 ) {
				this.problemCount++;
				problems += this.formatProblem( 'no-references' );
			}
			if ( problems ) {
				problems = '<ul>' + problems + '</ul>';
			}
			this.model.set( 'problems', problems );
		}
	} );

	mw.pageTriage.ArticleInfoHistoryView = Backbone.View.extend( {
		id: 'mwe-pt-info-history',
		template: mw.template.get( 'ext.pageTriage.views.toolbar', 'articleInfoHistory.underscore' ),

		render: function () {
			var parsedTimestamp, userTitle,
				that = this,
				lastDate = null,
				x = 0;

			this.model.each( function ( historyItem ) {
				// Display the 5 most recent revisions
				if ( x < 5 ) {
					// I'd rather do this date parsing in the model, but the change event isn't properly
					// passed through to nested models, and switching to backbone-relational in order to
					// move these few lines of code seems silly.
					parsedTimestamp = Date.parseExact(
						historyItem.get( 'timestamp' ),
						'yyyy-MM-ddTHH:mm:ssZ'
					);

					historyItem.set(
						'timestamp_date',
						parsedTimestamp.toString( mw.msg( 'pagetriage-info-timestamp-date-format' ) )
					);
					historyItem.set(
						'timestamp_time',
						parsedTimestamp.toString( mw.msg( 'pagetriage-info-timestamp-time-format' ) )
					);
					if ( historyItem.get( 'timestamp_date' ) !== lastDate ) {
						historyItem.set( 'new_date', true );
					} else {
						historyItem.set( 'new_date', false );
					}
					lastDate = historyItem.get( 'timestamp_date' );

					// get a userlink.
					// can't set link color since no userpage status is returned by the history api
					if ( historyItem.get( 'user' ) ) {
						try {
							userTitle = new mw.Title( historyItem.get( 'user' ), mw.config.get( 'wgNamespaceIds' ).user );
							historyItem.set( 'user_title_url', userTitle.getUrl() );
						} catch ( e ) {
							historyItem.set( 'user_title_url', '' );
						}
					}

					historyItem.set( 'revision_url', mw.config.get( 'wgScriptPath' ) + '/index.php?title=' +
										mw.util.wikiUrlencode( mw.config.get( 'wgPageName' ) ) +
										'&oldid=' + historyItem.get( 'revid' ) );

					that.$el.append( that.template( historyItem.toJSON() ) );
				}
				x++;
			} );
			return this;
		}
	} );

} );
