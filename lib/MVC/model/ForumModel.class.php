<?php

/**
 * ForumModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 */
class ForumModel extends PersistenceModel {

	const orm = 'ForumCategorie';

	protected static $instance;

	/**
	 * Eager loading of ForumDeel[].
	 * 
	 * @return ForumCategorie[]
	 */
	public function getForum() {
		$delen = ForumDelenModel::instance()->getAlleForumDelenPerCategorie();
		$categorien = $this->find(null, array(), 'volgorde');
		foreach ($categorien as $i => $cat) {
			if (!$cat->magLezen()) {
				unset($categorien[$i]);
			} else {
				if (array_key_exists($cat->categorie_id, $delen)) {
					$cat->setForumDelen($delen[$cat->categorie_id]);
					unset($delen[$cat->categorie_id]);
				} else {
					$cat->setForumDelen(array());
				}
			}
		}
		return $categorien;
	}

	public function getCategorie($id) {
		return $this->retrieveByPrimaryKey(array($id));
	}

}

class ForumDelenModel extends PersistenceModel {

	const orm = 'ForumDeel';

	protected static $instance;

	public function getAlleForumDelenPerCategorie() {
		$delen = $this->find(null, array(), 'volgorde');
		foreach ($delen as $i => $deel) {
			if (!$deel->magLezen()) {
				unset($delen[$i]);
			}
		}
		return array_group_by('categorie_id', $delen);
	}

	public function getForumDelenVoorCategorie($cid) {
		return $this->find('categorie_id = ?', array($cid), 'volgorde');
	}

	public function bestaatForumDeel($id) {
		return $this->existsByPrimaryKey(array($id));
	}

	public function getForumDeel($id) {
		return $this->retrieveByPrimaryKey(array($id));
	}

}

class ForumDradenGelezenModel extends PersistenceModel {

	const orm = 'ForumDraadGelezen';

	protected static $instance;

	public function getWanneerGelezenDoorLid(ForumDraad $draad) {
		$gelezen = $this->retrieveByPrimaryKey(array($draad->draad_id, LoginLid::instance()->getUid()));
		if (!$gelezen) {
			return '0000-00-00 00:00:00';
		}
		return $gelezen->datum_tijd;
	}

	public function setWanneerGelezenDoorLid(ForumDraad $draad) {
		$gelezen = $this->retrieveByPrimaryKey(array($draad->draad_id, LoginLid::instance()->getUid()));
		if (!$gelezen) {
			$gelezen = new ForumDraadGelezen();
			$gelezen->draad_id = $draad->draad_id;
			$gelezen->lid_id = LoginLid::instance()->getUid();
			$gelezen->datum_tijd = date('Y-m-d H:i:s');
			$this->create($gelezen);
		} else {
			$gelezen->datum_tijd = date('Y-m-d H:i:s');
			$this->update($gelezen);
		}
	}

}

class ForumDradenModel extends PersistenceModel implements Paging {

	const orm = 'ForumDraad';

	protected static $instance;
	/**
	 * Huidige pagina
	 * @var int
	 */
	private $pagina;
	/**
	 * Aantal draden per pagina
	 * @var int
	 */
	private $per_pagina;

	protected function __construct() {
		parent::__construct();
		$this->pagina = 1;
		$this->per_pagina = LidInstellingen::get('forum', 'draden_per_pagina');
	}

	public function getHuidigePagina() {
		return $this->pagina;
	}

	public function setHuidigePagina($number) {
		if ($number > 0) {
			$this->pagina = (int) $number;
		}
	}

	public function getAantalPerPagina() {
		return $this->per_pagina;
	}

