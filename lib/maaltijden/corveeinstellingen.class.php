<?php
# C.S.R. Delft | pubcie@csrdelft.nl
# -------------------------------------------------------------------
# maaltijden/corveeinstellingen.class.php
# -------------------------------------------------------------------
# Deze klasse verwerkt instellingen voor corvee.
# -------------------------------------------------------------------

require_once 'formulier.class.php';

class Corveeinstellingen{
	private $error = '';
	private $instellingen;
	private $instellingForm = array();


	//laad instellingen in object Corveeinstellingen en maakt formulierobjecten
	public function __construct(){
		$db=MySql::instance();
		$query="
			SELECT instelling, type, tekst, datum, `int`
			FROM maaltijdcorveeinstellingen";
		$result=$db->query($query);
		if($db->numRows($result)>0){
			while($instelling=$db->next($result)){
				$this->array2instelling($instelling);
			}
		}else{
			$this->error .= mysql_error();	
		}

		//formulier objecten maken
		$this->assignInstellingenForm();
	}

	/*
	 * geeft waarde van $instelling terug
	 * @paramter string $instelling naam v. instelling
	 * @return (tekst,datum of int) waarde zoals opgeslagen in Corveeinstellingen 
	 */
	private function getValue($instelling){
		return $this->instellingen[$instelling][2];
	}
	/*
	 * zoekt waarde van instelling en geeft die
	 * @return (tekst,datum of int) waarde zoals opgeslagen in db 
	 */
	public static function get($instelling){
		$db=MySql::instance();
		$query="
			SELECT instelling, type, tekst, datum, `int`
			FROM maaltijdcorveeinstellingen
			WHERE instelling='".$db->escape($instelling)."'
			LIMIT 1;";
		$result=$db->query($query);
		if($db->numRows($result)>0){
			$ainstelling=$db->next($result);
			return $ainstelling[$ainstelling['type']];
		}else{
			throw new Exception('Instelling '.$instelling.' niet gevonden. Corveeinstelingen::get()'.mysql_error());
		}
	}
	/*
	 * slaat waarde op in dit object
	 * @parameters 	string $instelling naam instelling, 
	 * 				$value datum, int of text
	 * @return void 
	 */
	public function set($instelling, $value){
		$this->instellingen[$instelling][2]= $value;
	}
	/*
	 * slaat gegeven array op in dit object
	 * @parameters	array $instelling 
	 * @return void
	 */
	public function array2instelling($instelling){
		$this->instellingen[$instelling['instelling']]= array(
			$instelling['instelling'], 
			$instelling['type'], 
			$instelling[$instelling['type']]
		);
	}

	// @return error string
	public function getError(){
		return $this->error;
	}

	/*
	 * formulier objecten maken
	 * @return void
	 */
	public function assignInstellingenForm(){
		$instellingForm[] = new Comment('Corveebeheerpagina - weergegeven periode');
		$instellingForm[] = new DatumField('periodebegin',$this->getValue('periodebegin') , 'Begin periode',2020);
		$instellingForm[] = new DatumField('periodeeind',$this->getValue('periodeeind') , 'Einde periode',2020);

		$instellingForm[] = new Comment('Corveerooster - weergegeven periode');
		$instellingForm[] = new DatumField('roosterbegin',$this->getValue('roosterbegin') , 'Begin (Alleen MaalCie)',2020);
		$instellingForm[] = new DatumField('roostereind',$this->getValue('roostereind') , 'Einde periode',2020);

		$instellingForm[] = new Comment('Corveepunten');
		$instellingForm[] = new IntField('puntentotaal',$this->getValue('puntentotaal') , 'Totaal per jaar', 30, 0); 
		$instellingForm[] = new IntField('puntenkoken',$this->getValue('puntenkoken') , 'Koken', 30, 0); 
		$instellingForm[] = new IntField('puntenafwas',$this->getValue('puntenafwas') , 'Afwas', 30, 0); 
		$instellingForm[] = new IntField('puntentheedoek',$this->getValue('puntentheedoek') , 'Theedoeken', 30, 0); 
		$instellingForm[] = new IntField('puntenafzuigkap',$this->getValue('puntenafzuigkap') , 'Afzuigkapschoonmaken', 30, 0); 
		$instellingForm[] = new IntField('puntenfrituur',$this->getValue('puntenfrituur') , 'Frituurschoonmaken', 30, 0); 
		$instellingForm[] = new IntField('puntenkeuken',$this->getValue('puntenkeuken') , 'Keukenschoonmaken', 30, 0); 

		$instellingForm[] = new Comment('E-mails voor automailer');
		$instellingForm[] = new TextField('koks',$this->getValue('koks') , 'Kwali-/gewone koks 
		Toegestane variabelen: LIDNAAM, DATUM, MEEETEN');
		$instellingForm[] = new TextField('afwas',$this->getValue('afwas') , 'Afwassers');
		$instellingForm[] = new TextField('theedoeken',$this->getValue('theedoeken') , 'Theedoekwassers');
		$instellingForm[] = new TextField('afzuigkap',$this->getValue('afzuigkap') , 'Afzuigkapschoonmakers');
		$instellingForm[] = new TextField('frituur',$this->getValue('frituur') , 'Frituurschoonmakers');
		$instellingForm[] = new TextField('keuken',$this->getValue('keuken') , 'Keukenschoonmakers');

		$this->instellingForm=$instellingForm;
	}

	/*
	 * Geeft objecten van het formulier terug
	 * @return array met FormField objecten
	 */
	public function getFields(){ 
		return $this->instellingForm;
	}
	
	/*
	 * Controleren of de velden van formulier zijn gePOST
	 * @return bool succes/mislukt
	 */
	public function isPostedFields(){
		$posted=false;
		foreach($this->getFields() as $field){
			if($field instanceof FormField AND $field->isPosted()){
				$posted=true;
			}
		}
		return $posted;
	}
	/*
	 * Controleren of de velden van formulier correct zijn
	 * @return bool succes/mislukt
	 */
	public function validFields(){
		//alle veldjes langslopen, en kijken of ze valideren.
		$valid=true;
		foreach($this->getFields() as $field){
			//we checken alleen de formfields, niet de comments enzo.
			if($field instanceof FormField AND !$field->valid()){
				$valid=false;
				$this->error .= 'Een veld heeft geen geldige input';
			}
		}
		return $valid;
	}
	/*
	 * Slaat de velden van formulier op
	 * @return bool succes/mislukt
	 */
	public function saveFields(){
		//object vullen
		foreach($this->getFields() as $field){
			if($field instanceof FormField){
				$this->set($field->getName(), $field->getValue());
			}
		}

		if($this->save()){
			return true;
		}

		return false;
	}

	/*
	 * Slaat dit object op in db
	 * @return bool succes/mislukt
	 */
	private function save(){
		$db=MySql::instance();

		$values = array();
		foreach($this->instellingen as $key => $instelling){
			$data=array('tekst'=>"", 'datum'=>"0000-00-00", 'int'=>0);
			$data[$instelling[1]] = $db->escape($instelling[2]);

			$values[] = "('".$instelling[0]."', '".$instelling[1]."', '".$data['tekst']."', '".$data['datum']."', '".$data['int']."')";
		}
		$qSave="
			REPLACE INTO maaltijdcorveeinstellingen (
				instelling, type, tekst, datum, `int`
			)VALUES
				".implode(', ', $values).";";

		if($db->query($qSave)){
			return true;
		}
		$this->error.='Fout in query, mysql gaf terug: '.mysql_error().' Corveeinstellingen::save()';
		return false;
	}
}
?>
