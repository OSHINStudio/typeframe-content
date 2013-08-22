<?php
/**
 * Base model for the content_page_revision table
 * This class was automatically generated from the database. Instead of
 * modifying it directly, extend it to add new functionality.
 */
class BaseModel_ContentPageRevision extends Dbi_Model {
	public function __construct() {
		parent::__construct();
		$this->name = 'content_page_revision';
		$this->prefix = DBI_PREFIX;
		$this->addField('revisionid', new Dbi_Field('int', array('11', 'unsigned', 'auto_increment'), '', false));
		$this->addField('pageid', new Dbi_Field('int', array('10', 'unsigned'), '0', false));
		$this->addField('data', new Dbi_Field('longtext', array(), '', false));
		$this->addField('datemodified', new Dbi_Field('datetime', array(), '0000-00-00 00:00:00', false));
		$this->addIndex('primary', array(
			'revisionid'
		), 'unique');
	}
}
