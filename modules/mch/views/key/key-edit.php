<?php
defined('YII_ENV') or exit('Access Denied');
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/27
 * Time: 11:36
 */

$urlManager = Yii::$app->urlManager;
$this->title = 'key码编辑';
$this->params['active_nav_group'] = 1;
?>
<div class="panel mb-3">
    <div class="panel-header"><?= $this->title ?></div>
    <div class="panel-body">
        <form class="auto-form" method="post" return="<?= $urlManager->createUrl(['mch/key/key']) ?>">
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                    <label class="col-form-label required">key码</label>
                </div>
                <div class="col-sm-6">
                    <input class="form-control" type="text" name="model[key]" value="<?= $model['key'] ?>">
                </div>
            </div>
            <div class="form-group row">
                <div class="form-group-label col-sm-2 text-right">
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary auto-form-btn" href="javascript:">保存</a>
                    <input type="button" class="btn btn-default ml-4" 
                           name="Submit" onclick="javascript:history.back(-1);" value="返回">
                </div>
            </div>
        </form>
    </div>
</div>