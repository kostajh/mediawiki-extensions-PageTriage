<?php
/**
 * Internationalisation for Page Triage extension
 *
 * @file
 * @ingroup Extensions
 */

$messages = array();

/** English
 * @author Ryan Kaldari
 */
$messages['en'] = array(
	'pagetriage' => 'Page Triage',
	'pagetriage-new-pages-feed' => 'New Pages Feed',
	'pagetriage-desc' => 'Facilitates reviewing and approving new pages',
	'pagetriage-api-invalidid' => 'The ID you provided ($1) is not valid.',
	'pagetriage-markpatrolled' => 'Mark this page as reviewed',
	'pagetriage-self-review-error' => "You can't review pages you've created.",
	'pagetriage-reviewed' => 'Reviewed',
	'pagetriage-unreviewed' => 'Unreviewed',
	'pagetriage-mark-as-reviewed-error' => 'Error occurred in marking as reviewed',
	'pagetriage-hist' => 'hist',
	'pagetriage-bytes' => '$1 {{PLURAL:$1|byte|bytes}}',
	'pagetriage-edits' => '$1 {{PLURAL:$1|edit|edits}}',
	'pagetriage-categories' => '$1 {{PLURAL:$1|category|categories}}',
	'pagetriage-no-categories' => 'No categories',
	'pagetriage-orphan' => 'Orphan',
	'pagetriage-no-author' => 'No author information present',
	'pagetriage-byline' => 'By',
	'pagetriage-editcount' => '$1 {{PLURAL:$1|edit|edits}} since $2',
	'pagetriage-author-not-autoconfirmed' => 'New editor',
	'pagetriage-author-blocked' => 'Blocked',
	'pagetriage-author-bot' => 'Bot',
	'pagetriage-creation-dateformat' => 'HH:mm, d MMMM yyyy',
	'pagetriage-user-creation-dateformat' => 'yyyy-MM-dd',
	'pagetriage-special-contributions' => 'Special:Contributions', // FIXME: Can this be replaced with {{#special:Contributions}} for automatic translation?
	'pagetriage-showing' => 'Showing',
	'pagetriage-filter-list-prompt' => 'Filter list',
	'pagetriage-article-count' => 'There {{PLURAL:$1|is currently $1 unreviewed page|are currently $1 unreviewed pages}}',
	'pagetriage-viewing' => 'Viewing',
	'pagetriage-sort-by' => 'Sort by:',
	'pagetriage-newest' => 'Newest',
	'pagetriage-oldest' => 'Oldest',
	'pagetriage-triage' => 'Review',
	'pagetriage-filter-show-heading' => 'Include:',
	'pagetriage-filter-reviewed-edits' => 'Reviewed pages',
	'pagetriage-filter-nominated-for-deletion' => 'Nominated for deletion',
	'pagetriage-filter-bot-edits' => 'Were created by bots',
	'pagetriage-filter-redirects' => 'Redirects',
	'pagetriage-filter-namespace-heading' => 'In namespace:',
	'pagetriage-filter-user-heading' => 'Were created by user:',
	'pagetriage-filter-tag-heading' => 'With tag:',
	'pagetriage-filter-second-show-heading' => 'That:',
	'pagetriage-filter-no-categories' => 'Have no categories',
	'pagetriage-filter-orphan' => 'Are orphaned',
	'pagetriage-filter-non-autoconfirmed' => 'Were created by new editors',
	'pagetriage-filter-blocked' => 'Were created by blocked users',
	'pagetriage-filter-all' => 'Show all',
	'pagetriage-filter-set-button' => 'Set filters',
	'pagetriage-stats-unreviewed-age' => '<span class="mwe-pt-stats-label">Page ages:</span> Average: $1, oldest: $2',
	'pagetriage-stats-less-than-a-day' => 'less than one day',
	'pagetriage-stats-top-reviewers' => 'Top {{PLURAL:$1|reviewer|$1 reviewers}}:',
	'pagetriage-filter-ns-all' => 'All',
	'pagetriage-more' => 'More',
	'pagetriage-filter-stat-all' => 'All',
	'pagetriage-filter-stat-namespace' => 'Namespace: $1',
	'pagetriage-filter-stat-reviewed' => 'Include reviewed',
	'pagetriage-filter-stat-bots' => 'Bots',
	'pagetriage-filter-stat-redirects' => 'Include redirects',
	'pagetriage-filter-stat-nominated-for-deletion' => 'Include deleted',
	'pagetriage-filter-stat-no-categories' => 'No categories',
	'pagetriage-filter-stat-orphan' => 'Orphans',
	'pagetriage-filter-stat-non-autoconfirmed' => 'New editors',
	'pagetriage-filter-stat-blocked' => 'Blocked users',
	'pagetriage-filter-stat-username' => 'Username: $1',
	'pagetriage-no-pages' => 'No pages match your criteria.',
	'pagetriage-warning-prototype' => 'This is a prototype, not a final product. Key features are still in development. <a href="$1">Learn more</a> · <a href="$2">Leave feedback</a>',
	'pagetriage-warning-browser' => 'This tool may not work correctly in browsers older than Internet Explorer 8.',
);

/**
 * Message documentation (Message documentation)
 */
