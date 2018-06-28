<?php

use MediaWiki\MediaWikiServices;
use MediaWiki\Extension\PageTriage\ArticleCompile\ArticleCompileAfcTag;

/**
 * Tests the inclusion of the Draft namespace.
 *
 * @group PageTriage
 * @group extensions
 * @group medium
 */
class ApiPageTriageListTest extends PageTriageTestCase {

	/** @var int */
	protected $draftNsId = 150;

	/**
	 * Setup draft namespace.
	 */
	public function setUp() {
		global $wgContLang;
		parent::setUp();

		// Define a Draft NS unless there already is one.
		$draftNsId = MediaWikiServices::getInstance()
			->getMainConfig()
			->get( 'PageTriageDraftNamespaceId' );
		if ( !$draftNsId ) {
			$this->setMwGlobals( [
				'wgExtraNamespaces' => [ $this->draftNsId => 'Draft' ],
				'wgPageTriageDraftNamespaceId' => $this->draftNsId,
			] );
			// Clear NS caches.
			MWNamespace::clearCaches();
			$wgContLang->resetNamespaces();
		} else {
			$this->draftNsId = $draftNsId;
		}
	}

	/**
	 * Creating a page in Draft namespace adds it to the queue.
	 * @covers \MediaWiki\Extension\PageTriage\Api\ApiPageTriageList
	 */
	public function testCreateDraftPage() {
		// Get initial queue length, for comparison.
		$originalList = $this->doApiRequest(
			[ 'action' => 'pagetriagelist', 'showunreviewed' => '1' ]
		);
		$originalPagesCount = count( $originalList[0]['pagetriagelist']['pages'] );

		// If we don't ask for it, a draft page shouldn't be returned.
		$this->insertPage( Title::newFromText( 'Draft:Test page 1' ) );
		$list1 = $this->doApiRequest( [ 'action' => 'pagetriagelist', 'showunreviewed' => '1' ] );
		static::assertCount( $originalPagesCount, $list1[0]['pagetriagelist']['pages'] );

		// Request the Draft namespace.
		$list2 = $this->getPageTriageList();
		static::assertArraySubset(
			[ 'title' => 'Draft:Test page 1' ],
			$list2[0]
		);
	}

	/**
	 * Adding/changing AfC categories.
	 * @covers \MediaWiki\Extension\PageTriage\Api\ApiPageTriageList
	 */
	public function testAfcTags() {
		$page = $this->insertPage( 'AfC test page', '', $this->draftNsId );
		$apiParams = [ 'afc_state' => ArticleCompileAfcTag::DECLINED ];

		// Initially there should be no declined drafts.
		$list1 = $this->getPageTriageList( $apiParams );
		static::assertCount( 0, $list1 );

		// Add category.
		$this->insertPage( 'AfC test page', '[[Category:Declined AfC submissions]]',
			$this->draftNsId
		);

		// Check that the database was updated correctly (not really necessary?).
		$db = wfGetDB( DB_MASTER );
		$pageTags = $db->select( 'pagetriage_page_tags', '*',
			[ 'ptrpt_page_id' => $page['id'] ],
			__METHOD__
		);
		static::assertEquals( 2, $pageTags->numRows() );
		static::assertEquals( ArticleCompileAfcTag::DECLINED, $pageTags->current()->ptrpt_value );

		// Request the declined drafts.
		$list2 = $this->getPageTriageList( $apiParams );
		static::assertArraySubset(
			[ 'title' => 'Draft:AfC test page' ],
			$list2[0]
		);

		// Move the page out of the declined category, and it disappears from the list.
		$this->insertPage( 'AfC test page', '[[category:nop]]', $this->draftNsId );
		$list3 = $this->getPageTriageList( $apiParams );
		static::assertCount( 0, $list3 );
	}

	/**
	 * Getting unsubmitted drafts (not in related category).
	 * @covers \MediaWiki\Extension\PageTriage\Api\ApiPageTriageList
	 */
	public function testUnsubmittedDrafts() {
		$apiParams = [ 'afc_state' => ArticleCompileAfcTag::UNSUBMITTED ];

		$originalUnsubmittedCount = count( $this->getPageTriageList( $apiParams ) );

		// Create a draft page.
		$this->insertPage( 'Test page 2', '', $this->draftNsId );

		// There should be one more unsubmitted draft.
		static::assertCount(
			$originalUnsubmittedCount + 1,
			$this->getPageTriageList( $apiParams )
		);

		// Add valid category.
		$this->insertPage( 'Test page 2', '[[Category:Declined AfC submissions]]',
			$this->draftNsId
		);

		// Should now be back to the original count.
		static::assertCount(
			$originalUnsubmittedCount,
			$this->getPageTriageList( $apiParams )
		);

		// Remove the category and the page should once again be 'unsubmitted'.
		$this->insertPage( 'Test page 2', '[[Category:Nop]]', $this->draftNsId );
		static::assertCount(
			$originalUnsubmittedCount + 1,
			$this->getPageTriageList( $apiParams )
		);
	}

