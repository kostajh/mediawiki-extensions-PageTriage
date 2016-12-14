<?php
/**
 * MediaWiki PageTriage extension
 * http://www.mediawiki.org/wiki/Extension:PageTriage
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * This program is distributed WITHOUT ANY WARRANTY.
 */

/**
 * This file loads everything needed for the PageTriage extension to function.
 *
 * @file
 * @ingroup Extensions
 * @author Ryan Kaldari
 * @license MIT License
 */

// Alert the user that this is not a valid entry point to MediaWiki if they try to access the
// special pages file directly.
if ( !defined( 'MEDIAWIKI' ) ) {
	echo <<<EOT
To install this extension, put the following line in LocalSettings.php:
require_once "\$IP/extensions/PageTriage/PageTriage.php";
EOT;
	exit( 1 );
}

// Extension credits that will show up on Special:Version
$wgExtensionCredits['specialpage'][] = [
	'path' => __FILE__,
	'name' => 'PageTriage',
	'version' => '0.2.1',
	'url' => 'https://www.mediawiki.org/wiki/Extension:PageTriage',
	'author' => [
		'Ryan Kaldari',
		'Benny Situ',
		'Ian Baker',
		'Andrew Garrett',
	],
	'descriptionmsg' => 'pagetriage-desc',
	'license-name' => 'MIT',
];

// Begin configuration variables
// Maximum number of articles for the API to retrieve at once
$wgPageTriagePagesPerRequest = 20;
// Whether or not to use infinite scrolling in the page list
$wgPageTriageInfiniteScrolling = true;
// Whether or not the top nav bar should float
$wgPageTriageStickyControlNav = true;
// Whether or not the bottom nav bar should float
$wgPageTriageStickyStatsNav = true;
// 1 day - How long after visiting Special:NewPagesFeed do we show review links on articles
$wgPageTriageMarkPatrolledLinkExpiry = 3600 * 24;
// Array of template names (without prefixes) that will trigger noindexing of
// pages that include them, for example, speedy deletion templates. Note that
// it isn't necessary to list redirects or subtemplates.
$wgPageTriageNoIndexTemplates = [];
// Set this to true if new, unreviewed articles should be set to noindex. In other
// words, if they should not be indexed by search engines until they are reviewed.
$wgPageTriageNoIndexUnreviewedNewArticles = false;
$wgPageTriageLearnMoreUrl = '//en.wikipedia.org/wiki/Wikipedia:Page_Curation/Help';
$wgPageTriageProjectLink = 'Wikipedia:Page Curation';
$wgPageTriageFeedbackUrl = '//en.wikipedia.org/wiki/Wikipedia_talk:Page_Curation';
// enable the curation toolbar?
$wgPageTriageEnableCurationToolbar = true;
$wgPageTriageCurationModules = [
	'articleInfo' => [
		'helplink' => '//en.wikipedia.org/wiki/Wikipedia:Page_Curation/Help#PageInfo',
		'namespace' => [ NS_MAIN, NS_USER ],
	],
	'mark' => [
		'helplink' => '//en.wikipedia.org/wiki/Wikipedia:Page_Curation/Help#MarkReviewed',
		'namespace' => [ NS_MAIN, NS_USER ],
		'note' => [ NS_MAIN ],
	],
	'tags' => [
		'helplink' => '//en.wikipedia.org/wiki/Wikipedia:Page_Curation/Help#AddTags',
		'namespace' => [ NS_MAIN ],
	],
	'delete' => [
		'helplink' => '//en.wikipedia.org/wiki/Wikipedia:Page_Curation/Help#MarkDeletion',
		'namespace' => [ NS_MAIN, NS_USER ],
	],
];
// version number to be added to cache key so that cache can be refreshed easily
$wgPageTriageCacheVersion = '1.4';
// only include these namespaces for pagetriage
$wgPageTriageNamespaces = [ NS_MAIN, NS_USER ];
$wgTalkPageNoteTemplate = [
	'Mark' => 'Reviewednote-NPF',
	'UnMark' => [ 'note' => 'Unreviewednote-NPF', 'nonote' => 'Unreviewednonote-NPF' ],
	'Tags' => 'Taggednote-NPF'
];
// Set which PageTriage Echo events (defined in PageTriageHooks::onBeforeCreateEchoEvent)
// will be enabled as notifications
$wgPageTriageEnabledEchoEvents = [
	'pagetriage-mark-as-reviewed',
	'pagetriage-add-maintenance-tag',
	'pagetriage-add-deletion-tag'
];
// Set default user options
$wgDefaultUserOptions['echo-subscriptions-web-page-review'] = true;
// This is overriden for new users in PageTriageHooks::onLocalUserCreated
$wgDefaultUserOptions['echo-subscriptions-email-page-review'] = false;
// End configuration variables


