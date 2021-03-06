<?php

/**
 * MaaltijdForm.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 *
 * Formulier voor een nieuwe of te bewerken maaltijd.
 * 
 */
class MaaltijdForm extends ModalForm {

	public function __construct($mid, $mrid = null, $titel = null, $limiet = null, $datum = null, $tijd = null, $prijs = null, $filter = null, $omschrijving = null) {
		parent::__construct(null, maalcieUrl . '/opslaan/' . $mid);

		if (!is_int($mid) || $mid < 0) {
			throw new Exception('invalid mid');
		}
		if ($mid === 0) {
			$this->titel = 'Maaltijd aanmaken';
		} else {
			$this->titel = 'Maaltijd wijzigen';
			$this->css_classes[] = 'PreventUnchanged';
		}

		$fields['mrid'] = new IntField('mlt_repetitie_id', $mrid, null);
		$fields['mrid']->readonly = true;
		$fields['mrid']->hidden = true;
		$fields[] = new TextField('titel', $titel, 'Titel', 255, 5);
		$fields[] = new DateField('datum', $datum, 'Datum', date('Y') + 2, date('Y') - 2);
		$fields[] = new TimeField('tijd', $tijd, 'Tijd', 15);
		$fields[] = new BedragField('prijs', $prijs, 'Prijs', '€', 0, 50, 0.50);
		$fields[] = new IntField('aanmeld_limiet', $limiet, 'Aanmeldlimiet', 0, 200);
		$fields[] = new RechtenField('aanmeld_filter', $filter, 'Aanmeldrestrictie');
		$fields[] = new BBCodeField('omschrijving', $omschrijving, 'Omschrijving');
		$fields[] = new FormDefaultKnoppen();

		$this->addFields($fields);
	}

}
