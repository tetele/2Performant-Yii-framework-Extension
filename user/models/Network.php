<?php 

class Network extends CTPNetwork {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	public function tableName() {
		return 'your_networks_table';
	}
}