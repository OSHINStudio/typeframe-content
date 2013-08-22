<?php
/**
 * Typeframe Content application
 *
 * admin-side plug controller
 */

Plugin_Breadcrumbs::Add('Edit Plugin');

// save typing below
$typef_app_dir = Typeframe::CurrentPage()->applicationUri();

// validate plug id (must be 'Content' plug)
$plugid = @$_REQUEST['plugid'];
$plug   = Model_Plug::Get($plugid);
if (!$plug->exists() || ('Content' != $plug->get('plug')))
{
	Typeframe::Redirect('Invalid plugin.', $typef_app_dir, -1);
	return;
}

// get template from settings; expand to its full value
$settings      = $plug['settings'];
$template      = (isset($settings['template']) ? $settings['template'] : 'generic.plug.html');
$full_template = (TYPEF_SOURCE_DIR . '/templates/content/' . $template);

// cannot edit content if template does not exist
if (!file_exists($full_template))
{
	Typeframe::Redirect("Unable to find plugin template ($template).", $typef_app_dir, -1);
	return;
}

// load inserts and groups for template
$inserts = Insertable::ElementsFrom($full_template);
$groups  = Insertable::GroupsFrom($full_template);

// get revision id, if any
$revisionid = @$_REQUEST['revisionid'];

// process form
if ('POST' == $_SERVER['REQUEST_METHOD'])
{
	// build content array from post
	$content = Content::ProcessPOST($inserts, $groups);
	// add/edit content
	//$content_plug = new Content_Plug($plugid, $revisionid);
	$content_plug = Model_Content_Plug::Get($plugid);
	if (!$content_plug->exists()) {
		$content_plug = Model_Content_Plug::Create();
		$content_plug['plugid'] = $plugid;
	}
	$content_plug->set('content', $content);
	$content_plug->save();

	// done; redirect
	Typeframe::Redirect('Plugin content updated.', $typef_app_dir);
	return;
}

// load values from content; add inserts and groups to template
//list($plug_content, $inserts, $groups) = Content_Plug::LoadData($plugid, $revisionid, $inserts, $groups);
$plug = Model_Content_Plug::Get($plugid);
if ($revisionid) {
	$revision = Model_Content_Plug::Get($revisionid);
	$plug_content = $revision['data'];
} else {
	$plug_content = $plug['content'];
}
$pm->setVariable('content', $plug_content);
$pm->setVariable('inserts',      $inserts);
$pm->setVariable('groups',       $groups);

// add other variables to template
$pm->setVariable('plugid', $plugid);

// "set to default" option is only available to enterprise installs on a child site
if (TYPEF_ENT && (0 != TypeframeEnterprise::GetCurrentChildID()))
	$pm->setVariable('can_default_value', true);

// add template to (pagemill) template
$pm->setVariable('template', "/content/$template");

$pm->setVariable('group_url', TYPEF_WEB_DIR . '/admin/content/groups/form?plugid=' . $plugid);
