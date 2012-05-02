<?php
/**
 * Custom ResourceLoader module that loads a custom PageTriageTagsOptions.js per-wiki.
 */
class PageTriageExternalTagsOptions extends ResourceLoaderWikiModule {

	/**
	 * @param $context ResourceLoaderContext
	 * @return array
	 */
	protected function getPages( ResourceLoaderContext $context ) {
		return array(
			'MediaWiki:PageTriageExternalTagsOptions.js' => array( 'type' => 'script' ),
		);
	}

	/**
	 * @return array
	 */
	public function getDependencies() {
		return array( 'ext.pageTriage.defaultTagsOptions' );
	}

	/**
	 * @return array
	 */
	public function getMessages() {
		global $wgPageTriageTagsOptionsMessages;
		return $wgPageTriageTagsOptionsMessages;
	}
}