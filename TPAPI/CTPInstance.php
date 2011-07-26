<?php

/**
 * This is the model class for table "instances".
 *
 * The followings are the available columns in table 'tb_instances':
 * @property integer $id
 * @property string $token
 * @property string $secret
 * @property string $network_id
 * @property string $public_token
 */
abstract class CTPInstance extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Instance the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('token, secret, network', 'required'),
			array('token', 'length', 'max'=>20),
			array('secret', 'length', 'max'=>40),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, token, secret, public_token', 'safe', 'on'=>'search'),
		);
	}
	
	abstract protected function networkClassName();

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'network'=>array(self::BELONGS_TO, $this->networkClassName(), 'network_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'token' => 'Token',
			'secret' => 'Secret',
			'public_token' => 'Public Token',
		);
	}
	
	public function setNetwork($value) {
		$networkClass = new ReflectionClass($this->networkClassName());
		$modelMethod = new ReflectionMethod($this->networkClassName(), 'model');
		$model = $modelMethod->invoke(null);
		$network = $model->findByAttributes(array(
			'network'=>$value,
		));
		
		if(!$network) {
			$network = $networkClass->newInstance();
			$network->network = $value;
			$network->save();
		}
		
		$this->network_id = $network->id;
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('token',$this->token,true);
		$criteria->compare('secret',$this->secret,true);
		$criteria->compare('network',$this->network,true);
		$criteria->compare('public_token',$this->public_token,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
		));
	}
	
	protected function beforeSave() {
		if( parent::beforeSave() ) {
			if( !$this->secret )
				return false;
			
			$this->public_token = md5( Yii::app()->params['app_secret'] . '-' . $this->secret );
			return true;
		} else {
			return false;
		}
	}
}