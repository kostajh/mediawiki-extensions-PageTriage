<?php

namespace MediaWiki\Extension\PageTriage\Api;

use MediaWiki\Extension\PageTriage\ArticleMetadata;
use MediaWiki\Extension\PageTriage\OresMetadata;
use MediaWiki\Extension\PageTriage\PageTriageUtil;
use ApiBase;
use ApiResult;
use ORES\ORESServices;
use SpecialPage;
use Title;

/**
 * API module to generate a list of pages to triage
 *
 * @ingroup API
 * @ingroup Extensions
 */
class ApiPageTriageList extends ApiBase {

	public function execute() {
		// Get the API parameters and store them
		$opts = $this->extractRequestParams();
		$pages = null;

		if ( $opts['page_id'] ) {
			// page id was specified
			$pages = [ $opts['page_id'] ];
			$pageIdValidated = false;
		} else {
			// Retrieve the list of page IDs
			$pages = self::getPageIds( $opts );
			$pageIdValidated = true;
		}
		$pageIdValidateDb = DB_REPLICA;

		$sortedMetaData = [];

		if ( $pages ) {
			// fetch metadata for those pages
			$articleMetadata = new ArticleMetadata( $pages, $pageIdValidated, $pageIdValidateDb );
			$metaData = $articleMetadata->getMetadata();

			$userPageStatus = PageTriageUtil::pageStatusForUser( $metaData );

			if ( PageTriageUtil::oresIsAvailable() ) {
				$oresMetadata = OresMetadata::newFromGlobalState( $this->getContext(), $pages );
			}

			// Sort data according to page order returned by our query. Also convert it to a
			// slightly different format that's more Backbone-friendly.
			foreach ( $pages as $page ) {
				if ( !isset( $metaData[$page] ) ) {
					continue;
				}
				$metaData[$page]['creation_date_utc'] = $metaData[$page]['creation_date'];
				$metaData[$page]['creation_date'] = $this->getContext()->getLanguage()->userAdjust(
					$metaData[$page]['creation_date']
				);

				// Page creator
				$metaData[$page] += $this->createUserInfo(
					$metaData[$page]['user_name'],
					$userPageStatus,
					'creator'
				);

				// Page reviewer
				if ( $metaData[$page]['reviewer'] ) {
					$metaData[$page] += $this->createUserInfo(
						$metaData[$page]['reviewer'],
						$userPageStatus,
						'reviewer'
					);
				}

				// Add ORES data
				if ( PageTriageUtil::oresIsAvailable() ) {
					$metaData[$page] = $metaData[$page] + $oresMetadata->getMetadata( $page );
				}

				$metaData[$page][ApiResult::META_BC_BOOLS] = [
					'creator_user_page_exist', 'creator_user_talk_page_exist',
					'reviewer_user_page_exist', 'reviewer_user_talk_page_exist',
				];

				$sortedMetaData[] = [ 'pageid' => $page ] + $metaData[$page];
			}
		}

		// Output the results
		$result = [ 'result' => 'success', 'pages' => $sortedMetaData ];
		$this->getResult()->addValue( null, $this->getModuleName(), $result );
	}

	/**
	 * Create user info like user page, user talk page, user contribution page
	 * @param string $userName a valid username
	 * @param array $userPageStatus an array of user page, user talk page existing status
	 * @param string $prefix array key prefix
	 * @return array
	 */
	private function createUserInfo( $userName, $userPageStatus, $prefix ) {
		$userPage = Title::makeTitle( NS_USER, $userName );
		$userTalkPage = Title::makeTitle( NS_USER_TALK, $userName );
		$userContribsPage = SpecialPage::getTitleFor( 'Contributions', $userName );

		return [
			$prefix . '_user_page' => $userPage->getPrefixedText(),
			$prefix . '_user_page_url' => $userPage->getFullURL(),
			$prefix . '_user_page_exist' => isset( $userPageStatus[$userPage->getPrefixedDBkey()] ),
			$prefix . '_user_talk_page' => $userTalkPage->getPrefixedText(),
			$prefix . '_user_talk_page_url' => $userTalkPage->getFullURL(),
			$prefix . '_user_talk_page_exist' => isset( $userPageStatus[$userTalkPage->getPrefixedDBkey()] ),
			$prefix . '_contribution_page' => $userContribsPage->getPrefixedText(),
			$prefix . '_contribution_page_url' => $userContribsPage->getFullURL(),
		];
	}

