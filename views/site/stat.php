<?php

use yii\helpers\Json;
use yii\bootstrap5\ActiveForm;
use app\models\StatForm;
use app\models\Cur;
use yii\helpers\Html;

/**
 * @var array $chartOptions
 * @var StatForm $model
 */

?>

<div class="site-index">
    <?php $form = ActiveForm::begin([
        'method' => 'post'
    ]) ?>

    <div class="row">
        <div class="col-sm-5">
            <?= $form->field($model, 'cur')->dropDownList(Cur::getDropdown())?>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-5">
            <?= $form->field($model, 'parser')->dropDownList($model->parserDropdown())?>
        </div>
    </div>

    <?= Html::submitButton('Показать статистику', ['class' => 'btn btn-primary']) ?>

    <?php ActiveForm::end() ?>
</div>

<?php if (!empty($chartOptions)): ?>
    <div id="container"></div>
    <?php $this->registerJs('var chart = Highcharts.chart("container", ' . Json::encode($chartOptions) . ');'); ?>
<?php endif ?>
