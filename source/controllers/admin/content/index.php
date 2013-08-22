<?php
/**
 * Typeframe Content application
 *
 * admin-side index controller
 */

if (!empty($_REQUEST['pageid']))
{
	Typeframe::Redirect('Redirecting to page content...', Typeframe::CurrentPage()->applicationUri() . '/page?pageid=' . $_REQUEST['pageid']);
	return;
}

// add pages
$pages = new Model_Page();
$pages->where('application = ?', 'Content');
$pages->where('siteid = ?', Typeframe::CurrentPage()->siteid());
$pages->order('nickname, uri');
foreach ($pages->getAll() as $page)
{
	$settings = $page['settings'];
	$template = TYPEF_SOURCE_DIR . '/templates/content/' . (!empty($settings['template']) ? $settings['template'] : 'generic.html');
	if (!file_exists($template))
	{
		$page->set('dead_template', $settings['template']);
		$template = (TYPEF_SOURCE_DIR . '/templates/content/generic.html');
	}
	$elements = Insertable::ElementsFrom($template);
	$groups   = Insertable::GroupsFrom($template);
	if ((count($elements) > 0) || (count($groups) > 0))
	{
		$garray = array();
		foreach ($groups as $group)
		{
			if (empty($group['admin']) || (Typeframe::User()->get('usergroupid') == TYPEF_ADMIN_USERGROUPID) || inGroup($group['admin']))
				$garray[] = $group;
		}
		$page->set('groups', $garray);
		$pm->addLoop('pages', $page->getAsArray());
	}
}

// add plugins
$plugs = new Model_Plug();
$plugs->where('plug = ?', 'Content');
$plugs->order('name');
$pm->setVariable('plugins', $plugs);
