<?php

namespace app\models;

use yii\db\ActiveRecord;

class Country extends ActiveRecord
{
//	private $countryId;
//	private $iso2Code;
//	private $iso3Code;
//	private $fullName;
	
	public static function findByCondition( $condition ){
		return parent::findByCondition( $condition );
	}
}