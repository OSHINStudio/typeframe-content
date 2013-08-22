<?php
/**
 * Typeframe Content application
 *
 * client-side index controller
 */

// save typing below
$pathinfo      = Typeframe::CurrentPage()->pathInfo();
$settings      = Typeframe::CurrentPage()->settings();
$typef_app_dir = Typeframe::CurrentPage()->uri();

// set template
if (empty($settings['template'])) $settings['template'] = 'generic.html';
Typeframe::SetPageTemplate("/content/{$settings['template']}");

// if given path information
if ($pathinfo)
{
	$pm->setVariable('pathinfo', $pathinfo);
	// find out which skin to use; derive appropriate template from that
	$skin          = Typeframe_Skin::At($typef_app_dir);
	$skin_template = (TYPEF_DIR . "/skins/$skin/templates/content/" . $settings['template']);
	$base_template = (TYPEF_DIR . "/source/templates/content/"      . $settings['template']);
	$source        = file_get_contents(file_exists($skin_template) ? $skin_template : $base_template);

	// look for groups and subpages
	if (is_int(strpos($source, 'pm:group')) && is_int(strpos($source, 'subpage')))
	{
		// convert all entities in source into spaces, except for &, ", >, and <
		//$invalidEntities = array_diff(array_values(Pagemill::GetExtendedEntityTable()),
		//									array('&amp;', '&quot;', '&gt;', '&lt;'));
		//$source = str_replace($invalidEntities, ' ', $source);

		// get page id
		$pageid = Typeframe::CurrentPage()->pageid();

		// load groups from source as XML; loop over them
		$xml    = Pagemill_SimpleXmlElement::LoadString($source);
		$groups = $xml->xpath('//pm:group');
		foreach ($groups as $group)
		{
			// skip group if no subpage or name
			if (!isset($group['subpage']) || !isset($group['name']))
				continue;

			// make sure the subpage indicated in pathinfo exists
			$content = Model_Content_Page::Get($pageid);
			if ($content->exists())
			{
				$content = $content->get('content');

				// define (and cast) group subpage and name
				$groupsubpage = (string)$group['subpage'];
				$groupname    = (string)$group['name'];

				// make sure it has this group name
				if (isset($content[$groupname]) && is_array($content[$groupname]))
				{
					// search all subpages for pathinfo
					foreach ($content[$groupname] as &$subpage)
					{
						// found; done (InsertGroupTag gets data when template is parsed)
						if (isset($subpage[$groupsubpage]) &&
							makeFriendlyUrlText($subpage[$groupsubpage]) == $pathinfo)
							return;
					}
				}
			}
		}
	}

	// report 404 (page not found)
	//header('HTTP/1.0 404 Not Found');
	http_response_code(404);
	Typeframe::SetPageTemplate('/404.html');
	//Typeframe::CurrentPage()->stop();
}
