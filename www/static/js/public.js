/**
 * Created by Administrator on 2017/1/10.
 */
//鍒濆璁＄畻
// $('.nav').height($('.nav img').width()*56/157);
// $('.nav .display-select').css('top',$('.nav img').width()*56/157+14);
$('.banner .inner img').width($('body').width());
// 杞挱鍥�
//var banner = function(ele,time){
//    var winW = $('body').width();
//    var banner = $(ele);
//    var inner = banner.find(".inner");
//
//    var prepend = inner.children().eq(inner.children().length-1).clone();
//    var append = inner.children().eq(0).clone();
//    inner.prepend(prepend);
//    inner.append(append);
//    inner.children().each(function(){
//        $(this).find('img').css('width',winW)
//    });
//    var length = inner.children().length;
//    var ste = 1;
//    inner.css({'transform':'translateX(-'+(ste*winW)+'px)','transition-duration':'0s'});
//    var startLoot = function (){
//        ste++;
//        inner.css({'transform':'translateX(-'+(ste*winW)+'px)','transition-duration':'0.3s'});
//        if(ste == length-1){
//            window.setTimeout(function(){
//                inner.css({'transform':'translateX(-'+(winW)+'px)','transition-duration':'0s'});
//                ste = 1
//            },300)
//        }
//
//    };
//    var timerInterval = window.setInterval(startLoot,time);
//    //鑷姩杞挱
//    //鎸囧皷婊戝姩
//    var start = function(_this,event){
//        //alert(1);
//        var touch = event;
//        _this.startX = touch.pageX;
//        inner[0].addEventListener('touchmove',function(e){move(this,e)},false);
//        window.clearInterval(timerInterval)
//    };
//
//    var move = function(_this,event){
//
//        var touch = event;
//        var nowX = touch.pageX;
//        var num = nowX-_this.startX;
//        inner.css({'transform':'translate3d('+(-ste*winW+num)+'px, 0px, 0px)','transition-duration':'0s'});
//
//        inner.unbind('touchend')[0].addEventListener('touchend',function(e){end(_this,e,num)},false)
//
//    };
//    var end = function(_this,event,num){
//
//        if(num>0 && Math.abs(num)>100){
//            ste--
//
//        }else if(num<0 && Math.abs(num)>100){
//            ste++
//        }else{
//            inner.css({'transform':'translate3d('+(-ste*winW)+'px, 0px, 0px)','transition-duration':'0.3s'})
//        }
//
//        if(ste == length-1){
//            inner.css({'transform':'translateX(-'+(ste*winW)+'px)','transition-duration':'0.3s'});
//            window.setTimeout(function(){
//                inner.css({'transform':'translateX(-'+(winW)+'px)','transition-duration':'0s'});
//                ste = 1
//            },300)
//        }else if(ste == 0){
//            inner.css({'transform':'translateX(-'+(ste*winW)+'px)','transition-duration':'0.3s'});
//            window.setTimeout(function(){
//                inner.css({'transform':'translateX(-'+((length-2)*winW)+'px)','transition-duration':'0s'});
//                ste = length-2
//            },300)
//        }else{
//            inner.css({'transform':'translate3d('+(-ste*winW)+'px, 0px, 0px)','transition-duration':'0.3s'})
//        }
//        timerInterval = window.setInterval(startLoot,time);
//    };
//    inner[0].addEventListener('touchstart',function(e){e.preventDefault();alert(e);start(this,e)},false)
//
//};




/*
 * 类似vw的字体放大缩小
 * def: 设计稿的宽度( 根据设计稿的宽度来设置字体大小 用rem布局)
 * 原理根据设计稿的宽度与页面当前的宽度比例来放大缩小根元素的字体大小
 * */
var setFontSize = function(def){// def 设置一个初始的宽度；
    var font_html = document.documentElement;
    var nowWidth;
    if(window.getComputedStyle){
        nowWidth = window.getComputedStyle(document.body)['width'] || window.getComputedStyle(document.documentElement)['width'];
    }else{
        nowWidth = document.body.currentStyle('width') || document.documentElement.currentStyle('width');
    }
    // 去除单位（px）
    nowWidth = nowWidth.replace(/px/g,"");
    // 获取放大或缩小的比例
    var reta = nowWidth/def*100;
    font_html.style.fontSize = reta+"%"
};
// 用法 例：
setFontSize(600);
window.onresize  = function(){
    setFontSize(600);
    $('.banner .inner img').width($('body').width());
};


var run = function(ele,eleP){ //ele 鍐呭鍏冪礌  eleP 闇€瑕佹粴鍔ㄧ殑鍏冪礌

    var elp = $(eleP);
    var el = $(ele);
    var elW = el.width()+21;
    var width = elp.width();
    var clone = null;
    var i = 0;
    if(elW / width >= 1){// 澶т簬瀹瑰櫒瀹藉害
        clone = $(ele+':first').clone();
        elp.append(clone)
    }else if(elW / width < 1){
        if(width % elW == 0){
            for(i = 0;i<width/elW;i++){
                clone = $(ele+':first').clone();
                elp.append(clone)
            }
        }else{
            for(i = 0;i<parseInt(width / elW)+1;i++){
                clone = $(ele+':first').clone();
                elp.append(clone);
            }
        }
    }
    var ste = 0;
    window.setInterval(function(){
        ste++;
        if(ste == elW){
            ste = 0;
            elp.css({'transform':'translateX(-'+(ste)+'px)','transition-duration':'0s'});
        }else{
            elp.css({'transform':'translateX(-'+(ste)+'px)','transition-duration':'0.1s'});
        }
    },25)
};

$(function(){
    //瀵艰埅鏍�
    var nav = $('.nav'),
        display_select = $('.display-select'),
        more = nav.find('.more i');
    display_select.click(function(e){
        e.stopPropagation()
    });
    more.click(function(e){
        e.stopPropagation();
        display_select.toggle()
    });
    $('body').bind('click',function(){
        display_select.hide()
    });
    ////鏈夎疆鎾浘 灏卞惎鍔ㄨ疆鎾浘
    if($('.banner').length != 0){
        TouchSlide({
            slideCell:'#banner',
            mainCell:"#banner .inner",
            effect:"leftLoop",
            // prevCell:".banner-prevCell",
            // nextCell:".banner-nextCell",
            titCell : '.banner-item-outer span',
            autoPlay:true,
            interTime:4000
        })
    }
    // running 璺戦┈鐏�
    if($('.notice').length != 0){
        run('.notice .light','.notice .tran')
    }

    $('.picture li img.flowing').each(function () {
        $(this).css({width:$(this).width()+'px'})
    });


    $('.picture li').each(function () {
        $(this).click(function () {
            var _this = this;
            $(_this).find('.flowing').addClass('con-img');
            $('.pic-opacity').fadeIn(100)
            $('.pic-opacity').unbind().click(function () {
                $(this).fadeOut(100);
                $(_this).find('.flowing').removeClass('con-img');
            })
        })
    })




});
