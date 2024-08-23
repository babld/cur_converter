<?php

namespace app\models;

/**
 * @property string $datetime
 * @property int $cur_id
 * @property string $value
 * @property Cur $cur
 */
class CurDetail extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'cur_detail';
    }

    public function rules()
    {
        return [
            [['cur_id', 'value', 'datetime'], 'required']
        ];
    }

    public function getCur()
    {
        return $this->hasOne(Cur::class, ['id' => 'cur_id']);
    }
}