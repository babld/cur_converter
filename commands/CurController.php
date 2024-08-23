<?php

namespace app\commands;

use app\components\parsers\ParserAbstract;
use Yii;
use yii\base\InvalidConfigException;
use yii\di\NotInstantiableException;

class CurController extends \yii\console\Controller
{
    /**
     * @throws NotInstantiableException
     * @throws InvalidConfigException
     */
    public function actionIndex(string $parserCode = 'ru')
    {
        /** @var ParserAbstract $parser */
        $parser = Yii::$container->get(Yii::$app->params['curParser'][$parserCode]['class']);

        $parser->consoleParse();
    }
}