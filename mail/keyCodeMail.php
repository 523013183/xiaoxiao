<?php if($type == 1) {?>
<p>尊敬的用户，您己成功购买的华美一元外教课程，请前往注册确认绑定的手机号<a href="<?php echo $url; ?>"><?php echo $url; ?></a> 当天即可学习，7天无理由退款！</p>
<?php } else {?>
<p>尊敬的用户，您购买的华美一元外教学习兑换码已生成<?php echo $code;?>,请前往兑换页<a href="<?php echo $url; ?>"><?php echo $url; ?></a>将兑换码复制拷贝并注册下载当天即可登录学习，7天无理由退款！</p>
<?php }?>
