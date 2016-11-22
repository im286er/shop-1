/*
* @Author: iceStone
* @Date:   2016-01-26 23:10:20
* @Last Modified by:   iceStone
* @Last Modified time: 2016-01-26 23:12:46
*/

//'use strict';
$(function(){
    //右边切换模块图片加载
    $.ajax({
        type: "POST",
        url: "json/module.json",
        dataType:"json",
        success: function(msg){
            var str='';
            for(var i in msg){
                str+='<li><img src="'+msg[i]+'" alt=""></li>';
            }
            $(".content-right-content").html(str);
        }
    });
    //左边头部切换
    $(".content-left-top").find("div").on("click",function(){
        $(this).addClass("content-left-top-active").siblings().removeClass("content-left-top-active");
    });
    //左边导航栏切换
    $(".content-left-nav").find("li").on("click",function(){
        $(this).addClass("content-left-nav-active").siblings().removeClass("content-left-nav-active");
    });
    //右边头部切换
    $(".content-right-top").find("div").on("click",function(){
        $(this).addClass("content-right-top-active").siblings().removeClass("content-right-top-active");
    });
    //右边导航栏切换
    $(".content-right-nav").find("li").on("click",function(){
        $(this).addClass("content-right-nav-active").siblings().removeClass("content-right-nav-active");
    });
    //右边主题切换
    $(".content-right-tab").find("span").on("click",function(){
        $(this).addClass("content-right-tab-active").siblings().removeClass("content-right-tab-active");
    });
    //主题具体切换
    $(".content-right-middle").find("ul").find("li").on("click",function(){
        $(this).addClass("list-active").siblings().removeClass("list-active");
    });
    //显示二维码
    $("#barcode").hover(function(){
        $(this).after('<div class="url-barcode"><img src="'+"images/bigbarcode.png"+'" alt=""><span></span></div>');
    },function(){
        $(this).next().remove();
    });
    //栏目切换
    $(".right-content2-change1").on("click",function(){
        $(this).addClass("right-content2-top-active").siblings().removeClass("right-content2-top-active");
        $(".right-content2-content").show();
        $(".right-content2-content1").hide();
    });
    $(".right-content2-change2").on("click",function(){
        $(this).addClass("right-content2-top-active").siblings().removeClass("right-content2-top-active");
        $(".right-content2-content1").show();
        $(".right-content2-content").hide();
    });
    //右边切换
    $(".right-fenge").on("click",function(){
        $(".right-content").hide();
        $("#right-content1").show();
    });
    $(".right-lanmu").on("click",function(){
        $(".right-content").hide();
        $("#right-content2").show();
    });
    //新增栏目
    $("#add-column").click(function(){
        new Header("新闻");
        new Header("主页");
        new Header("公司详情",true);
    });
});