$messages['qqq'] = array(
	'pagetriage' => 'The name of this application (Page Triage)',
	'pagetriage-new-pages-feed' => 'Title of a special page',
	'pagetriage-desc' => '{{desc}}',
	'pagetriage-api-invalidid' => 'Invalid title error message for pagetriage API',
	'pagetriage-markpatrolled' => 'Button text for the mark-as-patrolled button',
	'pagetriage-self-review-error' => 'An error message to display when someone tries to review a page they created themselves',
	'pagetriage-reviewed' => 'Text to indicate a page has been reviewed',
	'pagetriage-mark-as-reviewed-error' => 'Generic error message for marking as reviewed',
	'pagetriage-bytes' => 'The number of bytes in the article',
	'pagetriage-edits' => 'The number of times the article has been edited',
	'pagetriage-categories' => 'The number of categories in the article',
	'pagetriage-no-categories' => 'Label indicating an article with no categories',
	'pagetriage-orphan' => 'Label indicating an article has no external links (orphan)',
	'pagetriage-no-author' => 'Error message for missing article author information',
	'pagetriage-byline' => 'Text indicating the article author (username comes after). No $1 because the username is a hyperlink.',
	'pagetriage-editcount' => 'Display of article author\'s editing experience. $1 is total edit count, $2 is author\'s join date',
	'pagetriage-author-not-autoconfirmed' => 'String indicating that the author was not yet autoconfirmed when the article was last edited',
	'pagetriage-author-blocked' => 'String indicating that the author was blocked when the article was last edited',
	'pagetriage-author-bot' => 'String indicating that the author is a bot',
	'pagetriage-creation-dateformat' => 'Format specifier for the article creation date. Month and weekday names will be localized. For formats, see: http://code.google.com/p/datejs/wiki/FormatSpecifiers',
	'pagetriage-user-creation-dateformat' => 'Format specifier for the author\'s account creation date. Month and weekday names will be localized. For formats, see: http://code.google.com/p/datejs/wiki/FormatSpecifiers',
	'pagetriage-special-contributions' => 'The name of Special:Contributions on this wiki',
	'pagetriage-showing' => 'The label for which filters are being shown',
	'pagetriage-filter-list-prompt' => 'Prompt to choose filters for the list view',
	'pagetriage-article-count' => 'A description of the number of unreviewed articles. $1 is the count.',
	'pagetriage-viewing' => 'Label for the sort-order buttons (oldest/newest)',
	'pagetriage-sort-by' => 'Label for the sort-order buttons (oldest/newest)',
	'pagetriage-newest' => 'Text for a sort-order button',
	'pagetriage-oldest' => 'Text for a sort-order button',
	'pagetriage-filter-show-heading' => 'Prompt for the first set of checkboxes in the filter menu',
	'pagetriage-filter-reviewed-edits' => 'Checkbox text for reviewed articles',
	'pagetriage-filter-nominated-for-deletion' => 'Checkbox text for articles nominated for deletion',
	'pagetriage-filter-bot-edits' => 'Checkbox text for articles by bots',
	'pagetriage-filter-redirects' => 'Checkbox text for redirect articles',
	'pagetriage-filter-namespace-heading' => 'Prompt for the namespace to display',
	'pagetriage-filter-user-heading' => 'Prompt for the user to find articles by',
	'pagetriage-filter-tag-heading' => 'Prompt to find articles with a given tag',
	'pagetriage-filter-second-show-heading' => 'Prompt for the second set of checkboxes in the filter menu',
	'pagetriage-filter-no-categories' => 'Checkbox text for articles with no categories',
	'pagetriage-filter-orphan' => 'Checkbox text for orphan articles',
	'pagetriage-filter-non-autoconfirmed' => 'Checkbox text for articles by non-Autoconfirmed users',
	'pagetriage-filter-blocked' => 'Checkbox text for articles by blocked users',
	'pagetriage-filter-all' => 'Radio button text for all items radio button',
	'pagetriage-filter-set-button' => 'Button text for the set filter button',
	'pagetriage-stats-unreviewed-age' => 'Navigation text displaying triage stats, $1 and $2 are the ages of average and oldest articles respectively',
	'pagetriage-stats-less-than-a-day' => 'show this message if the article age is less than one day, part of variable $1 and $2 of {{msg-pagetriage|pagetriage-stats-unreviewed-age}} ',
	'pagetriage-stats-top-reviewers' => 'Text that shows the top reviewers; $1 is the total number or reviewers',
	'pagetriage-filter-ns-all' => 'For the namespace filter select list, text indicating that all namespaces will be selected',
	'pagetriage-more' => 'Text for a link that loads more articles into list',
	'pagetriage-filter-stat-all' => 'Status display component for all pages (no filter)',
	'pagetriage-filter-stat-namespace' => 'Status display component for the namespace filter. $1 is the name of the namespace.',
	'pagetriage-filter-stat-reviewed' => 'Status display component for reviewed pages',
	'pagetriage-filter-stat-bots' => 'Status display component for bot-created pages',
	'pagetriage-filter-stat-redirects' => 'Status display component for redirects',
	'pagetriage-filter-stat-nominated-for-deletion' => 'Status dispay component for articles nominated for deletion',
	'pagetriage-filter-stat-no-categories' => 'Status display component for articles with no categories',
	'pagetriage-filter-stat-orphan' => 'Status display component for orphan articles',
	'pagetriage-filter-stat-non-autoconfirmed' => 'Status display component for articles by non-autoconfirmed editors',
	'pagetriage-filter-stat-blocked' => 'Status display component for articles by blocked users',
	'pagetriage-filter-stat-username' => 'Status display component for filter by username. $1 is the username.',
	'pagetriage-no-pages' => 'Message to display when no pages were retrieved',
	'pagetriage-warning-prototype' => 'Warning message. $1 and $2 are URLs',
);