	/**
	 * Return all the page ids in PageTriage matching the specified filters
	 * @param array $opts Array of filtering options
	 * @param bool $count Set to true to return a count instead
	 * @return array|int an array of ids or total number of pages
	 *
	 * @todo - enforce a range of timestamp to reduce tag record scan
	 */
	public static function getPageIds( $opts = [], $count = false ) {
		// Initialize required variables
		$pages = [];
		$options = [];
		$join_conds = [];

		if ( !$count ) {
			// Get the expected limit as defined in getAllowedParams
			$options['LIMIT'] = $opts['limit'] + 1;

			switch ( strtolower( $opts['dir'] ) ) {
				case 'oldestfirst':
					$options['ORDER BY'] = 'ptrp_created ASC, ptrp_page_id ASC';
					$offsetOperator = ' > ';
					break;
				case 'oldestreview':
					$options['ORDER BY'] = 'ptrp_reviewed_updated ASC, ptrp_page_id ASC';
					$offsetOperator = ' > ';
					break;
				case 'newestreview':
					$options['ORDER BY'] = 'ptrp_reviewed_updated DESC, ptrp_page_id DESC';
					$offsetOperator = ' < ';
					break;
				default:
					$options['ORDER BY'] = 'ptrp_created DESC, ptrp_page_id DESC';
					$offsetOperator = ' < ';
			}
		}

		// Start building the massive filter which includes meta data
		$tables	= [ 'pagetriage_page', 'page' ];
		$join_conds['page'] = [
			'INNER JOIN',
			'ptrp_page_id = page_id',
		];

		// Helpful hint: In the ptrp_reviewed column...
		// 0 = unreviewed
		// 1 = reviewed
		// 2 = patrolled
		// 3 = autopatrolled
		$reviewOpr = '';
		if ( isset( $opts['showreviewed'] ) && $opts['showreviewed'] ) {
			$reviewOpr .= '>';
		}
		if ( isset( $opts['showunreviewed'] ) && $opts['showunreviewed'] ) {
			$reviewOpr .= '=';
		}
		if ( !$reviewOpr ) {
			if ( $count ) {
				return 0;
			} else {
				return $pages;
			}
		}
		if ( $reviewOpr !== '>=' ) {
			$conds[] = 'ptrp_reviewed ' . $reviewOpr . ' 0';
		}

		// Exclude redirects unless they are explicitly requested
		if ( !isset( $opts['showredirs'] ) || !$opts['showredirs'] ) {
			$conds['page_is_redirect'] = 0;
		}
		// Exclude pages marked for deletion unless they are explicitly requested
		if ( !isset( $opts['showdeleted'] ) || !$opts['showdeleted'] ) {
			$conds['ptrp_deleted'] = 0;
		}

		// Show by namespace. Defaults to main namespace.
		$nsId = ( isset( $opts['namespace'] ) && $opts['namespace'] ) ? $opts['namespace'] : NS_MAIN;
		$conds['page_namespace'] = PageTriageUtil::validatePageNamespace( $nsId );

		// Database setup
		$dbr = wfGetDB( DB_REPLICA );

		// Offset the list by timestamp
		if (
			array_key_exists( 'offset', $opts ) &&
			is_numeric( $opts['offset'] ) &&
			$opts['offset'] > 0 &&
			!$count
		) {
			$opts['offset'] = $dbr->addQuotes( $dbr->timestamp( $opts['offset'] ) );
			// Offset the list by page ID as well (in case multiple pages have the same timestamp)
			if (
				array_key_exists( 'pageoffset', $opts ) &&
				is_numeric( $opts['pageoffset'] ) &&
				$opts['pageoffset'] > 0
			) {
				$conds[] = '( ptrp_created' . $offsetOperator . $opts['offset'] . ') OR ' .
					'( ptrp_created = ' . $opts['offset'] .' AND ' .
					'ptrp_page_id ' . $offsetOperator . $opts['pageoffset'] . ')';
			} else {
				$conds[] = 'ptrp_created' . $offsetOperator . $opts['offset'];
			}
		}

		$tagConds = self::buildTagQuery( $opts );
		if ( $tagConds ) {
			$conds[] = $tagConds;
			$tables[] = 'pagetriage_page_tags';
			$join_conds['pagetriage_page_tags'] = [
				'INNER JOIN',
				'ptrpt_page_id = ptrp_page_id',
			];
		}

		// ORES wp10 filter
		if ( PageTriageUtil::oresIsAvailable() &&
			PageTriageUtil::isOresWp10Query( $opts ) ) {
			$oresCond = ORESServices::getDatabaseQueryBuilder()->buildQuery(
				'wp10',
				PageTriageUtil::mapOresParamsToClassNames( 'wp10', $opts )
			);
			if ( $oresCond ) {
				self::joinWithOres( 'wp10', $tables, $conds, $join_conds );
				$conds[] = $oresCond;
			}
		}

		// ORES draftquality filter
		if ( PageTriageUtil::oresIsAvailable() &&
			PageTriageUtil::isOresDraftQualityQuery( $opts ) ) {
			$oresCond = ORESServices::getDatabaseQueryBuilder()->buildQuery(
				'draftquality',
				PageTriageUtil::mapOresParamsToClassNames( 'draftquality', $opts ),
				true
			);
			if ( $oresCond ) {
				self::joinWithOres( 'draftquality', $tables, $conds, $join_conds );
				$conds[] = $oresCond;
			}
		}

		if ( $count ) {
			$res = $dbr->selectRow(
				$tables,
				[ 'COUNT(ptrp_page_id) AS total' ],
				$conds,
				__METHOD__,
				$options,
				$join_conds
			);
			return (int)$res->total;
		} else {
			// Pull page IDs from database
			$res = $dbr->select(
				$tables,
				'ptrp_page_id',
				$conds,
				__METHOD__,
				$options,
				$join_conds
			);

			// Loop through result set and return ids
			foreach ( $res as $row ) {
				$pages[] = $row->ptrp_page_id;
			}

			return $pages;
		}
	}

