<?php
/**
 * Base model for the content_plug table
 * This class was automatically generated from the database. Instead of
 * modifying it directly, extend it to add new functionality.
 */
class BaseModel_ContentPlug extends Dbi_Model {
	public function __construct() {
		parent::__construct();
		$this->name = 'content_plug';
		$this->prefix = DBI_PREFIX;
		$this->addField('plugid', new Dbi_Field('int', array('10', 'unsigned'), '', false));
		$this->addField('content', new Dbi_Field('longtext', array(), '', false));
		$this->addField('datecreated', new Dbi_Field('datetime', array(), '0000-00-00 00:00:00', false));
		$this->addField('datemodified', new Dbi_Field('datetime', array(), '0000-00-00 00:00:00', false));
		$this->addIndex('primary', array(
			'plugid'
		), 'unique');
	}
}
