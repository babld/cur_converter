<?php

use yii\widgets\ActiveForm;
use yii\web\View;
use app\models\ConverterForm;
use app\models\Cur;
use yii\helpers\Html;

/**
 * @var View $this
 * @var ConverterForm $model
 */

$this->title = 'Конвертации валют';
?>

<div class="site-index">
    <?php $form = ActiveForm::begin([
        'method' => 'post'
    ]) ?>

    <div class="row">
        <div class="col-sm-5">
            <?= $form->field($model, 'from')?>
            <?= $form->field($model, 'curFromId')->dropDownList(Cur::getDropdown())?>
        </div>
        <div class="col-sm-2">

        </div>
        <div class="col-sm-5">
            <?= $form->field($model, 'to')->textInput(['readonly' => true])?>
            <?= $form->field($model, 'curToId')->dropDownList(Cur::getDropdown())?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-5">
            <?= $form->field($model, 'parser')->dropDownList($model->parserDropdown())?>
        </div>
    </div>

    <?= Html::submitButton('Конвертировать', ['class' => 'btn btn-primary']) ?>

    <?php ActiveForm::end() ?>
</div>