	/**
	 * @param string $model Name of the model this join is for
	 * @param array $tables
	 * @param array $conds
	 * @param array $join_conds
	 */
	private static function joinWithOres( $model, &$tables, &$conds, &$join_conds ) {
		$modelId = ORESServices::getModelLookup()->getModelId( $model );
		$tableAlias = "ores_{$model}_cls";
		$tables[ $tableAlias ] = 'ores_classification';
		$join_conds[ $tableAlias ] = [
			'INNER JOIN',
			"$tableAlias.oresc_rev = page_latest",
		];
		$conds[ "$tableAlias.oresc_model" ] = $modelId;
	}

	/**
	 * @param array $opts
	 * @return string
	 */
	private static function buildTagQuery( $opts ) {
		$dbr = wfGetDB( DB_REPLICA );
		$tagConds = '';

		$searchableTags = [
			// no categories assigned
			'no_category' => [ 'name' => 'category_count', 'op' => '=', 'val' => '0' ],
			// AfC status
			'afc_state' => [ 'name' => 'afc_state', 'op' => '=', 'val' => false ],
			// no inbound links
			'no_inbound_links' => [ 'name' => 'linkcount', 'op' => '=', 'val' => '0' ],
			// non auto confirmed users
			'non_autoconfirmed_users' => [ 'name' => 'user_autoconfirmed', 'op' => '=', 'val' => '0' ],
			// learning users (newly autoconfirmed)
			'learners' => [ 'name' => 'user_experience', 'op' => '=', 'val' => 'learner' ],
			// blocked users
			'blocked_users' => [ 'name' => 'user_block_status', 'op' => '=', 'val' => '1' ],
			// bots
			'showbots' => [ 'name' => 'user_bot', 'op' => '=', 'val' => '1' ],
			// user name
			// false means use the actual value
			'username' => [ 'name' => 'user_name', 'op' => '=', 'val' => false ]
		];

		$tags = ArticleMetadata::getValidTags();

		// only single tag search is allowed
		foreach ( $searchableTags as $key => $val ) {
			if ( isset( $opts[$key] ) && $opts[$key] ) {
				if ( $val['val'] === false ) {
					// if val is false, use the value that was supplied via the api call
					$tagConds = " ptrpt_tag_id = '" . $tags[$val['name']] . "' AND " .
						"ptrpt_value " . $val['op'] . " " . $dbr->addQuotes( $opts[$key] );
				} else {
					$tagConds = " ptrpt_tag_id = '" . $tags[$val['name']] . "' AND " .
						"ptrpt_value " . $val['op'] . " " . $dbr->addQuotes( $val['val'] );
				}
				break;
			}
		}

		return $tagConds;
	}

	public function getAllowedParams() {
		return array_merge(
			PageTriageUtil::getOresApiParams(),
			PageTriageUtil::getCommonApiParams(),
			[
				'page_id' => [
					ApiBase::PARAM_TYPE => 'integer',
				],
				'limit' => [
					ApiBase::PARAM_MAX => 200,
					ApiBase::PARAM_DFLT => 20,
					ApiBase::PARAM_MIN => 1,
					ApiBase::PARAM_TYPE => 'integer',
				],
				'offset' => [
					ApiBase::PARAM_TYPE => 'integer',
				],
				'pageoffset' => [
					ApiBase::PARAM_TYPE => 'integer',
				],
				'dir' => [
					ApiBase::PARAM_TYPE => [
						'newestfirst',
						'oldestfirst',
						'oldestreview',
						'newestreview',
					],
				]
			]
		);
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 * @return array
	 */
	protected function getExamplesMessages() {
		return [
			'action=pagetriagelist&limit=100&namespace=0&showunreviewed=1'
				=> 'apihelp-pagetriagelist-example-1',
		];
	}

}
