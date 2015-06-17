<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\RoleSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Home page';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="role-index">

<?= Html::encode($homePage )?>
<?= var_dump( $new_goods_recommend_list )?>

</div>
