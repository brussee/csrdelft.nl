<?php

/**
 * Kring.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class Kring extends AbstractGroep {

	const leden = 'KringLedenModel';

	/**
	 * Verticaleletter
	 * @var string
	 */
	public $verticale;
	/**
	 * Kringnummer
	 * @var int
	 */
	public $kring_nummer;
	/**
	 * Database table columns
	 * @var array
	 */
	protected static $persistent_attributes = array(
		'verticale'		 => array(T::Char),
		'kring_nummer'	 => array(T::Integer)
	);
	/**
	 * Database table name
	 * @var string
	 */
	protected static $table_name = 'kringen';

	public function getUrl() {
		return '/groepen/kringen/' . $this->verticale . '.' . $this->kring_nummer . '/';
	}

}