$dir = __DIR__ . '/';

$wgMessagesDirs['PageTriage'] = $dir . 'i18n';
$wgExtensionMessagesFiles['PageTriage'] = $dir . 'PageTriage.i18n.php';
$wgExtensionMessagesFiles['PageTriageAlias'] = $dir . 'PageTriage.alias.php';

$wgAutoloadClasses['SpecialNewPagesFeed'] = $dir . 'SpecialNewPagesFeed.php';
$wgSpecialPages['NewPagesFeed'] = 'SpecialNewPagesFeed';
$wgAutoloadClasses['ArticleMetadata'] = $dir . 'includes/ArticleMetadata.php';
$wgAutoloadClasses['PageTriage'] = $dir . 'includes/PageTriage.php';
$wgAutoloadClasses['PageTriageUtil'] = $dir . 'includes/PageTriageUtil.php';
$wgAutoloadClasses['PageTriageHooks'] = $dir . 'PageTriage.hooks.php';
$wgAutoloadClasses['ArticleCompileProcessor'] = $dir . 'includes/ArticleMetadata.php';
$wgAutoloadClasses['ArticleCompileInterface'] = $dir . 'includes/ArticleMetadata.php';
$wgAutoloadClasses['ArticleCompileBasicData'] = $dir . 'includes/ArticleMetadata.php';
$wgAutoloadClasses['ArticleCompileLinkCount'] = $dir . 'includes/ArticleMetadata.php';
$wgAutoloadClasses['ArticleCompileCategoryCount'] = $dir . 'includes/ArticleMetadata.php';
$wgAutoloadClasses['ArticleCompileSnippet'] = $dir . 'includes/ArticleMetadata.php';
$wgAutoloadClasses['ArticleCompileUserData'] = $dir . 'includes/ArticleMetadata.php';
$wgAutoloadClasses['ArticleCompileDeletionTag'] = $dir . 'includes/ArticleMetadata.php';
$wgAutoloadClasses['PageTriageExternalTagsOptions'] = $dir
	. 'includes/PageTriageExternalTagsOptions.php';
$wgAutoloadClasses['PageTriageExternalDeletionTagsOptions'] = $dir
	. 'includes/PageTriageExternalDeletionTagsOptions.php';
$wgAutoloadClasses['PageTriageMessagesModule'] = $dir
	. 'includes/PageTriageMessagesModule.php';

$wgAutoloadClasses['PageTriageLogFormatter'] = $dir
	. 'includes/PageTriageLogFormatter.php';
$wgAutoloadClasses['PageTriagePresentationModel'] = $dir
	. 'includes/Notifications/PageTriagePresentationModel.php';
$wgAutoloadClasses['PageTriageMarkAsReviewedPresentationModel'] = $dir
	. 'includes/Notifications/PageTriageMarkAsReviewedPresentationModel.php';
$wgAutoloadClasses['PageTriageAddMaintenanceTagPresentationModel'] = $dir
	. 'includes/Notifications/PageTriageAddMaintenanceTagPresentationModel.php';
$wgAutoloadClasses['PageTriageAddDeletionTagPresentationModel'] = $dir
	. 'includes/Notifications/PageTriageAddDeletionTagPresentationModel.php';