	/**
	 * Moving an existing page to the Draft namespace adds it to the queue.
	 * @covers \MediaWiki\Extension\PageTriage\Api\ApiPageTriageList
	 */
	public function testMoveToDraftPage() {
		// Get the initial queue count.
		$originalPagesCount = count( $this->getPageTriageList() );

		// Move the page from mainspace to Draft.
		$from = Title::newFromText( 'Test page 3' );
		$to = Title::newFromText( 'Draft:Test page 3' );
		$this->insertPage( $from );
		$movePage = new MovePage( $from, $to );
		$movePage->move( static::getTestUser()->getUser(), '', false );

		// Check that the moved page is in the queue of unreviewed pages.
		$list = $this->getPageTriageList();
		static::assertCount( $originalPagesCount + 1, $list );
		static::assertArraySubset(
			[ 'title' => 'Draft:Test page 3' ],
			$list[0]
		);
	}

	/**
	 * Moving a page out of the Draft namespace removes it from the queue.
	 * @covers \MediaWiki\Extension\PageTriage\Api\ApiPageTriageList
	 */
	public function testMoveFromDraftPage() {
		// Add a page to the Draft namespace.
		$from = Title::newFromText( 'Draft:Test page 4' );
		$to = Title::newFromText( 'Mainspace page 4' );
		$this->insertPage( $from );

		// Get the queue count.
		$originalPagesCount = count( $this->getPageTriageList() );

		// Move the page to mainspace.
		$movePage = new MovePage( $from, $to );
		$movePage->move( static::getTestUser()->getUser(), '', false );

		// Check that the queue has decremented by one.
		static::assertEquals(
			$originalPagesCount - 1,
			count( $this->getPageTriageList() )
		);
	}

	/**
	 * Make sure mainspace pages by autopatrolled users are marked as reviewed and vice versa.
	 * @covers \MediaWiki\Extension\PageTriage\Api\ApiPageTriageList
	 * @covers \MediaWiki\Extension\PageTriage\Hooks::addToPageTriageQueue()
	 */
	public function testAutopatrolledCreation() {
		$apiParams = [ 'namespace' => 0 ];

		$this->insertPage( 'Mainspace test page 1', '' );

		// Should not be in unreviewed list (test user is a sysop and hence autopatrolled).
		$list = $this->getPageTriageList( $apiParams );

		// First check count($list) in case this test is ran standalone.
		static::assertTrue( count( $list ) === 0 || $list[0]['title'] !== 'Mainspace test page 1' );

		// Create another page using a non-autopatrolled user.
		$user = static::getTestUser()->getUser();
		$this->insertPage( 'Mainspace test page 2', '', 0, $user );

		// Test page 2 *should* be in the queue (and at the top since it's the most recent).
		$list = $this->getPageTriageList( $apiParams );
		static::assertEquals( 'Mainspace test page 2', $list[0]['title'] );
	}

	/**
	 * Sorting drafts by submission date or date of decline.
	 * @covers \MediaWiki\Extension\PageTriage\Api\ApiPageTriageList
	 */
	public function testSubmissionSorting() {
		$apiParams = [
			'dir' => 'oldestreview',
		];

		$originalTopPage = $this->getPageTriageList( $apiParams )[0];

		// New draft in a relevant category.
		$page = $this->insertPage( 'Test page 5', '[[Category:Declined AfC submissions]]',
			$this->draftNsId
		);

		// Original top page should still be the top (or the new one, if none existed beforehand).
		$list = $this->getPageTriageList( $apiParams );
		if ( $originalTopPage ) {
			static::assertEquals( $originalTopPage, $list[0] );
		} else {
			static::assertArraySubset(
				[ 'title' => 'Draft:Test page 5' ],
				$list[0]
			);
		}

		// Manually set the reviewed at attribute to something really old.
		$dbw = wfGetDB( DB_MASTER );
		$dbw->update( 'pagetriage_page',
			[ 'ptrp_reviewed_updated' => '20010115000000' ],
			[ 'ptrp_page_id' => $page['id'] ],
			__METHOD__
		);

		// Insert another draft in a relevant category.
		$this->insertPage( 'Draft:Test page 6', '[[Category:Pending AfC submissions]]',
			$this->draftNsId
		);

		// 'Test page 5' should be the oldest.
		static::assertArraySubset(
			[ 'title' => 'Draft:Test page 5' ],
			$this->getPageTriageList( $apiParams )[0]
		);
	}

}
