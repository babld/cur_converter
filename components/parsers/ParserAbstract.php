<?php

namespace app\components\parsers;

use app\models\ConverterForm;
use app\models\Cur;
use app\models\CurDetail;
use GuzzleHttp\Client;
use yii\base\BaseObject;

abstract class ParserAbstract extends BaseObject
{
    public Client $client;
    protected string $title = 'ParserAbstract';

    public function __construct($config = [])
    {
        $this->client = new Client([
            'timeout' => 30
        ]);
        parent::__construct($config);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    abstract public function parse(ConverterForm $model);

    public function createCur($name, $charCode, $parserId): Cur
    {
        $model = new Cur();

        $model->name = $name;
        $model->char_code = $charCode;
        $model->parser = $parserId;
        $model->save();

        return $model;
    }

    public function createCurDetail($curId, $value = 1, $datetime = null): ?CurDetail
    {
        $model = new CurDetail();

        $model->datetime = $datetime ?: date('Y-m-d H:i:s');
        $model->value = $value;
        $model->cur_id = $curId;
        $model->save();

        return $model;
    }

    public function findCurModel($charCode, $parserId): ?Cur
    {
        return Cur::findOne([
            'char_code' => $charCode,
            'parser' => $parserId,
        ]);
    }

    public function findCurDetail($curId, $date): ?CurDetail
    {
        $dateTime = new \DateTime($date);

        return CurDetail::find()
            ->where(['cur_id' => $curId])
            ->andWhere('datetime between :d1 and :d2', [
                'd1' => $dateTime->format('Y-m-d 00:00:00'),
                'd2' => $dateTime->format('Y-m-d 23:59:59'),
            ])
            ->one();
    }

    /**
     * @throws \Exception
     */
    public function findOrCreateCur($charCode, $parserId, $name): Cur
    {
        $model = $this->findCurModel($charCode, $parserId);

        if ($model) {
            return $model;
        }

        return $this->createCur($name, $charCode, $parserId);
    }

    public function findOrCreateCurDetail(int $curId, $date, $value): CurDetail
    {
        $model = $this->findCurDetail($curId, $date);

        if ($model) {
            return $model;
        }

        return $this->createCurDetail($curId, $value, $date);
    }

    abstract public function consoleParse();
}