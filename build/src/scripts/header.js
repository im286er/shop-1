/**
 * Created by NHY on 2016/11/19.
 */
function Header(title,system){
    this.title=title;
    this.dom=$("<tr></tr>");
    this.open=true;
    this.system=system||false;
    this.init();
}
Header.prototype={
    constructor:Header,
    init:function(){
        this.bindDom();
        this.bindEvent();
    },
    bindDom:function(){
        var ele="";
        if(!this.system){
            ele='<td><span class="column-title">'+this.title+'</span></td><td><span class="column-item1 column-select"></span></td><td><span class="column-edit-active column-item2"></span><span class="column-up column-item3"></span><span class="column-down column-item4"></span><span class="column-del column-item5"></span></td>';
        }else{
            ele='<td><span class="column-title column-system">'+this.title+'</span></td><td><span class="column-item1 column-select"></span></td><td><span class="column-edit-active column-item2"></span><span class="column-up column-item3"></span><span class="column-down column-item4"></span></td>';
        }
        var d=this.dom.append(ele);
        $(".right-content2-content").find("tbody").append(d);
    },
    bindEvent:function(){
        var father=$(".right-content2-content").find("tbody");
        //栏目开启
        var that=this;
        this.dom.on("click",".column-item1",function(){
            if(that.open){
                $(this).removeClass("column-select").addClass("column-select-active");
                $(this).parent().siblings().find(".column-item3").remove();
                $(this).parent().siblings().find(".column-item4").remove();
                that.open=false;
            }else{
                if(!that.open) {
                    $(this).removeClass("column-select-active").addClass("column-select");
                    $(this).parent().siblings().find(".column-item2").after('<span class="column-up column-item3"></span><span class="column-down column-item4"></span>');
                }
                that.open=true;
            }
        });
        //向上
        this.dom.on("click",".column-item3",function(){
            $(this).siblings().removeClass("column-down-active");
            var par=$(this).parents("tr").clone(true);
            if($(this).parents("tr").prevAll().length) {
                if($(this).parents("tr").nextAll().length==1) {
                    par.find(".column-item3").addClass("column-up-active");
                }
                $(this).parents("tr").prev().before(par);
                $(this).parents("tr").remove();
            }
        });
        //向下
        this.dom.on("click",".column-item4",function(){
            $(this).siblings().removeClass("column-up-active");
            var par=$(this).parents("tr").clone(true);
            if($(this).parents("tr").nextAll().length){
                if($(this).parents("tr").nextAll().length==1) {
                    par.find(".column-item4").addClass("column-down-active");
                }
                    $(this).parents("tr").next().after(par);
                    $(this).parents("tr").remove();
            }
        });
        //删除
        this.dom.on("click",".column-item5",function(){
            $(this).parents("tr").remove();
        });
    }
};
