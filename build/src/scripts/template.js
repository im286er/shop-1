/**
 * Created by NHY on 2016/11/19.
 */


//头部自适应高度
function headHeight(){
    var ele=$(".template-head");
    var h=ele.outerHeight();
    ele.css("bottom",-h);
}
$(function(){
    headHeight();
    $("#show").click(function(){
        if($(this).html()==="≡"){
            $(this).html("×");
        }else{
            $(this).html("≡");
        }
        $(".template-head").toggle();
    });
});