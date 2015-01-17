<?php

/**
 * OneTimeTokensModel.class.php
 * 
 * @author P.W.G. Brussee <brussee@live.nl>
 * 
 * Model voor two-step authentication.
 * 
 */
class OneTimeTokensModel extends PersistenceModel {

	const orm = 'OneTimeToken';

	protected static $instance;

	protected function __construct() {
		parent::__construct('security/');
	}

	public function verifyToken($uid, $tokenString) {
		$token = $this->find('uid = ? AND token = ?', array($uid, $tokenString), null, null, 1)->fetch();
		if (!$token OR $token->verified OR strtotime($token->expire) <= time()) {
			return false;
		}
		if (LoginModel::instance()->login($token->uid, null, true, $token->expire, true)) {
			$token->verified = true;
			$this->update($token);
			redirect($token->url);
		}
		return false;
	}

	/**
	 * Is current session verified by onetime token to execute a certain url on behalf of the user given uid?
	 * 
	 * @param string $uid
	 * @param string $url
	 * @return boolean
	 */
	public function isVerified($uid, $url) {
		$token = $this->retrieveByPrimaryKey(array($uid, $url));
		if ($token) {
			return $token->verified AND LoginModel::getUid() === $token->uid AND strtotime($token->expire) > time();
		}
		return false;
	}

	public function discardToken($uid, $url) {
		$this->deleteByPrimaryKey(array($uid, $url));
	}

	public function createToken($uid, $url) {
		$token = new OneTimeToken();
		$token->uid = $uid;
		$token->url = $url;
		$token->token = crypto_rand_token(200);
		$token->expire = getDateTime(strtotime(Instellingen::get('beveiliging', 'one_time_token_expire_after')));
		$token->verified = false;
		if ($this->exists($token)) {
			$this->update($token);
		} else {
			$this->create($token);
		}
		return $token;
	}

	public function opschonen() {
		foreach ($this->find('expire <= ?', array(getDateTime())) as $token) {
			$this->delete($token);
		}
	}

}
