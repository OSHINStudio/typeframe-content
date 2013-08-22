<?php
/**
 * Typeframe Content application
 *
 * admin-side groups form controller
 */

// save typing below
$typef_app_dir = Typeframe::CurrentPage()->applicationUri();

// break up incoming base
$base = $_REQUEST['base'];

if (preg_match_all('/\[?([a-z0-9_]*)\]?\[[\d]*\]/i', $base, $matches))
{
	// use the matches to shape an xpath query identifying the group
	$full_template = (TYPEF_SOURCE_DIR . '/templates/' . $_REQUEST['template']);
	//$skin = Typeframe_Skin::At(TYPEF_WEB_DIR . $page['uri']);
	if (!empty($_REQUEST['pageid'])) {
		$page = Model_Page::Get($_REQUEST['pageid']);
		if ($page->exists()) {
			$skin = Typeframe_Skin::At(TYPEF_WEB_DIR . $page['uri']);
			if (CONTENT_USE_PAGE_STYLE) {
				//$stylesheets = Pagemill_Tag_Stylesheets::GetStylesheetsAt(TYPEF_WEB_DIR . $page['uri']);
				$stylesheets = array();
				if (!empty($stylesheets)) {
					$pm->setVariable('editor_stylesheets', ("['" . implode("','", $stylesheets) . "']"));
				}
			}
		} else {
			$skin = 'default';
		}
	} else {
		$skin = 'default';
	}
	$groups        = Insertable::GroupsFrom($full_template, $matches[1], $skin);
	$group         = $groups[0];

	// @todo Check permissions here?
	if ('POST' == $_SERVER['REQUEST_METHOD'])
	{
		if (isset($_POST['action']) && ('Cancel' != $_POST['action']))
		{
			$content = array();
			foreach ($group['members'] as $member)
			{
				$key = $member['name'];
				if ('image' == $member['type'])
				{
					$value = basename(FileManager::GetPostedOrUploadedFile($key, TYPEF_DIR . '/files/public/content'));
				}
				elseif (isset($_POST[$key]))
				{
					$value = $_POST[$key];
				}
				else
				{
					$value = null;
				}
				$content[$key] = $value;
			}
			$pm->setVariable('row',   $content);
			$pm->setVariable('group', $group);
		}

		Typeframe::SetPageTemplate('/admin/content/groups/form-post.html');
	}

	$pm->setVariable('action',   $_SERVER['REQUEST_URI']);
	$pm->setVariable('group',    $group);
	$pm->setVariable('template', $_REQUEST['template']);
	$pm->setVariable('base',     $_REQUEST['base']);

	if (!empty($_REQUEST['pageid'])) {
		$pm->setVariable('group_url', TYPEF_WEB_DIR . '/admin/content/groups/form?pageid=' . $_REQUEST['pageid']);
	} else {
		$pm->setVariable('group_url', TYPEF_WEB_DIR . '/admin/content/groups/form?plugid=' . $_REQUEST['plugid']);
	}
}
else
{
	Typeframe::Redirect('Invalid base.', $typef_app_dir, -1);
	return;
}
