<?php

namespace app\components\parsers;

use app\components\ParserInterface;
use app\models\ConverterForm;
use app\models\CurDetail;
use yii\helpers\ArrayHelper;
use Yii;

class Cbrf extends ParserAbstract implements ParserInterface
{
    public string $url = 'http://www.cbr.ru/scripts/XML_daily.asp';
    protected string $title = 'ЦБ России';
    const PARSER_ID = 1;

    public function parse(ConverterForm $model): array|false
    {
        $result = [];
        foreach ([$model->curFromId, $model->curToId] as $cur) {
            if ($cur === 'RUB') {
                $result[] = new CurDetail(['value' => 1]);
                continue;
            }

            $curModel = $this->findCurModel($cur, self::PARSER_ID);

            if (!$curModel && !$this->makeRequest()) {
                Yii::$app->getSession()->setFlash('danger', 'Сервис временно недоступен, попробуйте позже');
                return false;
            }

            $curModel = $this->findCurModel($cur, self::PARSER_ID);
            $curDetailModel = $this->findCurDetail($curModel->id, date('Y-m-d H:i:s'));

            if (!$curDetailModel && !$this->makeRequest()) {
                Yii::$app->getSession()->setFlash('danger', 'Сервис временно недоступен, попробуйте позже');
                return false;
            }

            $result[] = $this->findCurDetail($curModel->id, date('Y-m-d H:i:s'));
        }

        return $result;
    }

    public function makeRequest($date = '')
    {
        $date = new \DateTime(date('Y-m-d H:i:s'));
        $url = $this->url;

        if (!empty($date)) {
            $date = new \DateTime($date);
            $url = $this->url . '?date_req=' . $date->format('d/m/Y');
        }

        try {
            $request = $this->client->post($url);
        } catch (\Throwable $exception) {
            return false;
        }

        $xml = new \SimpleXMLElement($request->getBody()->getContents());

        foreach ($xml as $value) {
            $curModel = $this->findOrCreateCur(ArrayHelper::getValue($value, 'CharCode'), self::PARSER_ID, ArrayHelper::getValue($value, 'Name'));
            $this->findOrCreateCurDetail($curModel->id, $date->format('Y-m-d H:i:s'), (string)$value->VunitRate);
        }
    }

    public function consoleParse()
    {
        $dateStart = (new \DateTime(date('Y-m-d')))->modify('-1 week');
        $dateEnd = (new \DateTime(date('Y-m-d')));

        $dateInterval = new \DateInterval('P1D');

        $datePeriod = new \DatePeriod($dateStart, $dateInterval, $dateEnd);

        foreach ($datePeriod as $date) {
            /** @var \DateTime $date */
            $this->makeRequest($date->format('Y-m-d H:i:s'));
            sleep(5);
        }
    }
}