$wgAutoloadClasses['ApiPageTriageList'] = $dir . 'api/ApiPageTriageList.php';
$wgAutoloadClasses['ApiPageTriageStats'] = $dir . 'api/ApiPageTriageStats.php';
$wgAutoloadClasses['ApiPageTriageAction'] = $dir . 'api/ApiPageTriageAction.php';
$wgAutoloadClasses['ApiPageTriageTemplate'] = $dir . 'api/ApiPageTriageTemplate.php';
$wgAutoloadClasses['ApiPageTriageTagging'] = $dir . 'api/ApiPageTriageTagging.php';

// custom exceptions
$wgAutoloadClasses['MWPageTriageUtilInvalidNumberException'] = $dir . 'includes/PageTriageUtil.php';
$wgAutoloadClasses['MWPageTriageMissingRevisionException'] = $dir . 'includes/PageTriage.php';

// api modules
$wgAPIModules['pagetriagelist'] = 'ApiPageTriageList';
$wgAPIModules['pagetriagestats'] = 'ApiPageTriageStats';
$wgAPIModules['pagetriageaction'] = 'ApiPageTriageAction';
$wgAPIModules['pagetriagetemplate'] = 'ApiPageTriageTemplate';
$wgAPIModules['pagetriagetagging'] = 'ApiPageTriageTagging';

// hooks
$wgHooks['LoadExtensionSchemaUpdates'][] = 'PageTriageHooks::onLoadExtensionSchemaUpdates';
$wgHooks['SpecialMovepageAfterMove'][] = 'PageTriageHooks::onSpecialMovepageAfterMove';
$wgHooks['NewRevisionFromEditComplete'][] = 'PageTriageHooks::onNewRevisionFromEditComplete';
$wgHooks['PageContentInsertComplete'][] = 'PageTriageHooks::onPageContentInsertComplete';
$wgHooks['PageContentSaveComplete'][] = 'PageTriageHooks::onPageContentSaveComplete';
$wgHooks['LinksUpdateComplete'][] = 'PageTriageHooks::onLinksUpdateComplete';
$wgHooks['UnitTestsList'][] = 'efPageTriageUnitTests'; // unit tests
$wgHooks['GetPreferences'][] = 'PageTriageHooks::onGetPreferences';
$wgHooks['ArticleViewFooter'][] = 'PageTriageHooks::onArticleViewFooter';
$wgHooks['ArticleDeleteComplete'][] = 'PageTriageHooks::onArticleDeleteComplete';
$wgHooks['MarkPatrolledComplete'][] = 'PageTriageHooks::onMarkPatrolledComplete';
$wgHooks['BlockIpComplete'][] = 'PageTriageHooks::onBlockIpComplete';
$wgHooks['ResourceLoaderGetConfigVars'][] = 'PageTriageHooks::onResourceLoaderGetConfigVars';
$wgHooks['BeforeCreateEchoEvent'][] = 'PageTriageHooks::onBeforeCreateEchoEvent';
$wgHooks['EchoGetDefaultNotifiedUsers'][] = 'PageTriageHooks::onEchoGetDefaultNotifiedUsers';
$wgHooks['LocalUserCreated'][] = 'PageTriageHooks::onLocalUserCreated';
$wgHooks['UserMergeAccountFields'][] = 'PageTriageHooks::onUserMergeAccountFields';
$wgHooks['ResourceLoaderRegisterModules'][] ='PageTriageHooks::onResourceLoaderRegisterModules';

// logging
$wgLogTypes[] = 'pagetriage-curation';
$wgLogTypes[] = 'pagetriage-deletion';
$wgLogActionsHandlers['pagetriage-curation/reviewed'] = 'LogFormatter';
$wgLogActionsHandlers['pagetriage-curation/unreviewed'] = 'LogFormatter';
$wgLogActionsHandlers['pagetriage-curation/tag'] = 'PageTriageLogFormatter';
$wgLogActionsHandlers['pagetriage-curation/delete'] = 'PageTriageLogFormatter';
$wgLogActionsHandlers['pagetriage-deletion/delete'] = 'PageTriageLogFormatter';

/**
 * UnitTestsList hook handler - adds unit test files to the unit tester
 * @param $files array
 * @return bool
 */
