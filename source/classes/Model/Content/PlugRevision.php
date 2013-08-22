<?php
class Model_Content_PlugRevision extends BaseModel_ContentPlugRevision {
	public function __construct() {
		parent::__construct();
		$this->order('datemodified DESC');
		$this->attach(Dbi_Model::EVENT_AFTERSELECT, new ModelEvent_JsonDecode('data'));
	}
}
