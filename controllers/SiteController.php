<?php

namespace app\controllers;

use app\components\ParserInterface;
use app\components\parsers\ParserAbstract;
use app\models\ConverterForm;
use app\models\CurDetail;
use app\models\StatForm;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\Response;
use GuzzleHttp\Client;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use DateTime;
use app\models\Cur;

class SiteController extends Controller
{
    public Client $client;

    public function __construct($id, $module, $config = [])
    {
        $this->client = new Client();

        parent::__construct($id, $module, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'stat', 'index'],
                'rules' => [
                    [
                        'actions' => ['logout', 'index', 'stat'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['login'],
                        'allow' => true,
                        'roles' => ['?'],
                    ]
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string|Response
     */
    public function actionIndex()
    {
        $model = new ConverterForm();

        if ($model->load(Yii::$app->request->post())) {
            /** @var ParserAbstract $parser */
            $parser = Yii::$container->get(Yii::$app->params['curParser'][$model->parser]['class']);

            if (!$parser instanceof ParserInterface) {
                Yii::$app->getSession()->setFlash('danger', 'Ошибка. Неизвестный парсер');
            }

            $result = $parser->parse($model);

            if ($result === false) {
                Yii::$app->getSession()->setFlash('danger', 'Неизвестная ошибка, попробуйте позже');

                return $this->render('index', [
                    'model' => $model
                ]);
            }

            $koef = $this->getValue($result[0]) / $this->getValue($result[1]);

            $model->to = number_format($koef * (float) $model->from, 2, '.', ' ');
        }

        return $this->render('index', [
            'model' => $model,
        ]);
    }

    public function getValue($value)
    {
        return (float) str_replace(',', '.', $value);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     */
    public function actionLogout(): Response
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionStat(): Response|string
    {
        $model = new StatForm();

        if ($model->load(Yii::$app->request->post())) {
            $data = CurDetail::find()
                ->leftJoin('cur', 'cur.id = cur_detail.cur_id')
                ->where([
                    'char_code' => $model->cur,
                    'parser' => $model->parser,
                ])
                ->andWhere('datetime between :d1 and :d2', [
                    'd1' => (new \DateTime(date('Y-m-d')))->modify('-1 week')->format('Y-m-d 00:00:00'),
                    'd2' => (new \DateTime(date('Y-m-d')))->format('Y-m-d 23:59:59'),
                ])
                ->orderBy(['datetime' => SORT_ASC])
                ->all();

            $chartOptions = [
                'title' => [
                    'text' => 'Статистика за неделю',
                    'align' => 'center',
                ],
                'yAxis' => [
                    'title' => ['text' => 'RUB'],
                ],
                'xAxis' => [
                    'categories' => array_map(fn($item) => (new DateTime($item->datetime))->format('Y-m-d'), $data),
                ],
                'series' => [
                    [
                        'data' => array_map(fn ($item) => (float) str_replace(',', '.', $item->value), $data),
                        'type' => 'line',
                        'name' => Cur::findOne(['char_code' => $model->cur])->name
                    ]
                ],
            ];

            return $this->render('stat', [
                'chartOptions' => $chartOptions,
                'model' => $model,
            ]);
        }

        return $this->render('stat', [
            'model' => $model,
            'chartOptions' => ''
        ]);
    }

    public function actionGetCurByStat()
    {
        $model = new StatForm();
        $model->load(Yii::$app->request->post());

        return $this->getDropdown($model->parser);
    }

    public function actionGetCurByConverter()
    {
        $model = new ConverterForm();
        $model->load(Yii::$app->request->post());

        return $this->getDropdown((Yii::$container->get(Yii::$app->params['curParser'][$model->parser]['class']))::PARSER_ID);
    }

    public function getDropdown($parserId)
    {
        $result = '';

        foreach (Cur::findAll(['parser' => $parserId]) as $value) {
            $result .= '<option value="' . $value->char_code . '">' . $value->name . '</option>';
        }

        return $result;
    }

}
