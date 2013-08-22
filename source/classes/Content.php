<?php
class Content
{
	public static function RecurseGroups($group, $data)
	{
		$content = array();
		foreach ($data as $datum)
		{
			$row = array();
			foreach ($group['members'] as $member)
			{
				$name = $member['name'];
				$row[$name] = (isset($datum[$name]) ? $datum[$name] : null);
			}
			foreach ($group['subgroups'] as $subgroup)
			{
				$name = $subgroup['name'];
				$row[$name] = (isset($datum[$name]) ? self::RecurseGroups($subgroup, $datum[$name]) : null);
			}
			$content[] = $row;
		}
		return $content;
	}

	public static function ProcessPost(&$inserts, &$groups)
	{
		$content = array();
		foreach ($inserts as $insert) {
			$key = $insert['name'];
			if ('image' == $insert['type']) {
				$value = basename(FileManager::GetPostedOrUploadedFile($key, TYPEF_DIR . '/files/public/content'));
			} elseif (isset($_POST[$key])) {
				$value = $_POST[$key];
			} else {
				$value = null;
			}
			$content[$key] = $value;
		}
		foreach ($groups as $group) {
			$key = $group['name'];
			$content[$key] = (isset($_POST[$key]) ?
								Content::RecurseGroups($group, $_POST[$key]) :
								null);
		}
		return $content;
	}

	public static function LoadData($dao, &$content, &$inserts, &$groups)
	{
		// add data to inserts
		foreach ($inserts as $index => $insert)
		{
			$key = $insert['name'];
			$insert['value'] = (isset($content[$key]) ? $content[$key] : null);
			$inserts[$index] = $insert;
		}

		// add data to groups
		if (!empty($groups))
		{
			foreach ($groups as $index => $group)
			{
				$key = $group['name'];
				$group['content'] = array();
				if (isset($content[$key]) && is_array($content[$key]))
				{
					foreach ($content[$key] as $value) {
						$group['content'][] = $value;
					}
				}
				$groups[$index] = $group;
			}
		}

		// return result
		return array($dao, $inserts, $groups);
	}

}