function efPageTriageUnitTests( &$files ) {
	$base = __DIR__ . '/tests';
	$files[] = $base . '/phpunit/SpecialNewPagesFeedTest.php';
	$files[] = $base . '/phpunit/ArticleMetadataTest.php';
	$files[] = $base . '/phpunit/ApiPageTriageActionTest.php';
	return true;
}

// Register ResourceLoader modules
$ptResourceTemplate = [
	'localBasePath' => __DIR__. '/modules',
	'remoteExtPath' => 'PageTriage/modules'
];

// where can the template API find the templates?
$wgPtTemplatePath = $ptResourceTemplate['localBasePath'];

// Tag option messages, in the UI language
// Must be overriden in LocalSettings.php equivalent (as needed) if
// MediaWiki:PageTriageExternalTagsOptions.js is.
$wgPageTriageTagsOptionsMessages = [
	'pagetriage-tags-title',
	'pagetriage-tags-cat-common-label',
	'pagetriage-tags-cat-metadata-label',
	'pagetriage-tags-cat-cleanup-label',
	'pagetriage-tags-cat-neutrality-label',
	'pagetriage-tags-cat-sources-label',
	'pagetriage-tags-cat-structure-label',
	'pagetriage-tags-cat-unwantedcontent-label',
	'pagetriage-tags-cat-verifiability-label',
	'pagetriage-tags-cat-writingstyle-label',
	'pagetriage-tags-cat-moretags-label',
	'pagetriage-tags-cat-all-label',
	'pagetriage-tags-linkrot-label',
	'pagetriage-tags-linkrot-desc',
	'pagetriage-tags-copyedit-label',
	'pagetriage-tags-copyedit-desc',
	'pagetriage-tags-morefootnotes-label',
	'pagetriage-tags-morefootnotes-desc',
	'pagetriage-tags-refimprove-label',
	'pagetriage-tags-refimprove-desc',
	'pagetriage-tags-uncategorised-label',
	'pagetriage-tags-uncategorised-desc',
	'pagetriage-tags-unreferenced-label',
	'pagetriage-tags-unreferenced-desc',
	'pagetriage-tags-deadend-label',
	'pagetriage-tags-deadend-desc',
	'pagetriage-tags-externallinks-label',
	'pagetriage-tags-externallinks-desc',
	'pagetriage-tags-catimprove-label',
	'pagetriage-tags-catimprove-desc',
	'pagetriage-tags-orphan-label',
	'pagetriage-tags-orphan-desc',
	'pagetriage-tags-overlinked-label',
	'pagetriage-tags-overlinked-desc',
	'pagetriage-tags-cleanup-label',
	'pagetriage-tags-cleanup-desc',
	'pagetriage-tags-expertsubject-label',
	'pagetriage-tags-expertsubject-desc',
	'pagetriage-tags-prose-label',
	'pagetriage-tags-prose-desc',
	'pagetriage-tags-roughtranslation-label',
	'pagetriage-tags-roughtranslation-desc',
	'pagetriage-tags-advert-label',
	'pagetriage-tags-advert-desc',
	'pagetriage-tags-autobiography-label',
	'pagetriage-tags-autobiography-desc',
	'pagetriage-tags-coi-label',
	'pagetriage-tags-coi-desc',
	'pagetriage-tags-peacock-label',
	'pagetriage-tags-peacock-desc',
	'pagetriage-tags-pov-label',
	'pagetriage-tags-pov-desc',
	'pagetriage-tags-weasel-label',
	'pagetriage-tags-weasel-desc',
	'pagetriage-tags-blpsources-label',
	'pagetriage-tags-blpsources-desc',
	'pagetriage-tags-originalresearch-label',
	'pagetriage-tags-originalresearch-desc',
	'pagetriage-tags-primarysources-label',
	'pagetriage-tags-primarysources-desc',
	'pagetriage-tags-onesource-label',
	'pagetriage-tags-onesource-desc',
	'pagetriage-tags-condense-label',
	'pagetriage-tags-condense-desc',
	'pagetriage-tags-leadmissing-label',
	'pagetriage-tags-leadmissing-desc',
	'pagetriage-tags-leadrewrite-label',
	'pagetriage-tags-leadrewrite-desc',
	'pagetriage-tags-leadtoolong-label',
	'pagetriage-tags-leadtoolong-desc',
	'pagetriage-tags-leadtooshort-label',
	'pagetriage-tags-leadtooshort-desc',
	'pagetriage-tags-cleanupreorganise-label',
	'pagetriage-tags-cleanupreorganise-desc',
	'pagetriage-tags-sections-label',
	'pagetriage-tags-sections-desc',
	'pagetriage-tags-stub-label',
	'pagetriage-tags-stub-desc',
	'pagetriage-tags-verylong-label',
	'pagetriage-tags-verylong-desc',
	'pagetriage-tags-closeparaphrasing-label',
	'pagetriage-tags-closeparaphrasing-desc',
	'pagetriage-tags-copypaste-label',
	'pagetriage-tags-copypaste-desc',
	'pagetriage-tags-nonfree-label',
	'pagetriage-tags-nonfree-desc',
	'pagetriage-tags-notability-label',
	'pagetriage-tags-notability-desc',
	'pagetriage-tags-disputed-label',
	'pagetriage-tags-disputed-desc',
	'pagetriage-tags-citationstyle-label',
	'pagetriage-tags-citationstyle-desc',
	'pagetriage-tags-hoax-label',
	'pagetriage-tags-hoax-desc',
	'pagetriage-tags-nofootnotes-label',
	'pagetriage-tags-nofootnotes-desc',
	'pagetriage-tags-confusing-label',
	'pagetriage-tags-confusing-desc',
	'pagetriage-tags-essaylike-label',
	'pagetriage-tags-essaylike-desc',
	'pagetriage-tags-fansite-label',
	'pagetriage-tags-fansite-desc',
	'pagetriage-tags-notenglish-label',
	'pagetriage-tags-notenglish-desc',
	'pagetriage-tags-technical-label',
	'pagetriage-tags-technical-desc',
	'pagetriage-tags-tense-label',
	'pagetriage-tags-tense-desc',
	'pagetriage-tags-tone-label',
	'pagetriage-tags-tone-desc',
	'pagetriage-tags-allplot-label',
	'pagetriage-tags-allplot-desc',
	'pagetriage-tags-fiction-label',
	'pagetriage-tags-fiction-desc',
	'pagetriage-tags-inuniverse-label',
	'pagetriage-tags-inuniverse-desc',
	'pagetriage-tags-outofdate-label',
	'pagetriage-tags-outofdate-desc',
	'pagetriage-tags-overlydetailed-label',
	'pagetriage-tags-overlydetailed-desc',
	'pagetriage-tags-plot-label',
	'pagetriage-tags-plot-desc',
	'pagetriage-tags-recentism-label',
	'pagetriage-tags-recentism-desc',
	'pagetriage-tags-toofewopinions-label',
	'pagetriage-tags-toofewopinions-desc',
	'pagetriage-tags-unbalanced-label',
	'pagetriage-tags-unbalanced-desc',
	'pagetriage-tags-update-label',
	'pagetriage-tags-update-desc',
	'pagetriage-tags-param-date-label',
	'pagetriage-tags-param-issues-label',
	'pagetriage-tags-param-blp-label',
	'pagetriage-tags-param-source-label',
	'pagetriage-tags-param-free-label',
	'pagetriage-tags-param-url-label',
	'pagetriage-tag-count-total',
	'pagetriage-button-add-tag',
	'pagetriage-button-add-tag-number',
	'pagetriage-button-add-parameters',
	'pagetriage-button-add-details',
	'pagetriage-button-edit-details',
	'pagetriage-button-mark-for-deletion',
	'cancel',
	'pagetriage-tags-param-free-yes-label',
	'pagetriage-tags-param-free-no-label',
	'pagetriage-tags-param-missing-required',
	'pagetriage-tags-param-date-format',
	'pagetriage-tags-param-for-label',
	'pagetriage-tags-tooltip',
];

