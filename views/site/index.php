<?php

use yii\widgets\ActiveForm;
use yii\web\View;
use app\models\ConverterForm;
use app\models\Cur;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var View $this
 * @var ConverterForm $model
 */

$this->title = 'Конвертации валют';
?>

<div class="site-index">
    <?php $form = ActiveForm::begin([
        'method' => 'post',
        'options' => ['class' => 'form'],
    ]) ?>

    <div class="row">
        <div class="col-sm-5">
            <?= $form->field($model, 'from')?>
            <?= $form->field($model, 'curFromId')->dropDownList(Cur::getDropdown(Yii::$app->params['curParser'][$model->parser]['id'] ?? 1), ['class' => 'cur-select form-control'])?>
        </div>
        <div class="col-sm-2">

        </div>
        <div class="col-sm-5">
            <?= $form->field($model, 'to')->textInput(['readonly' => true])?>
            <?= $form->field($model, 'curToId')->dropDownList(Cur::getDropdown(Yii::$app->params['curParser'][$model->parser]['id'] ?? 1), ['class' => 'cur-select form-control'])?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-5">
            <?= $form->field($model, 'parser')->dropDownList($model->parserDropdown(), ['class' => 'ajax-parser-load form-control'])?>
        </div>
    </div>

    <?= Html::submitButton('Конвертировать', ['class' => 'btn btn-primary']) ?>

    <?php ActiveForm::end() ?>
</div>

<?php
$url = Url::to(['/site/get-cur-by-converter']);

$this->registerJs(<<<JS
    $('.ajax-parser-load').change(function() {
        $.post('$url', $('form.form').serialize(), function (response) {
            $('.cur-select').empty().append(response);
        });
    });
JS)?>
