<?php

namespace app\models;

use yii\base\Model;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * @property string $from
 * @property string $tp
 * @property string $curFromId
 * @property string $curToId
 * @property string $parser
 */
class ConverterForm extends Model
{
    public string $from = '';
    public string $to = '';
    public $curFromId;
    public $curToId;
    public string $parser = '';

    public function init()
    {
        parent::init();
    }

    public function setParser()
    {
        var_dump('test');exit;
    }

    public function rules(): array
    {
        return [
            [['from', 'curFromId', 'curToId', /*'parser'*/], 'required'],
            [['from'], 'number'],
            ['parser', 'string'],
            // [['curFromId', 'curToId'], 'each', 'rule' => ['exist', 'targetAttribute' => 'id', 'targetClass' => Cur::class]],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'from' => 'Сумма (откуда)',
            'to' => 'Сумма (куда)',
            'curFromId' => 'Валюта (откуда)',
            'curToId' => 'Валюта (куда)',
            'parser' => 'Парсер',
        ];
    }

    public function parserDropdown(): array
    {
        return ArrayHelper::map(
            Yii::$app->params['curParser'],
            fn ($item) => $item['code'],
            fn ($item) => (Yii::$container->get($item['class']))->getTitle()
        );
    }
}