// Deletion tag option messages, in the UI language
// Must be overriden in LocalSettings.php equivalent (as needed) if
// MediaWiki:PageTriageExternalDeletionTagsOptions.js is.
$wgPageTriageDeletionTagsOptionsMessages = [
	'pagetriage-del-tags-cat-csd-label',
	'pagetriage-del-tags-cat-csd-desc',
	'pagetriage-del-tags-cat-prod-label',
	'pagetriage-del-tags-cat-prod-desc',
	'pagetriage-del-tags-cat-discussion-desc',
	'pagetriage-del-tags-dbg3-label',
	'pagetriage-del-tags-dbg3-desc',
	'pagetriage-del-tags-dbg10-label',
	'pagetriage-del-tags-dbg10-desc',
	'pagetriage-del-tags-dbg11-label',
	'pagetriage-del-tags-dbg11-desc',
	'pagetriage-del-tags-dbg12-label',
	'pagetriage-del-tags-dbg12-desc',
	'pagetriage-del-tags-dba1-label',
	'pagetriage-del-tags-dba1-desc',
	'pagetriage-del-tags-dba7-label',
	'pagetriage-del-tags-dba7-desc',
	'pagetriage-del-tags-dbg1-label',
	'pagetriage-del-tags-dbg1-desc',
	'pagetriage-del-tags-dba3-label',
	'pagetriage-del-tags-dba3-desc',
	'pagetriage-del-tags-dbg2-label',
	'pagetriage-del-tags-dbg2-desc',
	'pagetriage-del-tags-dbg4-label',
	'pagetriage-del-tags-dbg4-desc',
	'pagetriage-del-tags-dbg5-label',
	'pagetriage-del-tags-dbg5-desc',
	'pagetriage-del-tags-dbg7-label',
	'pagetriage-del-tags-dbg7-desc',
	'pagetriage-del-tags-dba10-label',
	'pagetriage-del-tags-dba10-desc',
	'pagetriage-del-tags-dba2-label',
	'pagetriage-del-tags-dba2-desc',
	'pagetriage-del-tags-dbu2-label',
	'pagetriage-del-tags-dbu2-desc',
	'pagetriage-del-tags-dbu3-label',
	'pagetriage-del-tags-dbu3-desc',
	'pagetriage-del-tags-dba9-label',
	'pagetriage-del-tags-dba9-desc',
	'pagetriage-del-tags-blpprod-label',
	'pagetriage-del-tags-blpprod-desc',
	'pagetriage-del-tags-prod-label',
	'pagetriage-del-tags-prod-desc',
	'pagetriage-del-tags-articlefordeletion-label',
	'pagetriage-del-tags-redirectsfordiscussion-label',
	'pagetriage-del-tags-miscellanyfordeletion-label',
	'pagetriage-tags-param-article-label',
	'pagetriage-tags-param-url-label',
	'pagetriage-tags-param-source-label',
	'pagetriage-del-tags-param-discussion-label',
	'pagetriage-del-title',
	'pagetriage-del-tooltip',
	'pagetriage-tags-param-url-label',
	'pagetriage-tags-param-article-label',
	'pagetriage-tags-param-source-label',
];

