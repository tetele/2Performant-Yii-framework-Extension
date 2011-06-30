<?php
class Instance extends CTPInstance {
public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	public function tableName() {
		return 'your_instances_table';
	}
}