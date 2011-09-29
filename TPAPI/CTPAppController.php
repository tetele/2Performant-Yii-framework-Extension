<?php

class CTPAppController extends Controller
{
	public function getCallbackUrl() {
		return $this->createAbsoluteUrl('callback');
	}
	
	public function actionInit() {
		$this->api->oauthInit($this->callbackUrl);
	}
	
	public function actionCallback() {
		$this->api->oauthCallback($this->callbackUrl);
	}
	
	public function actionDelete() {
		$this->api->instance->delete();
	}
	
	private $_api = null;
	public function getApi() {
		if($this->_api === null) {
			$this->_api = $this->getApiObject();
		}
		return $this->_api;
	}
	
	protected function getApiObject() {
		return Yii::app()->tpapi;
	}
}