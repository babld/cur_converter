<?php

namespace app\components\parsers;

use app\components\ParserInterface;
use app\models\ConverterForm;
use Psr\Http\Message\ResponseInterface;
use Yii;
use yii\helpers\ArrayHelper;

class Tha extends ParserAbstract implements ParserInterface
{
    public string $urlTpl = 'https://apigw1.bot.or.th/bot/public/Stat-ExchangeRate/v2/DAILY_AVG_EXG_RATE/?start_period={START_PERIOD}&end_period={END_PERIOD}';
    protected string $title = 'ЦБ Тайланда';
    protected string $apiKey = 'c2bbe063-d0ff-456c-bc08-fbd5115fb340';
    const PARSER_ID = 2;

    /**
     * @throws \Exception
     */
    public function parse(ConverterForm $model): array|false
    {
        $result = [];
        foreach ([$model->curFromId, $model->curToId] as $cur) {
            if ($cur === 'THB') {
                $result[] = 1;
                continue;
            }

            $request = $this->makeRequest();
            /** @var ResponseInterface $request */
            if ($request->getStatusCode() !== 200) {
                Yii::$app->getSession()->setFlash('danger', 'ЦБ Таиланда не знает такой валюты');
                return false;
            }

            $data = json_decode($request->getBody()->getContents(), true);

            $arr = $data['result']['data']['data_detail'] ?? false;

            if (!$arr) {
                Yii::$app->getSession()->setFlash('danger', 'Сервис временно недоступен, попробуйте позже');
                return false;
            }

            $curModel = null;
            $first = true;
            foreach ($arr as $detail) {
                if (!$curModel) {
                    $curModel = $this->findOrCreateCur($cur, self::PARSER_ID, ArrayHelper::getValue($detail, 'currency_name_eng'));
                }

                $curDetail = $this->findOrCreateCurDetail(
                    $curModel->id,
                    (new \DateTime($detail['period']))->format('Y-m-d H:i:s'),
                    ArrayHelper::getValue($detail, 'mid_rate')
                );
                if ($first) {
                    $result[] = $curDetail->value;
                    $first = false;
                }
            }
        }

        return $result;
    }

    protected function getUrl(): string
    {
        $result = str_replace('{START_PERIOD}', date('Y-m-d', strtotime('-1 month')), $this->urlTpl);

        return str_replace('{END_PERIOD}', date('Y-m-d'), $result);
    }

    public function makeRequest(): ResponseInterface
    {
        return Yii::$app->cache->getOrSet('cur', function() {
            try {
                return $this->client->get($this->getUrl(), [
                    'headers' => [
                        'X-IBM-Client-Id' => $this->apiKey,
                    ]
                ]);
            } catch (\GuzzleHttp\Exception\ClientException $exception) {
                return $exception->getResponse();
            }
        }, 60 * 60); // Кешируем на час
    }

    public function consoleParse(): void
    {
        echo "Parse CB THA \n";

        $request = $this->makeRequest();

        if ($request->getStatusCode() !== 200) {
            echo "Request error \n";
            return;
        }

        $data = json_decode($request->getBody()->getContents(), true);
        $arr = $data['result']['data']['data_detail'] ?? false;

        if (!$arr) {
            return;
        }

        foreach ($arr as $detail) {
            $curModel = $this->findOrCreateCur(ArrayHelper::getValue($detail, 'currency_id'), self::PARSER_ID, ArrayHelper::getValue($detail, 'currency_name_eng'));

            $this->findOrCreateCurDetail(
                $curModel->id,
                (new \DateTime($detail['period']))->format('Y-m-d H:i:s'),
                ArrayHelper::getValue($detail, 'mid_rate')
            );
        }
    }
}
