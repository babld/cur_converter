<?php

use yii\helpers\Json;
use yii\bootstrap5\ActiveForm;
use app\models\StatForm;
use app\models\Cur;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var array $chartOptions
 * @var StatForm $model
 */

?>

<div class="site-index">
    <h1>Статистика валют</h1>

    <?php $form = ActiveForm::begin([
        'method' => 'post',
        'options' => ['class' => 'form'],
    ]) ?>

    <div class="row">
        <div class="col-sm-5">
            <?= $form->field($model, 'cur')->dropDownList(Cur::getDropdown($model->parser ?? 1), ['class' => 'cur-select'])?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-5">
            <?= $form->field($model, 'parser')->dropDownList($model->parserDropdown(), ['class' => 'ajax-parser-load'])?>
        </div>
    </div>

    <?= Html::submitButton('Показать статистику', ['class' => 'btn btn-primary']) ?>

    <?php ActiveForm::end() ?>
</div>

<?php if (!empty($chartOptions)): ?>
    <div id="container"></div>
    <?php $this->registerJs('var chart = Highcharts.chart("container", ' . Json::encode($chartOptions) . ');'); ?>
<?php endif ?>

<?php
$url = Url::to(['/site/get-cur-by-stat']);

$this->registerJs(<<<JS
    $('.ajax-parser-load').change(function() {
        $.post('$url', $('form.form').serialize(), function (response) {
            $('.cur-select').empty().append(response);
        });
    });
JS)?>
