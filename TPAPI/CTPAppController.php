<?php

class CTPAppController extends Controller
{
	public function getCallbackUrl() {
		return $this->createAbsoluteUrl('app/callback');
	}
	
	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionInit() {
//		Yii::trace(Yii::getPathOfAlias('ext.TPAPI.2Performant.PEAR.HTTP.OAuth.Consumer'));
//		Yii::app()->end();
		Yii::app()->api->oauthInit($this->callbackUrl);
	}
	
	public function actionCallback() {
		Yii::app()->api->oauthCallback();
	}
	
	public function actionDelete() {
		$this->api->instance->delete();
	}
	
	public function getApi() {
		return Yii::app()->api;
	}
}