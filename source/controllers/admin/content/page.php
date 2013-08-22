<?php
/**
 * Typeframe Content application
 *
 * admin-side page controller
 */

Plugin_Breadcrumbs::Add('Edit Page');

// save typing below
$typef_app_dir = Typeframe::CurrentPage()->applicationUri();

// validate page id (must be 'Content' page)
$pageid = @$_REQUEST['pageid'];
$page = Model_Page::Get($pageid);
if (!$page->exists() || ('Content' != $page->get('application')) || $page['siteid'] != Typeframe::CurrentPage()->siteid())
{
	Typeframe::Redirect('Invalid page.', $typef_app_dir, -1);
	return;
}

// get template from settings; expand to its full value
$settings      = $page['settings'];
$template      = @$settings['template'];
if (!$template) $template = 'generic.html';
$full_template = (TYPEF_SOURCE_DIR . "/templates/content/$template");

// cannot edit content if template does not exist
if (!file_exists($full_template))
{
	Typeframe::Redirect("Unable to find page template ($template).", $typef_app_dir, -1);
	return;
}

$skin = Typeframe_Skin::At(TYPEF_WEB_DIR . $page['uri']);
$stylesheets = Typeframe_Skin::StylesheetsFor('/content/' . $template, TYPEF_WEB_DIR . $page['uri']);
$pm->setVariable('stylesheets', $stylesheets);

// load inserts and groups for template
$inserts = Insertable::ElementsFrom($full_template, TYPEF_WEB_DIR . $page['uri']);
$groups  = Insertable::GroupsFrom($full_template, null, $skin);

// get revision id, if any
$revisionid = @$_REQUEST['revisionid'];

// process form
if ('POST' == $_SERVER['REQUEST_METHOD'])
{
	if (!empty($_POST['cmd']) && $_POST['cmd'] == 'Preview') {
		Typeframe_Skin::Set(Typeframe_Skin::At(TYPEF_WEB_DIR . $page['uri']));
		Typeframe::CurrentPage()->setPageTemplate('/content/' . $page['settings']['template']);
		return;
	}
	// build content array from post
	$content = Content::ProcessPost($inserts, $groups);
	// add/edit content
	//$content_page = new Content_Page($pageid, $revisionid);

	$content_page = Model_Content_Page::Get($pageid);
	if (!$content_page->exists()) {
		$content_page = Model_Content_Page::Create();
		$content_page['pageid'] = $pageid;
	}
	$content_page['content'] = $content;
	//$content_page->set('content', json_encode($content));
	$content_page->save();

	// done; redirect
	Typeframe::Redirect('Page content updated.', $typef_app_dir);
	return;
}

$page = Model_Content_Page::Get($pageid);
if ($revisionid) {
	$pm->setVariable('revisionid', $revisionid);
	$revision = Model_Content_PageRevision::Get($revisionid);
	$page['content'] = $revision['data']['content'];
}
$inserts = Insertable::ElementsFrom($full_template);
$groups = Insertable::GroupsFrom($full_template);
$pm->setVariable('content', $page['content']);
$pm->setVariable('revisions', $page['revisions']);
$pm->setVariable('inserts', $inserts);
$pm->setVariable('groups', $groups);

// save typing below
$page_uri      = $page->get('uri');
$full_page_uri = (TYPEF_WEB_DIR . $page_uri);

// determine if we redirect to front (client side) or admin side on save
$redirecttofront = (isset($_REQUEST['redirecttofront'])? $_REQUEST['redirecttofront'] : 0);
$redirecturl     = ($redirecttofront ? $full_page_uri : $typef_app_dir);
$pm->setVariable('redirecttofront', $redirecttofront);
$pm->setVariable('redirecturl',     $redirecturl);

// add other variables to template
$pm->setVariable('pageid',     $pageid);
$pm->setVariable('revisionid', $revisionid);
$pm->setVariable('pageuri',    $page_uri);
$pm->setVariable('nickname',  ($page->get('nickname') ? $page->get('nickname') : $page_uri));

// "set to default" option is only available to enterprise installs on a child site
if (TYPEF_ENT && (0 != TypeframeEnterprise::GetCurrentChildID()))
	$pm->setVariable('can_default_value', true);

// add template to (pagemill) template
$pm->setVariable('template', "/content/$template");

// add driverables to template
//$pm->setVariable('driverables', Page::LoadDriverables());

// optionally, get stylesheets; add to template if not empty
/*if (CONTENT_USE_PAGE_STYLE)
{
	$stylesheets = Pagemill_Tag_Stylesheets::GetStylesheetsAt($full_page_uri);
	if (!empty($stylesheets))
		$pm->setVariable('editor_stylesheets', ("['" . implode("','", $stylesheets) . "']"));
}*/
$pm->setVariable('group_url', TYPEF_WEB_DIR . '/admin/content/groups/form?pageid=' . $pageid);
