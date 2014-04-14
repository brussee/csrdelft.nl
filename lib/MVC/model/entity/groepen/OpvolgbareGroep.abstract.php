<?php

require_once 'MVC/model/entity/groepen/Groep.abstract.php';

/**
 * OpvolgbareGroep.abstract.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Een groep die opvolgbaar is en dus een begin, eind en status heeft.
 * 
 */
abstract class OpvolgbareGroep extends Groep {

	/**
	 * Familie van generaties
	 * @var string
	 */
	public $familie_id;
	/**
	 * Datum en tijd begin 
	 * @var string
	 */
	public $begin_moment;
	/**
	 * Datum en tijd einde
	 * @var string
	 */
	public $eind_moment;
	/**
	 * o.t. / h.t. / f.t.
	 * @see GroepStatus
	 * @var string
	 */
	public $status;
	/**
	 * Database table fields
	 * @var array
	 */
	protected static $persistent_fields = array(
		'familie_id' => 'varchar(255) NOT NULL',
		'begin_moment' => 'datetime DEFAULT NULL',
		'eind_moment' => 'datetime DEFAULT NULL',
		'status' => 'varchar(4) NOT NULL'
	);

	/**
	 * Extend the persistent fields.
	 */
	public static function __constructStatic() {
		parent::__constructStatic();
		self::$persistent_fields = parent::$persistent_fields + self::$persistent_fields;
	}

}
