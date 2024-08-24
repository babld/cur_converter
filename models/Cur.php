<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * @property int $id
 * @property string $name
 * @property string $char_code
 * @property int $parser
 */
class Cur extends ActiveRecord
{
    public function rules(): array
    {
        return [
            [['char_code', 'parser', 'name'], 'required'],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'char_code' => 'Международный код',
            'parser' => 'Парсер',
            'name' => 'Название',
        ];
    }

    public static function tableName(): string
    {
        return 'cur';
    }

    public static function getDropdown($parser = 1): array
    {
        return ArrayHelper::map(self::find()->where(['parser' => $parser])->all(), 'char_code', 'name');
    }
}