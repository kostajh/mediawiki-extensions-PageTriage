<?php

/**
 * Utility class for PageTriage
 */
class PageTriageUtil {

	/**
	 * Get whether or not a page needs triaging
	 *
	 * @param $article Article object
	 * 
	 * @return Mixed null if the page is not in the triage system,
	 * otherwise whether or not the page is unreviewed.
	 * Return convention is this way so that null and false are equivalent
	 * with a straight boolean test.
	 */
	public static function doesPageNeedTriage( $article ) {
		if ( ! $article ) {
			throw new MWException( "Invalid argument to " . __METHOD__ );
		}

		if ( ! $article->getId() ) {
			// article doesn't exist.  this happens a lot.
			return null;
		}

		$dbr = wfGetDB( DB_SLAVE );

		$row = $dbr->selectRow( 'pagetriage_page', 'ptrp_reviewed',
			array( 'ptrp_page_id' => $article->getID() )
		);

		if ( ! $row ) {
			return null;
		}

		return !(boolean)$row->ptrp_reviewed;
	}

	/**
	 * Get a list of stat for unreviewed articles
	 * @return array
	 *
	 * @Todo - Limit the number of records by a timestamp filter, maybe 30 days etc,
	 *         depends on the time the triage queue should look back for listview
	 */
	public static function getUnreviewedArticleStat() {
		global $wgMemc;

		$key = wfMemcKey( 'pagetriage', 'unreviewed-article', 'stat' );
	
		$data = $wgMemc->get( $key );
		if ( $data !== false ) {
			return $data;
		}

		$dbr = wfGetDB( DB_SLAVE );
		
		$res = $dbr->selectRow( 
			array( 'pagetriage_page' ),
			array( 'COUNT(ptrp_page_id) AS total' ),
			array( 'ptrp_reviewed' => 0 )
		);

		$percentile = array( 25, 50, 75, 90, 100 );

		$data = array( 'count' => 0 );

		foreach ( $percentile as $val ) {
			$data['age-' . $val . 'th-percentile'] = false;
		}

		if ( $res ) {
			$data['count'] = intval( $res->total );

			// show percentile stat only if there is a certain number of unreviewed articles
			if ( $data['count'] > 10 ) {
				foreach ( $percentile as $val ) {
					$data['age-' . $val . 'th-percentile'] = self::estimateArticleAgePercentile( $val, $data['count'] );
				}
			}
		}

		// make it expire in an hour
		$wgMemc->set( $key, $data, 3600 );
		return $data;
	}

	/**
	 * Get top page triagers in various time frame
	 * @param $time string - time to look back for top triagers, possible values include
	 *                       last-day, last-week, last-month, last-year
	 * @return array
	 */
	public static function getTopTriager( $time = 'last-week' ) {
		global $wgMemc;

		$now = wfTimestamp( TS_UNIX );

		// times to look back for top trigers and expiration time in cache
		$timeFrame = array( 
				'last-day' => array( 'ts' => $now - 24 * 60 * 60, 'expire' => 60 * 60 ), 
				'last-week' => array( 'ts' => $now - 7 * 24 * 60 * 60, 'expire' =>  24 * 60 * 60 ),
				//Todo: Do we really want to include big timeframe?
				'last-month' => array( 'ts' => $now - 30 * 24 * 60 * 60, 'expire' => 7 * 24 * 60 * 60 ),
				'last-year'=> array( 'ts' => $now - 365 * 24 * 60 * 60, 'expire' => 30 * 24 * 60 * 60 )
		);

		if ( !isset( $timeFrame[$time] ) ) {
			$time = 'last-day';
		}

		$dbr = wfGetDB( DB_SLAVE );
		$key = wfMemcKey( 'pagetriage', 'top-triager', $time );

		$topTriager = $wgMemc->get( $key );
		if ( $topTriager === false ) {
			$res = $dbr->select(
				array( 'pagetriage_log', 'user' ),
				array( 'user_name', 'user_id', 'COUNT(ptrl_id) AS num' ),
				array( 'user_id = ptrl_user_id', 'ptrl_timestamp > ' . $dbr->addQuotes( $dbr->timestamp( $timeFrame[$time]['ts'] ) ) ),
				__METHOD__,
				array( 'GROUP BY' => 'user_id', 'ORDER BY' => 'num DESC', 'LIMIT' => 50 )
			);
			
			$topTriager = iterator_to_array( $res );

			$wgMemc->set( $key, $topTriager, $timeFrame[$time]['expire'] );
		}

		return $topTriager;
	}

	/**
	 * Get the number of reviewed articles in last week
	 * @return int
	 */
	public static function getReviewedArticleNum() {
		global $wgMemc;

		$dbr = wfGetDB( DB_SLAVE );
		$key = wfMemcKey( 'pagetriage', 'reviewed-article', 'num' );

		$reviewedArticleNum = $wgMemc->get( $key );

		if ( $reviewedArticleNum !== false) {
			return $reviewedArticleNum;
		}

		$res = $dbr->selectRow(
			array( 'pagetriage_page' ),
			array( 'COUNT(ptrp_page_id) AS num' ),
			array( 'ptrp_reviewed = 1', 'ptrp_timestamp > ' . $dbr->addQuotes( $dbr->timestamp( wfTimestamp( TS_UNIX ) - 7 * 24 * 60 * 60 ) ) ),
			__METHOD__
		);

		if ( $res ) {
			$reviewedArticleNum = $res->num;
		} else {
			$reviewedArticleNum = 0;
		}

		$wgMemc->set( $key, $reviewedArticleNum, 6 * 60 * 60 );

		return $reviewedArticleNum;
	}

