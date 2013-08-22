<?php
if (!empty($_REQUEST['application'])) {
	if ($_REQUEST['application'] == 'Content') {
		$template = null;
		if (CONTENT_DEFAULT_TEMPLATE) {
			$template = CONTENT_DEFAULT_TEMPLATE;
		}
		if (!empty($_REQUEST['pageid'])) {
			//$rs = $db->prepare('SELECT settings FROM #__page WHERE pageid = ?');
			$row = Model_Page::Get($_REQUEST['pageid']);
			if ($row->exists()) {
				$pm->setVariable('settings', $row['settings']);
				$template = @$settings['template'];
			}
		}
		if ($template) {
			$pm->setVariable('template', $template);
		}
		$dh = opendir(TYPEF_SOURCE_DIR . '/templates/content');
		while (($file = readdir($dh)) !== false) {
			if ( ($file != ".") && ($file != "..") && !preg_match('/.plug.html/', $file)) {
				if (is_file(TYPEF_SOURCE_DIR . '/templates/content/' . $file)) {
					$source = file_get_contents(TYPEF_SOURCE_DIR . '/templates/content/' . $file);
					preg_match('/pm:tmplname="([\w\W\s\S]*?)"/', $source, $matches);
					if ($matches) {
						$label = $matches[1];
					} else {
						$label = $file;
					}
					$pm->addLoop('insertabletemplates', array('template' => $file, 'label' => $label));
				}
			}
		}
		$pm->sortLoop('insertabletemplates', 'template');
		closedir($dh);
		$pm->setVariable('settingstemplate', '/admin/content/settings.html');
	}
}