// Deletion tag option messages, in the wiki's content language
// Must be overriden in LocalSettings.php equivalent (as needed) if
// MediaWiki:PageTriageExternalDeletionTagsOptions.js is.
$wgPageTriageDeletionTagsOptionsContentLanguageMessages = [
	'pagetriage-del-tags-prod-notify-topic-title',
	'pagetriage-del-tags-speedy-deletion-nomination-notify-topic-title',
	'pagetriage-del-tags-xfd-notify-topic-title',
];

$wgResourceModules['ext.pageTriage.external'] = $ptResourceTemplate + [
	'scripts' => [
		'external/underscore.js',
		'external/backbone.js', // required for underscore
		'external/date.js',
		'external/datejs-mw.js',
		'external/jquery.waypoints.js'
	],
	'messages' => [
		'sunday',
		'monday',
		'tuesday',
		'wednesday',
		'thursday',
		'friday',
		'saturday',
		'sun',
		'mon',
		'tue',
		'wed',
		'thu',
		'fri',
		'sat',
		'january',
		'february',
		'march',
		'april',
		'may_long',
		'june',
		'july',
		'august',
		'september',
		'october',
		'november',
		'december',
		'jan',
		'feb',
		'mar',
		'apr',
		'may',
		'jun',
		'jul',
		'aug',
		'sep',
		'oct',
		'nov',
		'dec'
	]
];