	/**
	 * Calculate the age of unreviewed articles by percentile
	 * @param $percentile int
	 * @param $count int
	 * @return int|bool
	 */
	private static function estimateArticleAgePercentile( $percentile, $count ) {

		if ( !is_int( $percentile ) || $percentile < 1 || $percentile > 100) {
			throw new MWPageTriageUtilInvalidNumberException( 'Invalid percentage number' );
		}
		
		if ( !is_int( $count ) || $count < 1 ) {
			throw new MWPageTriageUtilInvalidNumberException ( 'Invalid total count' );
		}

		// starting from oldest timestamp if percent is > 50
		if ( $percentile > 50 ) {
			$percentile = 100 - $percentile;
			$order = 'ASC';
		} else {
			$order = 'DESC';
		}

		$start = floor( ( $percentile / 100 ) * $count ) - 1;
		
		if ( $start < 0 ) {
			$start = 0;
		}

		$dbr = wfGetDB( DB_SLAVE );
		
		$res = $dbr->selectRow(
			array( 'pagetriage_page' ),
			array( 'ptrp_timestamp' ),
			array( 'ptrp_reviewed' => 0 ),
			__METHOD__,
			array( 'ORDER BY' => "ptrp_timestamp $order", 'LIMIT' => '1', 'OFFSET' => $start )
		);

		if ( $res ) {
			return $res->ptrp_timestamp;
		} else {
			return false;
		}
	}

	/**
	 * Check the existance of user page and talk page for a list of users
	 * @param $users array - contains user_name db keys
	 * @return array
	 */
	public static function pageStatusForUser( $users ) {
		global $wgMemc;

		$return = array();
		$title  = array();
		$dataToCache = array();

		foreach ( $users as $user ) {
			$user = (array) $user;
			$key = wfMemcKey( 'pagetriage', 'user-page-status', $user['user_name']);
			$data = $wgMemc->get( $key );
			if ( $data !== false ) {
				foreach ( $data as $pageKey => $status ) {
					if ( $status === 1 ) {
						$return[$pageKey] = $status;
					}
				}
			} else {
				$u = Title::newFromText( $user['user_name'], NS_USER );
				if ( $u ) {
					$t = Title::makeTitle( NS_USER_TALK, $u->getDBkey() );
					$title[$u->getDBkey()] = array( 'user_name' => $user['user_name'], 'u' => $u, 't' => $t );
				}
			}
		}

		if ( $title ) {
			$dbr = wfGetDB( DB_SLAVE );
			$res = $dbr->select(
				array( 'page' ),
				array( 'page_namespace', 'page_title' ),
				array( 'page_title' => array_keys( $title ), 'page_namespace' => array( NS_USER, NS_USER_TALK ) ),
				__METHOD__
			);

			$dataToCache = array();
			foreach ( $res as $row ) {
				$user = $title[$row->page_title];
				if ( $row->page_namespace == NS_USER ) {
					$dataToCache[$user['user_name']][$user['u']->getPrefixedDBkey()] = 1;
				} else {
					$dataToCache[$user['user_name']][$user['t']->getPrefixedDBkey()] = 1;
				}
			}

			foreach ( $title as $key => $value ) {
				$data = array();
				if ( !isset( $dataToCache[$value['user_name']][$value['u']->getPrefixedDBkey()] ) ) {
					$dataToCache[$value['user_name']][$value['u']->getPrefixedDBkey()] = 0;
				} else {
					$return[$value['u']->getPrefixedDBkey()] = 1;
				}
				if ( !isset( $dataToCache[$value['user_name']][$value['t']->getPrefixedDBkey()] ) ) {
					$dataToCache[$value['user_name']][$value['t']->getPrefixedDBkey()] = 0;
				} else {
					$return[$value['t']->getPrefixedDBkey()] = 1;
				}
				$memcKey = wfMemcKey( 'pagetriage', 'user-page-status', $value['user_name'] );
				$wgMemc->set( $memcKey, $dataToCache[$value['user_name']], 3600 );
			}
		}

		return $return;
	}

	/**
	 * Update user metadata when a user's block status is updated
	 * @param $block Block - Block object
	 * @param $status int 1/0
	 */
	public static function updateMetadataOnBlockChange( $block, $status = 1 ) {
		// do instant update if the number of page to be updated is less or equal to
		// the number below, otherwise, delay this to the cron
		$maxNumToProcess = 500;

		$tags = ArticleMetadata::getValidTags();

		$dbr = wfGetDB( DB_SLAVE );

		$res = $dbr->select(
			array( 'pagetriage_page_tags' ),
			array( 'ptrpt_page_id' ),
			array( 'ptrpt_tag_id' => $tags['user_name'], 'ptrpt_value' => (string)$block->getTarget() ),
			__METHOD__,
			array( 'LIMIT' => $maxNumToProcess + 1 )
		);

		if ( $dbr->numRows( $res ) > $maxNumToProcess ) {
			return;
		}

		$pageIds = array();
		foreach ( $res as $row ) {
			$pageIds[] = $row->ptrpt_page_id;
		}

		$dbw = wfGetDB( DB_MASTER );
		$dbw->update(  
			'pagetriage_page_tags',
			array( 'ptrpt_value' => $status ),
			array( 'ptrpt_page_id' => $pageIds, 'ptrpt_tag_id' => $tags['user_block_status'] )
		);

		$metadata = new ArticleMetadata( $pageIds );
		$metadata->updateMetadataInCache( array( 'user_block_status' => $status ) );
	}
}

class MWPageTriageUtilInvalidNumberException extends MWException {}