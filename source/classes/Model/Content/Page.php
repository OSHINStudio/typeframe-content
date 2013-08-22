<?php
class Model_Content_Page extends BaseModel_ContentPage {
	public function  __construct() {
		parent::__construct();
		$this->innerJoin('page', 'Model_Page', 'pageid = page.pageid');
		$this->subquery('revisions', 'Model_Content_PageRevision', 'content_page.pageid = content_page_revision.pageid');
		$this->attach(Dbi_Model::EVENT_BEFORECREATE, new ModelEvent_Timestamp('datecreated'));
		$this->attach(Dbi_Model::EVENT_BEFORESAVE, new ModelEvent_Timestamp('datemodified'));
		$this->attach(Dbi_Model::EVENT_BEFORESAVE, new ModelEvent_SaveRevision('Model_Content_PageRevision'));
		$this->attach(Dbi_Model::EVENT_AFTERSELECT, new ModelEvent_JsonDecode('content'));
	}
}