$wgResourceModules['ext.pageTriage.init'] = $ptResourceTemplate + [
	'scripts' => [
		'ext.pageTriage.init/ext.pageTriage.init.js',
	],
	'dependencies' => [
		'ext.pageTriage.external',
	],
];

$wgResourceModules['ext.pageTriage.util'] = $ptResourceTemplate + [
	'scripts' => [
		// convenience functions for all views
		'ext.pageTriage.util/ext.pageTriage.viewUtil.js',

		// Message infrastructure (e.g. for content language messages)
		'ext.pageTriage.util/ext.pageTriage.messageUtil.js',
	],
	'messages' => [
		'pagetriage-api-error'
	],
	'dependencies' => [
		'ext.pageTriage.init',
	],
];

$wgResourceModules['ext.pageTriage.models'] = $ptResourceTemplate + [
	'dependencies' => [
		'mediawiki.Title',
		'mediawiki.user',
		'ext.pageTriage.init',
	],
	'scripts' => [
		'ext.pageTriage.models/ext.pageTriage.article.js',
		'ext.pageTriage.models/ext.pageTriage.revision.js',
		'ext.pageTriage.models/ext.pageTriage.stats.js'
	],
	'messages' => [
		'pipe-separator',
		'pagetriage-info-timestamp-date-format',
		'pagetriage-page-status-unreviewed',
		'pagetriage-page-status-autoreviewed',
		'pagetriage-page-status-reviewed',
		'pagetriage-page-status-delete',
		'pagetriage-page-status-reviewed-anonymous'
	],
];

$wgResourceModules['jquery.tipoff'] = $ptResourceTemplate + [
	'styles' => 'jquery.tipoff/jquery.tipoff.css',
	'scripts' => 'jquery.tipoff/jquery.tipoff.js'
];

$wgResourceModules['ext.pageTriage.views.list'] = $ptResourceTemplate + [
	'dependencies' => [
		'mediawiki.jqueryMsg',
		'ext.pageTriage.models',
		'ext.pageTriage.util',
		'jquery.tipoff',
		'jquery.ui.button',
		'jquery.spinner',
		'jquery.client'
	],
	'scripts' => [
		'ext.pageTriage.views.list/ext.pageTriage.listItem.js',
		'ext.pageTriage.views.list/ext.pageTriage.listControlNav.js',
		'ext.pageTriage.views.list/ext.pageTriage.listStatsNav.js',
		'ext.pageTriage.views.list/ext.pageTriage.listView.js'
	],
	'styles' => [
		'ext.pageTriage.css', // stuff that's shared across all views
		'ext.pageTriage.views.list/ext.pageTriage.listItem.css',
		'ext.pageTriage.views.list/ext.pageTriage.listControlNav.css',
		'ext.pageTriage.views.list/ext.pageTriage.listStatsNav.css',
		'ext.pageTriage.views.list/ext.pageTriage.listView.css'
	],
	'messages' => [
		'comma-separator',
		'days',
		'pagetriage-hist',
		'pagetriage-bytes',
		'pagetriage-edits',
		'pagetriage-categories',
		'pagetriage-no-categories',
		'pagetriage-orphan',
		'pagetriage-no-author',
		'pagetriage-byline',
		'pagetriage-byline-new-editor',
		'pipe-separator',
		'pagetriage-editcount',
		'pagetriage-author-not-autoconfirmed',
		'pagetriage-no-patrol-right',
		'pagetriage-author-blocked',
		'pagetriage-author-bot',
		'pagetriage-creation-dateformat',
		'pagetriage-user-creation-dateformat',
		'pagetriage-special-contributions',
		'pagetriage-showing',
		'pagetriage-filter-list-prompt',
		'pagetriage-unreviewed-article-count',
		'pagetriage-reviewed-article-count-past-week',
		'pagetriage-sort-by',
		'pagetriage-newest',
		'pagetriage-oldest',
		'pagetriage-triage',
		'pagetriage-filter-show-heading',
		'pagetriage-filter-reviewed-edits',
		'pagetriage-filter-unreviewed-edits',
		'pagetriage-filter-nominated-for-deletion',
		'pagetriage-filter-bot-edits',
		'pagetriage-filter-redirects',
		'pagetriage-filter-namespace-heading',
		'pagetriage-filter-article',
		'pagetriage-filter-user-heading',
		'pagetriage-filter-username',
		'pagetriage-filter-tag-heading',
		'pagetriage-filter-second-show-heading',
		'pagetriage-filter-no-categories',
		'pagetriage-filter-orphan',
		'pagetriage-filter-non-autoconfirmed',
		'pagetriage-filter-blocked',
		'pagetriage-filter-set-button',
		'pagetriage-stats-less-than-a-day',
		'blanknamespace',
		'pagetriage-filter-ns-all',
		'pagetriage-more',
		'pagetriage-filter-stat-reviewed',
		'pagetriage-filter-stat-unreviewed',
		'pagetriage-filter-stat-bots',
		'pagetriage-filter-stat-redirects',
		'pagetriage-filter-stat-nominated-for-deletion',
		'pagetriage-filter-stat-all',
		'pagetriage-filter-stat-no-categories',
		'pagetriage-filter-stat-orphan',
		'pagetriage-filter-stat-non-autoconfirmed',
		'pagetriage-filter-stat-blocked',
		'pagetriage-filter-stat-username',
		'pagetriage-filter-all',
		'pagetriage-no-pages',
		'pagetriage-warning-browser',
		'pagetriage-note-reviewed',
		'pagetriage-note-not-reviewed',
		'pagetriage-note-deletion',
		'pagetriage-refresh-list',
		'sp-contributions-talk',
		'contribslink',
		'comma-separator',
		'pagetriage-info-timestamp-date-format',
		'pagetriage-no-reference',
		'pagetriage-stats-filter-page-count'
	]
];

