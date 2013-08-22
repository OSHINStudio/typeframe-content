<?php
class Model_Content_Plug extends BaseModel_ContentPlug {
	public function  __construct() {
		parent::__construct();
		$this->attach(Dbi_Model::EVENT_BEFORESAVE, new ModelEvent_SaveRevision('Model_Content_PlugRevision'));
		$this->attach(Dbi_Model::EVENT_BEFORECREATE, new ModelEvent_Timestamp('datecreated'));
		$this->attach(Dbi_Model::EVENT_BEFORESAVE, new ModelEvent_Timestamp('datemodified'));
		$this->attach(Dbi_Model::EVENT_AFTERSELECT, new ModelEvent_JsonDecode('content'));
	}
}
