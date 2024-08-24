<?php

namespace app\models;

use yii\base\Model;
use yii\helpers\ArrayHelper;
use Yii;

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

    public function parserDropdown(): array
    {
        return ArrayHelper::map(
            Yii::$app->params['curParser'],
            fn ($item) => $item['id'],
            fn ($item) => (Yii::$container->get($item['class']))->getTitle()
        );
    }
}