$wgResourceModules['ext.pageTriage.defaultTagsOptions'] = $ptResourceTemplate + [
	'scripts' => 'ext.pageTriage.defaultTagsOptions/ext.pageTriage.defaultTagsOptions.js',
	'messages' => $wgPageTriageTagsOptionsMessages,
];

$wgResourceModules['ext.pageTriage.externalTagsOptions'] = $ptResourceTemplate + [
	'class' => 'PageTriageExternalTagsOptions',
];

$wgResourceModules['ext.pageTriage.defaultDeletionTagsOptions'] = $ptResourceTemplate + [
	'scripts' => 'ext.pageTriage.defaultDeletionTagsOptions/'
		. 'ext.pageTriage.defaultDeletionTagsOptions.js',
	'messages' => $wgPageTriageDeletionTagsOptionsMessages,
	'dependencies' => [
		'mediawiki.Title',
		'ext.pageTriage.messages',
	]
];

$wgResourceModules['ext.pageTriage.externalDeletionTagsOptions'] = $ptResourceTemplate + [
	'class' => 'PageTriageExternalDeletionTagsOptions',
];

$wgResourceModules['ext.pageTriage.toolbarStartup'] = $ptResourceTemplate + [
	'scripts' => 'ext.pageTriage.toolbarStartup/ext.pageTriage.toolbarStartup.js',
];

$wgResourceModules['ext.pageTriage.article'] = $ptResourceTemplate + [
	'styles' => 'ext.pageTriage.article/ext.pageTriage.article.css',
	'scripts' => 'ext.pageTriage.article/ext.pageTriage.article.js',
	'messages' => [
			'pagetriage-reviewed',
			'pagetriage-mark-as-reviewed-error',
	],
	'dependencies' => [
		'ext.pageTriage.init',
	],
];

/** Rate limit setting for PageTriage **/
$wgRateLimits += [
	'pagetriage-mark-action' => [
			'anon' => [ 1, 3 ],
			'user' => [ 1, 3 ]
	],

	'pagetriage-tagging-action' => [
			'anon' => [ 1, 10 ],
			'user' => [ 1, 10 ]
	]
];
