<?php $foot = CConfig::get('foot_conf'); ?>
<div class="footer">
    <div class="content pull-left">
        <p><?php  if(isset($foot['msg'])) echo $foot['msg']; ?></p>
        <p><?php  if(isset($foot['icp'])) echo $foot['icp']; ?></p>
        <p><?php  if(isset($foot['ccm'])) echo $foot['ccm']; ?></p>
    </div>
    <div class="icon pull-right"><a href="<?php  if(isset($foot['ccmurl'])) echo $foot['ccmurl']; ?>" target="_blank"><img src="/static/images/wenwangwen.png" alt=""></a></div>
</div>