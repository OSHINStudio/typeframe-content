<?php
class Insertable {
	//public static function ElementsFrom($template, $skin = 'default') {
	public static function ElementsFrom($template, $uri = null) {
		/*if (!is_null($uri)) {
			$skin = Typeframe_Skin::At($uri);
			$stylesheets = Typeframe_Tag_Stylesheets::GetStylesheetsAt($uri);
		} else {
			$skin = 'default';
			$stylesheets = Typeframe_Tag_Stylesheets::GetStylesheetsAt(TYPEF_WEB_DIR . '/');
		}*/
		$skin = Typeframe_Skin::At($uri);
		$array = null;
		if ( (file_exists($template)) && (is_file($template)) ) {
			$source = file_get_contents($template);
			/*foreach (Pagemill::GetExtendedEntityTable() as $character => $entity) {
				if ( ($entity != '&amp;') && ($entity != '&quot;') && ($entity != '&gt;') && ($entity != '&lt;') ) {
					$source = str_replace($entity, ' ', $source);
				}
			}*/
			$xml = Pagemill_SimpleXmlElement::LoadString($source);
			$array = array();
			$elements = $xml->xpath('//pm:insert|//pm:include');
			foreach ($elements as $e) {
				if ($e->getName() == 'insert') {
					$name = Insertable::_NameToSymbol((string)$e['name']);
					if ($name !== (string)$e['name']) {
						trigger_error("Insertable element name '{$e['name']}' has invalid characters (should be letters, numbers, and underscores only) in template '{$template}'");
						return null;
					}
					$label = (string)$e['label'];
					if (!$label) $label = $name;
					$type = (string)$e['type'];
					$admin = (string)$e['admin'];
					$selector = '';
					if ($type == 'html') {
						if (count($e->children())) {
							foreach ($e->children() as $c) {
								if (preg_match('/@{[\w\W\s\S]*?' . $name . '[\w\W\s\S]*?}@/', $c->asXml())) {
									$selector = Insertable::_InsertSelector($c, $skin);
									break;
								}
							}
						} else {
							$stack = $e->xpath('parent::*');
							if (count($stack)) {
								$element = array_pop($stack);
								$selector = Insertable::_InsertSelector($element, $skin);
							}
						}
					} else if ($type == 'select') {
						$options = array();
						foreach ($e->option as $option) {
							$options[] = array(
								'label' => "{$option}",
								'value' => (!is_null($option['value']) ? "{$option['value']}" : "{$option}")
							);
						}
					} else if ($type == 'model'){
						$options = array();
						if(class_exists($e['model'])){
							$model = (string)$e['model'];
							$values = new $model();
							foreach($values->getAll() as $value){
								$options[] = array('label' => $value->get((string)$e['modellabel']), 'value' => $model . ':' . $value->get('id'));
							}
						}
						$type = 'select';
					}
					$i = array(
						'name' => $name,
						'label' => $label,
						'type' => $type,
						'admin' => $admin,
						'selector' => $selector
					);
					if ($type == 'select') {
						$i['options'] = $options;
					}
					$array[] = $i;
				} else {
					$included = Insertable::ElementsFrom(TYPEF_DIR . '/source/templates/' . $e['template']);
					if (is_array($included)) {
						$array = array_merge($array, $included);						
					}
				}
			}
		} else {
			trigger_error("File '{$template}' does not exist");
		}
		return $array;
	}
	private static function RecurseGroups($e, $xml, $skin) {
		$group = array();
		$name = Insertable::_NameToSymbol((string)$e['name']);
		if ($name !== (string)$e['name']) {
				trigger_error("Group name '{$e['name']}' has invalid characters (should be letters, numbers, and underscores only) in template '{$template}'");
				return null;
		}
		$group = array();
		$group['name'] = $name;
		$group['label'] = (string)$e['label'];
		$group['subpage'] = (string)$e['subpage'];
		$group['members'] = array();
		$members = $e->xpath('pm:member');
		foreach ($members as $m) {
			$name = Insertable::_NameToSymbol((string)$m['name']);
			if ($name !== (string)$m['name']) {
					trigger_error("Group name '{$m['name']}' has invalid characters (should be letters, numbers, and underscores only) in template '{$template}'");
					return null;
			}
			$label = (string)$m['label'];
			$type = (string)$m['type'];
			$cssid = '';
			$cssclass = '';
			$selector = '';
			if ($type == 'html') {
				$thePath = '//pm:loop[@name=\'' . $group['name'] . '\']|//*[@pm:loop=\'' . $group['name'] . '\']';
				$loops = $xml->xpath($thePath);
				foreach ($loops as $l) {
					if (count($l->children())) {
						$children = $l->xpath('//*[contains(text(), \'@{' . $name . '}@\')]');
						foreach ($children as $c) {
							if (preg_match('/@{[\w\W\s\S]*?' . $name . '[\w\W\s\S]*?}@/', $c->asXml())) {
								$selector = Insertable::_InsertSelector($c, $skin);
								break;
							}
						}
					}
					if ($selector) {
						break;
					}
				}
			} else if ($type == 'select') {
				$options = array();
				foreach ($m->option as $option) {
					$options[] = array(
						'label' => "{$option}",
						'value' => (!is_null($option['value']) ? "{$option['value']}" : "{$option}")
					);
				}
			} else if ($type == 'model'){
				$options = array();
				if(class_exists($m['model'])){
					$model = (string)$m['model'];
					$values = new $model();
					foreach($values->getAll() as $value){
						$options[] = array('label' => $value->get((string)$m['modellabel']), 'value' => $model . ':' . $value->get('id'));
					}
				}
				$type = 'select';
			}
			$member = array(
				'name' => $name,
				'label' => $label,
				'type' => $type,
				'cssid' => $cssid,
				'cssclass' => $cssclass,
				'selector' => $selector
			);
			if ($type == 'select') {
				$member['options'] = $options;
			}
			$group['members'][] = $member;
		}
		$group['admin'] = (string)$e['admin'];
		$group['subgroups'] = array();
		$subgroups = $e->xpath('pm:group');
		foreach ($subgroups as $subgroup) {
			$group['subgroups'][] = self::RecurseGroups($subgroup, $xml, $skin);
		}
		return $group;
	}
    public static function GroupsFrom($template, $groupMap = null, $skin = 'default') {
		$groups = array();
		if ( (file_exists($template)) && (is_file($template)) ) {
			$source = file_get_contents($template);
			foreach (get_html_translation_table() as $character => $entity) {
				if ( ($entity != '&amp;') && ($entity != '&quot;') && ($entity != '&gt;') && ($entity != '&lt;') ) {
					$source = str_replace($entity, ' ', $source);
				}
			}
			$xml = Pagemill_SimpleXmlElement::LoadString($source);
			if ($groupMap) {
				if (is_array($groupMap)) {
					$xpath = '//*[name() != \'pm:group\']';
					foreach ($groupMap as $g) {
						$xpath .= '/pm:group[@name=\'' . $g . '\']';
					}
					$elements = $xml->xpath($xpath);
				} else {
					$elements = $xml->xpath('//*[name() != \'pm:group\']/pm:group[name=\'' . $groupMap . '\']');
				}
			} else {
				$elements = $xml->xpath('//*[name() != \'pm:group\']/pm:group | //*[name() != \'pm:group\']/pm:include');
			}
			foreach ($elements as $e) {
				if ($e->getName() == 'group') {
					$groups[] = self::RecurseGroups($e, $xml, $skin);
				} else {
					$included = Insertable::GroupsFrom(TYPEF_DIR . '/source/templates/' . $e['template'], $groupMap, $skin);
					if (is_array($included)) {
						$groups = array_merge($groups, $included);						
					}
				}
			}
		} else {
			trigger_error("File '{$template}' does not exist");
		}
		return $groups;
    }
	private static function _NameToSymbol($name) {
		$symbol = preg_replace('/[^a-z0-9]/', '_', $name);
		return $symbol;
	}
	private static function _InsertSelector($element, $skin) {
		static $ignore = array(
			'if',
			'loop',
			'insert',
			'codeblock'
		);
		$selector = '';
		$current = $element;
		while ($current) {
			if ($current->getName() == 'body') break;
			if (!in_array($current->getName(), $ignore)) {
				$class = trim("{$current['class']}");
				$id = trim("{$current['id']}");
				$part = $current->getName();
				if ($id) $part .= '#' . $id;
				if ($class) {
					$class = preg_replace('/ +/', '.', $class);
					$part .= '.' . $class;
				}
				$selector = $part . ' ' . $selector;
			}
			$stack = $current->xpath('parent::*');
			if (count($stack)) {
				$current = array_pop($stack);
			} else {
				$current = null;
			}
		}
		$skinXml = Pagemill_SimpleXmlElement::LoadFile(TYPEF_DIR . '/skins/' . $skin . '/skin.html');
		$xpath = $skinXml->xpath('//pm:import[@name=\'body\']');
		if (count($xpath)) {
			$current = $xpath[0];
			$stack = $current->xpath('parent::*');
			while (count($stack)) {
				$current = array_pop($stack);
				if ($current->getName() == 'body' || $current->getName == 'html') {
					break;
				}
				if (!in_array($current->getName(), $ignore)) {
					$id = '';
					$class = '';
					if ($current['id']) {
						$id = '#' . $current['id'];
					}
					if ($current['class']) {
						$class = '.' . preg_replace('/ +/', '.', $class);
					}
					//if ($id || $class) {
						$selector = $current->getName() . $id . $class . ' ' . $selector;
					//}
				}
				$stack = $current->xpath('parent::*');
			}
		}
		// Selectors should never contain Pagemill expressions
		if (strpos($selector, '@{') !== false) {
			$selector = '';
		}
		return trim($selector);
	}
}
