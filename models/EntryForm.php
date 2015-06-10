<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\models;

use yii\base\Model;

class EntryForm extends Model
{
    private $name;
    private $email;
    private $gender;
    private $birth;

    public function rules()
    {
        return [
            [['gender', 'birth', 'name', 'email' ], 'required'],
            ['email', 'email'],
        ];
    }
	public  function getName() {
		return $this->name;
	}

	public  function getEmail() {
		return $this->email;
	}

	public  function getGender() {
		return $this->gender;
	}

	public  function getBirth() {
		return $this->birth;
	}

	public  function setName($name) {
		$this->name = trim( $name );
	}

	public  function setEmail($email) {
		$this->email = trim( $email );
	}

	public  function setGender($gender = 'male') {
		$this->gender = trim( $gender );
	}

	public  function setBirth($birth = '1970-01-01') {
		$this->birth = trim( $birth );
	}


}