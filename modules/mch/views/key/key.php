<?php
defined('YII_ENV') or exit('Access Denied');

/**
 * Created by IntelliJ IDEA.
 * User: Administrator
 * Date: 2017/6/19
 * Time: 16:52
 */

use yii\widgets\LinkPager;

$urlManager = Yii::$app->urlManager;
$this->title = 'key管理';
$this->params['active_nav_group'] = 4;
$urlPlatform = Yii::$app->controller->route;
?>

<style>
    .table tbody tr td{
        vertical-align: middle;
    }
</style>

<div class="panel mb-3" id="app">
    <div class="panel-header"><?= $this->title ?></div>
    <div class="panel-body">
        <div class="dropdown float-left">
            <a href="<?= $urlManager->createUrl([$urlStr . '/mch/key/key-edit']) ?>" class="btn btn-primary"><i
                        class="iconfont icon-playlistadd"></i>添加key</a>
        </div>
        <div class="float-right mb-4">

        </div>
        <table class="table table-bordered bg-white">
            <thead>
            <tr>
                <th>ID</th>
                <th>key码</th>
                <th>是否兑换</th>
                <th>兑换手机号</th>
                <th>是否支付</th>
                <th>兑换次数</th>
            </tr>
            </thead>
            <?php foreach ($list as $u) : ?>
                <tr>
                    <td><?= $u['id']; ?></td>
                    <td>
                        <?= $u['key']; ?>
                    </td>
                    <td>
                        <?= $u['status'] == 1 ? '已兑换' : '未兑换'; ?>
                    </td>
                    <td>
                        <?= $u['phone']; ?>
                    </td>
                    <td>
                        <?= $u['is_pay'] == 1 ? '已支付' : '未支付'; ?>
                    </td>
                    <td><?= $u['exchange_time']; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <div class="text-center">
            <nav aria-label="Page navigation example">
                <?php echo LinkPager::widget([
                    'pagination' => $pagination,
                    'prevPageLabel' => '上一页',
                    'nextPageLabel' => '下一页',
                    'firstPageLabel' => '首页',
                    'lastPageLabel' => '尾页',
                    'maxButtonCount' => 5,
                    'options' => [
                        'class' => 'pagination',
                    ],
                    'prevPageCssClass' => 'page-item',
                    'pageCssClass' => "page-item",
                    'nextPageCssClass' => 'page-item',
                    'firstPageCssClass' => 'page-item',
                    'lastPageCssClass' => 'page-item',
                    'linkOptions' => [
                        'class' => 'page-link',
                    ],
                    'disabledListItemSubTagOptions' => ['tag' => 'a', 'class' => 'page-link'],
                ])
                ?>
            </nav>
            <div class="text-muted">共<?= $row_count ?>条数据</div>
        </div>
    </div>
</div>