	public function getAantalPaginas($forum_id) {
		return ceil($this->count('forum_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($forum_id)) / $this->per_pagina);
	}

	/**
	 * Eager loading of ForumDraadGelezen[].
	 * 
	 * @param int $forum_id
	 * @return ForumDraad[]
	 */
	public function getForumDradenVoorDeel($forum_id) {
		$orm = self::orm;
		$from = $orm::getTableName() . ' AS d LEFT JOIN forum_draden_gelezen AS g ON d.draad_id = g.draad_id AND g.lid_id = ? ';
		$columns = $orm::getFields();
		foreach ($columns as $i => $column) {
			$columns[$i] = 'd.' . $column; // prefix
		}
		$columns[] = 'g.datum_tijd AS wanneer_gelezen';
		$result = Database::sqlSelect($columns, $from, 'd.forum_id = ? AND d.wacht_goedkeuring = FALSE AND d.verwijderd = FALSE', array(LoginLid::instance()->getUid(), $forum_id), 'd.plakkerig DESC, d.laatst_gewijzigd DESC', $this->per_pagina, ($this->pagina - 1) * $this->per_pagina);
		$draden = $result->fetchAll(PDO::FETCH_CLASS, self::orm);
		return $draden;
	}

	public function getForumDraad($id) {
		$draad = $this->retrieveByPrimaryKey(array($id));
		if (!$draad) {
			throw new Exception('Forumdraad bestaat niet!');
		}
		return $draad;
	}

	public function maakForumDraad($forum_id, $titel) {
		$draad = new ForumDraad();
		$draad->forum_id = (int) $forum_id;
		$draad->lid_id = LoginLid::instance()->getUid();
		$draad->titel = $titel;
		$draad->datum_tijd = datum('Y-m-d H:i:s');
		$draad->laatst_gewijzigd = null;
		$draad->laatste_post_id = null;
		$draad->laatste_lid_id = null;
		$draad->aantal_posts = 0;
		$draad->gesloten = false;
		$draad->verwijderd = false;
		$draad->wacht_goedkeuring = LoginLid::instance()->hasPermission('P_LOGGED_IN');
		$draad->plakkerig = false;
		$draad->belangrijk = false;
		$draad->draad_id = (int) ForumDradenModel::instance()->create($draad);
		return $draad;
	}

	public function wijzigForumDraad(ForumDraad $draad, $property, $value) {
		if (!property_exists($draad, $property)) {
			throw new Exception('Property undefined: ' . $property);
		}
		if ($property === 'forum_id' AND !ForumDelenModel::instance()->bestaatForumDeel($value)) {
			throw new Exception('Forum bestaat niet!');
		}
		$draad->$property = $value;
		return $this->update($draad);
	}

}

class ForumPostsModel extends PersistenceModel implements Paging {

	const orm = 'ForumPost';

	protected static $instance;
	/**
	 * Huidige pagina
	 * @var int
	 */
	private $pagina;
	/**
	 * Aantal posts per pagina
	 * @var int
	 */
	private $per_pagina;

	protected function __construct() {
		parent::__construct();
		$this->pagina = 1;
		$this->per_pagina = LidInstellingen::get('forum', 'posts_per_pagina');
	}

	public function getHuidigePagina() {
		return $this->pagina;
	}

	public function setHuidigePagina($number) {
		if ($number > 0) {
			$this->pagina = (int) $number;
		}
	}

	public function getAantalPerPagina() {
		return $this->per_pagina;
	}

	public function getAantalPaginas($draad_id) {
		return ceil($this->count('draad_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($draad_id)) / $this->per_pagina);
	}

	public function getForumPostsVoorDraad(ForumDraad $draad) {
		$posts = $this->find('draad_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($draad->draad_id), null, $this->per_pagina, ($this->pagina - 1) * $this->per_pagina);
		// 2008 filter
		if (LidInstellingen::get('forum', 'filter2008') == 'ja') {
			foreach ($posts as $post) {
				if (startsWith($post->lid_id, '08')) {
					$post->gefilterd = 'Bericht van 2008';
				}
			}
		}
		return $posts;
	}

	public function getPaginaVoorPost(ForumPost $post) {
		$count = $this->count('draad_id = ? AND post_id < ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($post->draad_id, $post->post_id));
		return ceil($count / $this->per_pagina);
	}

	public function getAantalForumPostsVoorLid($uid) {
		return $this->count('lid_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($uid));
	}

	/**
	 * Laad de meest recente forumposts van een gebruiker.
	 * Zet de titel van het draadje als tekst van de post voor weergave.
	 * 
	 * @param string $uid
	 * @param int $aantal
	 * @return ForumPost[]
	 */
	public function getRecenteForumPostsVoorLid($uid, $aantal) {
		$posts = $this->find('lid_id = ? AND wacht_goedkeuring = FALSE AND verwijderd = FALSE', array($uid), 'post_id DESC', $aantal);
		$draden_ids = array_keys(array_key_property('draad_id', $posts, false));
		$in = implode(', ', array_fill(0, count($draden_ids), '?'));
		$draden = array_key_property('draad_id', ForumDradenModel::instance()->find('draad_id IN (' . $in . ')', $draden_ids));
		foreach ($posts as $post) {
			if (array_key_exists($post->draad_id, $draden)) {
				$post->tekst = $draden[$post->draad_id]->titel;
			}
		}
		return $posts;
	}

	public function getForumPost($id) {
		$post = $this->retrieveByPrimaryKey(array($id));
		if (!$post) {
			throw new Exception('Forumpost bestaat niet!');
		}
		return $post;
	}

	public function maakForumPost($draad_id, $tekst, $ip) {
		$post = new ForumPost();
		$post->draad_id = (int) $draad_id;
		$post->lid_id = LoginLid::instance()->getUid();
		$post->tekst = $tekst;
		$post->datum_tijd = date('Y-m-d H:i:s');
		$post->laatst_bewerkt = null;
		$post->bewerkt_tekst = null;
		$post->verwijderd = false;
		$post->auteur_ip = $ip;
		$post->wacht_goedkeuring = false;
		$post->post_id = (int) ForumPostsModel::instance()->create($post);
		return $post;
	}

	public function verwijderForumPost(ForumPost $post) {
		if ($post->verwijderd) {
			throw new Exception('Al verwijderd!');
		}
		$post->verwijderd = true;
		return $this->update($post);
	}

	public function bewerkForumPost(ForumPost $post, $nieuwe_tekst, $reden = '') {
		$post->tekst = $nieuwe_tekst;
		$post->laatst_bewerkt = getDateTime();
		$bewerkt = 'bewerkt door [lid=' . LoginLid::instance()->getUid() . '] [reldate]' . $post->laatst_bewerkt . '[/reldate]';
		if ($reden !== '') {
			$bewerkt .= ': ' . $reden;
		}
		$bewerkt .= "\n";
		$post->bewerkt_tekst .= $bewerkt;
		return $this->update($post);
	}

	public function offtopicForumPost(ForumPost $post) {
		$post->tekst = '[offtopic]' . $post->tekst . '[/offtopic]';
		$post->laatst_bewerkt = getDateTime();
		$post->bewerkt_tekst = 'offtopic door [lid=' . LoginLid::instance()->getUid() . '] [reldate]' . $post->laatst_bewerkt . '[/reldate]' . "\n";
		return $this->update($post);
	}

	public function goedkeurenForumPost(ForumPost $post) {
		if (!$post->wacht_goedkeuring) {
			throw new Exception('Al goedgekeurd!');
		}
		$post->wacht_goedkeuring = false;
		$post->laatst_bewerkt = getDateTime();
		$post->bewerkt_tekst .= '[prive=P_FORUM_MOD]Goedgekeurd door [lid=' . LoginLid::instance()->getUid() . '] [reldate]' . $post->laatst_bewerkt . '[/reldate][/prive]' . "\n";
		return $this->update($post);
	}

	public function citeerForumPost(ForumPost $post) {
		$tekst = CsrUbb::filterPrive($post->tekst);
		return '[citaat=' . $post->lid_id . ']' . $tekst . '[/citaat]';
	}

}
