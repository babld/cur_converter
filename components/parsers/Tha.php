<?php

namespace app\components\parsers;

use app\components\ParserInterface;
use app\models\ConverterForm;
use app\models\Cur;
use app\models\CurDetail;
use Yii;
use yii\helpers\ArrayHelper;

class Tha extends ParserAbstract implements ParserInterface
{
    public string $urlTpl = 'https://apigw1.bot.or.th/bot/public/Stat-ExchangeRate/v2/DAILY_AVG_EXG_RATE/?start_period={START_PERIOD}&end_period={END_PERIOD}&currency={CUR}';
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
//                $curModel = $this->findOrCreateCur($cur, 2, 'Таиландских батов');
//                $curDetail = $this->findOrCreateCurDetail($curModel->id, date('Y-m-d H:i:s'), 1);
//                $result[] = $curDetail;
                $result[] = new CurDetail(['value' => 1]);
                continue;
            }

            $response = $this->makeRequest($cur);

            $arr = $response['result']['data']['data_detail'] ?? false;

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
                    $result[] = $curDetail;
                    $first = false;
                }
            }
        }

        return $result;
    }

    protected function getUrl($cur): string
    {
        $result = str_replace('{START_PERIOD}', date('Y-m-d', strtotime('-1 month')), $this->urlTpl);

        $result = str_replace('{END_PERIOD}', date('Y-m-d'), $result);

        return str_replace('{CUR}', $cur, $result);
    }

    public function makeRequest($cur): array
    {
        return Yii::$app->cache->getOrSet('cur-' . $cur, function() use ($cur) {
            $request = $this->client->get($this->getUrl($cur), [
                'headers' => [
                    'X-IBM-Client-Id' => $this->apiKey,
                ]
            ]);

            return json_decode($request->getBody()->getContents(), true);
        }, 60 * 60);
    }

    public function consoleParse()
    {
        foreach (Cur::getDropdown() as $charCode => $name) {
            $response = $this->makeRequest($charCode);

            $arr = $response['result']['data']['data_detail'] ?? false;

            if (!$arr) {
                return false;
            }

            $curModel = null;
            foreach ($arr as $detail) {
                if (!$curModel) {
                    $curModel = $this->findOrCreateCur($charCode, self::PARSER_ID, ArrayHelper::getValue($detail, 'currency_name_eng'));
                }

                $this->findOrCreateCurDetail(
                    $curModel->id,
                    (new \DateTime($detail['period']))->format('Y-m-d H:i:s'),
                    ArrayHelper::getValue($detail, 'mid_rate')
                );
            }

            sleep(5); // В консоли не страшно
        }
    }
}
