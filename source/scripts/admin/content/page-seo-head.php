<?php
if (Typeframe::Allow(TYPEF_WEB_DIR . '/admin/seo-head')) {
	$inserts = $pm->getVariable('inserts');
	if (!is_array($inserts)) $inserts = array();
	$seo = array();
	$seo[] = array(
		'name' => 'seo_head_title',
		'label' => 'Page Title',
		'type' => 'text'
	);
	$seo[] = array(
		'name' => 'seo_head_description',
		'label' => 'Meta Description',
		'type' => 'text'
	);
	$seo[] = array(
		'name' => 'seo_head_keywords',
		'label' => 'Meta Keywords',
		'type' => 'text'
	);
	$pm->setVariable('inserts', array_mergs($seo, $inserts));
}
