<?php

/**
 * Mails versturen vanuit csrdelft.nl.
 *
 * Tijd om dit eens een beetje fatsonelijk te centraliseren.
 *
 * Alle mailadressen in to of bcc zullen als de host niet syrinx is
 * worden aangepast naar pubcie@csrdelft.nl
 */
class Mail{
	protected $onderwerp;
	protected $bericht;
	
	protected $from='pubcie@csrdelft.nl';
	protected $to=array();
	protected $bcc=array();

	protected $charset='utf8';
	
	public function __construct($to, $onderwerp, $bericht){
		$this->onderwerp=$onderwerp;
		$this->bericht=$bericht;

		$this->addTo($to);
	}

	public function getTo(){
		return implode(',', $this->to);
	}

	public function addTo($to){
		if(strpos($to, ',') !== false){
			foreach(explode(',', $to) as $email){
				$this->addTo($email);
			}
		}else{
			if(!email_like($to)){
				throw new Exception('Emailadres in $to geen valide email-adres');
			}
			$this->to[]=$this->production_safe($to);
		}
	}
	
	public function setFrom($from){
		if(!email_like($from)){
			throw new Exception('Emailadres in $from geen valide email-adres');
		}
		$this->from=$from;
	}

	public function getBcc(){
		return implode(',', $this->bcc);
	}
	
	public function addBcc($bcc){
		if(strpos($bcc, ',') !== false){
			foreach(explode(',', $bcc) as $email){
				$this->addBcc($email);
			}
		}else{
			if(!email_like($bcc)){
				throw new Exception('Emailadres in $bcc geen valide email-adres');
			}
			$this->bcc[]=$this->production_safe($bcc);
		}
	}

	/**
	 * Mails uit testomgevingen moet en niet naar andere dingen dan naar
	 * het pubcie-mailadres.
	 */
	protected function production_safe($email){
		if(!isSyrinx()){
			return 'pubcie@csrdelft.nl';
		}else{
			return $email;
		}
	}
	public function getHeaders(){
		$headers="From: ".$this->from."\n";
		if($this->bcc!=''){
			$headers.="BCC: ".$this->getBcc()."\n";
		}

		if($this->charset='utf8'){
			$headers.="Content-Type: text/plain; charset=UTF-8\r\n";
		}
		$headers.='X-Mailer: nl.csrdelft.lib.Mail'."\n\r";

		return $headers;
	}
	public function getSubject(){
		$onderwerp=$this->onderwerp;
		if(!isSyrinx()){
			$onderwerp.=' [Mail: Debug modus actief]';
		}
		if($this->charset='utf8'){
			//zorg dat het onderwerp netjes utf8 in base64 is. Als je dit niet doet krijgt het
			//spampunten van spamassasin (SUBJECT_NEEDS_ENCODING,SUBJ_ILLEGAL_CHARS)
			$onderwerp=' =?UTF-8?B?'. base64_encode($onderwerp) ."?=\n";
		}
		return $onderwerp;
	}
	public function getBody(){
		return $this->bericht;
	}
	
	public function send(){
		if($this->onderwerp==''){
			throw new Exception('Geen onderwerp ingevuld');
		}
		
		return mail($this->getTo(), $this->getSubject(), $this->getBody(), $this->getHeaders());
	}

	public function __toString(){
		return $this->getHeaders()."\nSubject:".$this->getSubject()."\n".$this->bericht;
	}
	
	/**
	 * Shorthand...
	 *
	 * bijvoorbeeld:
	 * Mail::mail('pubcie@csrdelft.nl', 'Test123', "Hoi Pubcie,\nDit is een test.");
	 */
	public static function mail($to, $onderwerp, $bericht){
		$mail=new Mail($to, $subject, $bericht);
		return $mail->send();
	}
}

/**
 * Extensie van mail, met een eenvoudige search-and-replace
 * templatejetzer.
 *
 * template bijvoorbeeld: "hallo %naam%, groet, %afzender%"
 * values bijvoorbeeld: array('naam' => 'Jieter', 'afzender' => 'PubCie');
 * resultaat in body: "hallo Jieter, groet, PubCie"
 *
 * Controleert niet of alle placeholders ook gegeven worden in de
 * values-array!
 */
class TemplatedMail extends Mail{

	protected $template;
	protected $values=null;
	
	public function __construct($to, $onderwerp, $template){
		parent::__construct($to, $onderwerp, '');
		$this->template=$template;
	}
	public function setValues($values){
		$this->values=$values;
	}
	public function getBody(){
		$body=$this->template;

		if(is_array($this->values)){
			foreach($this->values as $key => $value){
				$body=str_replace('%'.$key.'%', $value, $body);
			}
		}
		return $body;
	}
}

?>
