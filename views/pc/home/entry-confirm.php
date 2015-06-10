<?php
use yii\helpers\Html;
?>
<p>You have entered the following information:</p>

<ul>
    <li><label>Name</label>: <?= Html::encode($model->getName() ) ?></li>
    <li><label>Email</label>: <?= Html::encode($model->getEmail() ) ?></li>
    <li><label>Gender</label>: <?= Html::encode($model->getGender() ) ?></li>
    <li><label>Birth</label>: <?= Html::encode($model->getBirth() ) ?></li>
</ul>
