<?php
$cfg = require __DIR__ . '/../../vendor/mediawiki/mediawiki-phan-config/src/config.php';
$cfg['directory_list'] = array_merge(
	$cfg['directory_list'],
	[
		'./cron',
		'./tools',
		'./../../extensions/Echo',
		'./../../extensions/ORES',
	]
);
$cfg['exclude_analysis_directory_list'] = array_merge(
	$cfg['exclude_analysis_directory_list'],
	[
		'./../../extensions/Echo',
		'./../../extensions/ORES',

	]
);
// DB_MASTER
$cfg['suppress_issue_types'][] = 'PhanUndeclaredConstant';

return $cfg;
