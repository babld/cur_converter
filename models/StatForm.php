<?php

namespace app\models;

use yii\base\Model;

/**
 * @property int $curId
 * @property int $parser
 */
class StatForm extends Model
{
    public $cur;
    public $parser;

    public function rules(): array
    {
        return [
            [['cur', 'parser'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'cur' => 'Валюта',
            'parser' => 'Парсер',
        ];
    }
}
