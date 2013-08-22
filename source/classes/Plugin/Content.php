<?php
class Plugin_Content extends Plugin {
	public function admin(\Pagemill_Data $data, \Pagemill_Stream $stream) {
		$this->adminTemplate = '/admin/content/settings.plug.html';
		$data = $data->fork();
		$data->setArray($this->attributes());
		$templates = array();
		$dh = opendir(self::GetTemplateDirectory());
		while (($file = readdir($dh)) !== false) {
			if ( ($file != ".") && ($file != "..") ) {
				if (is_file(self::GetTemplateDirectory() . $file)) {
					if(preg_match('/.plug./', $file)){
						$templates[] = array('file' => $file);
					}
				}
			}
		}
		closedir($dh);
		$data['templates'] = $templates;
		$plugin = Model_Plug::Get($_REQUEST['plugid']);
		if ($plugin->exists()) {
			$data['plugin'] = $plugin;
			$data['settings'] = $plugin['settings'];
			parent::admin($data, $stream);
		}
	}

	/*public function update(array $input = null) {
		if(!isset($_POST['plugid'])){
			Typeframe::Redirect('No plug id provided', TYPEF_WEB_DIR . '/admin/skins/plugins');
		}

		$settings = $_POST['settings'];
		if(isset($_POST['settings']['template'])) $this->settings['template'] = $_POST['settings']['template'];
		$plugid = $this->settings['plugid'];

		$db = Typeframe::Database();

		//$settings = json_encode($_POST['settings'] ? $_POST['settings'] : array());
		$settings = json_encode($settings);

		$rs = $db->prepare('UPDATE #__plug SET settings = ?, name = ? WHERE plugid = ?');
		//var_dump($settings, $_POST); die();
		$rs->execute($settings, $_POST['name'], $plugid);
	}*/

	public function output(\Pagemill_Data $data, \Pagemill_Stream $stream) {
		$data = $data->fork();
		$data->setArray($this->attributes());
		$content = Model_Content_Plug::Get($this->attributes['plugid']);
		if (is_array($content['content'])) {
			$data->setArray($content['content']);
		}
		//$tag->process($data, $stream);
		$this->pluginTemplate = (!empty($this->attributes['template']) ? '/content/' . $this->attributes['template'] : '/content/generic.plug.html');
		parent::output($data, $stream);
	}

	/**
	 * Just a simple function to return the directory templates are contained in.
	 *
	 * @return string.
	 */
	public static function GetTemplateDirectory(){
		return TYPEF_SOURCE_DIR . '/templates/content/';
	}
}
