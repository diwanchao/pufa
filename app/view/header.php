<div class="nav">
    <a href="<?php echo !isset($_GET['cfg']) ? '/' : $this->createUrl('index/index'); ?>"> <img src="/static/images/logo.png" alt=""></a>
    <span class="more"><i class="iconfont">&#xe61e;</i></span>
</div>
<div class="select">
    <div class="display-select">
        <ul>
            <li class="first"><a href="<?php echo $this->createUrl('active/index');?>"><i class="iconfont">&#xe607;</i><span>活动专区</span></a></li>
            <li><a href="<?php echo $this->createUrl('notice/index');?>"><i class="iconfont">&#xe63d;</i><span>公告信息</span></a></li>
        </ul>
    </div>
</div>