<?php

ini_set(
  'include_path',
  dirname(__FILE__) . '/2Performant' . PATH_SEPARATOR . dirname(__FILE__) . '/2Performant/PEAR' . PATH_SEPARATOR . ini_get( 'include_path' )
);

require_once 'TPerformant.php';

class CTPerformant extends CApplicationComponent
{
	private $_api = null;
	
	private $_authType = null;
	private $_authObject = null;
	private $_network = null;
	
	protected $_userInfo = null;
	
	private $_instanceClass = null;
	
	private function getTPerformant($new = false) {
		if($this->_api === null || $new) {
			$network = 'http://' . ($this->_authType == 'oauth' ? $this->instance->network->network : $this->_network);
			$this->_api = new TPerformant( $this->_authType, $this->_authObject, $network );
			$this->_userInfo = $this->_api->user_loggedin();
		}
		return $this->_api;
	}
	
	public function getApi() {
		return $this->getTPerformant();
	}
	
	public function reloadApi() {
		return $this->getTPerformant(true);
	}
	
	public function setConnection( $connection ) {
		if(!is_array( $connection ))
			return false;
		if($this->_authType === null) {
			$this->_authType = $connection['type'];
		}
		
		if($this->_authObject === null){
			if($connection['type'] == 'simple') {
				$this->_authObject = array( 'user' => $connection['user'], 'pass' => $connection['pass'] );
				
				if($this->_network === null)
					$this->_network = $connection['api_url'];
			} else {
				if($this->_instanceClass === null) {
					if(isset($connection['instanceClass']))
						$this->_instanceClass = $connection['instanceClass'];
					else
						$this->_instanceClass = 'CTPInstance';
				}
				
				try {
					Yii::$classMap['HTTP_OAuth_Consumer'] = 'HTTP/OAuth/Consumer.php';
					$this->_authObject = new HTTP_OAuth_Consumer(
						$connection['key'],
						$connection['secret'],
						$this->instance->token,
						$this->instance->secret
					);
				} catch(CHttpException $e) {
					Yii::trace(CVarDumper::dumpAsString($e));
					if($e->statusCode == 403) {
						$this->_authObject = array(
							'key' => $connection['key'],
							'secret' =>$connection['secret']
						);
					} else {
						throw $e;
					}
				}
			}
		}
	}
	
	public function getOauth() {
		if($this->_authType != 'oauth')
			return false;
		return $this->_authObject;
	}
	
	public function getUserinfo() {
		if(!$this->_userInfo)
			$this->getApi();
		return $this->_userInfo;
	}
	
	public function getInstance() {
		if($this->_authType != 'oauth')
			return false;
		
		$token = isset($_GET['token']) ? $_GET['token'] : '';
		$ptoken = isset($_GET['public_token']) ? $_GET['public_token'] : '';
		$criteria = new CDbCriteria(array(
			'condition' => 'token=:token OR public_token=:public_token',
			'params' => array( ':token' => $token, ':public_token' => $ptoken ),
			'limit' => 1,
		));
		
		$method = new ReflectionMethod($this->_instanceClass, 'model');
		$model = $method->invoke(null);

		if(!$model->exists($criteria))
			throw new CHttpException(403,'Invalid application token.');
		
		return $model->find($criteria);
	}
	
	public function oauthInit( $callbackURL ) {
		$network  = $_GET['network'];
		
		Yii::$classMap['HTTP_OAuth_Consumer'] = 'HTTP/OAuth/Consumer.php';
		$consumer = new HTTP_OAuth_Consumer( $this->oauth['key'], $this->oauth['secret'] );
		$consumer->getRequestToken("http://".$network."/oauth/request_token", $callbackURL);
		
		// Store tokens
		$session = new CHttpSession;
		$session->open();
		
		$session['token'] = $consumer->getToken();
		$session['token_secret'] = $consumer->getTokenSecret();
		$session['network'] = $network;
		
		$url = $consumer->getAuthorizeUrl('http://'.$network.'/oauth/authorize');
		header("Location: $url");
		Yii::app()->end();
	}
	
	public function oauthCallback() {
		$session = new CHttpSession;
		$session->open();
		
		$network = $session['network'];
		
//		$key = Yii::app()->params['app_key'];
//		$secret = Yii::app()->params['app_secret'];
		
		Yii::$classMap['HTTP_OAuth_Consumer'] = 'HTTP/OAuth/Consumer.php';
		$consumer = new HTTP_OAuth_Consumer($this->oauth['key'], $this->oauth['secret'], $session['token'], $session['token_secret']);
		$consumer->getAccessToken("http://".$network."/oauth/access_token", $_GET['oauth_verifier']);
		
		// add instance to DB
		$instanceClass = new ReflectionClass($this->_instanceClass);
		$instance = $instanceClass->newInstance();
		$instance->attributes = array(
			'token' => $consumer->getToken(),
			'secret' => $consumer->getTokenSecret(),
		);
		$instance->setNetwork($session['network']);
		
		$saved = $instance->save();
		if($saved) {
			header("Location: http://".$network."/oauth_clients/show?token=". $consumer->getToken());
			Yii::app()->end();
		} else {
			throw new CException('Unable to save application instance');
		}
	}
	
	public $caching = false;
	
	protected function isCacheable( $method ) {
		return in_array(
			$method,
			array(
				'user_show',
				'campaigns_list',
				'campaigns_search',
				'campaign_show',
				'campaigns_listforaffiliate',
				'campaigns_listforowner',
				'campaign_showforowner',
				'affiliates_search',
				'affiliates_listforadvertiser',
				'commissions_search',
				'commissions_listforadvertiser',
				'commissions_listforaffiliate',
				'commission_show',
				'sites_list',
				'site_show',
				'sites_search',
				'sites_listforowner',
				'txtlinks_list',
				'txtlink_show',
				'txtlinks_search',
				'txtads_list',
				'txtad_show',
				'txtads_search',
				'banners_list',
				'banner_show',
				'banners_search',
				'product_stores_list',
				'product_store_show',
				'product_store_showitems',
				'product_store_showitem',
				'product_store_products_search',
				'ad_groups_list',
				'ad_group_show',
				'feeds_list',
				'received_messages_list',
				'sent_messages_list',
				'message_show',
				'admin_affiliate_invoices_list',
				'admin_affiliate_invoices_search',
				'admin_advertiser_invoices_list',
				'admin_advertiser_invoices_search',
				'admin_campaigns_list',
				'admin_campaigns_search',
				'admin_affiliate_commissions_list',
				'admin_affiliates_commissions_stats',
				'admin_advertiser_commissions_list',
				'admin_advertisers_commissions_stats',
				'admin_deposits_list',
				'admin_users_list',
				'admin_users_search',
				'admin_users_pending_list',
				'hooks_list',
			)
		);
	}
	
	public function __call( $name, $args = null ) {
		if($this->caching && $this->isCacheable($name)) {
			$key = md5(serialize($args));
			$result = Yii::app()->cache->get($key); 
			
			if(!$result) {
				$expire = Yii::app()->params['cache_expire'] || 0;
				$result = call_user_func_array($this->api->$name, $args);
				Yii::app()->cache->set($key, $result, $expire);
			}
		} else {
			$result = call_user_func_array(array($this->api, $name), $args);
		}
		
		return $result;
	}
}

?>