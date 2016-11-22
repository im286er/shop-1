/**
 * 鑾峰彇缃戦〉鐨勫搴﹀拰楂樺害
 * @param  {string} get    闇�鐨勫锛坵锛夋垨楂橈紙h锛�
 * @return 濡傛灉鍙傛暟get瀛樺湪锛屽垯杩斿洖鐩稿簲瀹芥垨楂橈紝濡傛灉get娌℃湁鍐欏垯杩斿洖鏁扮粍
 */
function getBodySize(get) {
    var bodySize = [];
    bodySize['w']=($(document.body).width()>$(window).width())? $(document.body).width():$(window).width();
    bodySize['h']=($(document.body).height()>$(window).height())? $(document.body).height():$(window).height();
    return get?bodySize[get]:bodySize;
}

/**
 * 閫氱敤AJAX鎻愪氦
 * @param  {string} url    琛ㄥ崟鎻愪氦鍦板潃
 * @param  {string} formObj    寰呮彁浜ょ殑琛ㄥ崟瀵硅薄鎴朓D
 */
function commonAjaxSubmit(url,formObj){
    if(!formObj||formObj==''){
        var formObj="form";
    }
    if(!url||url==''){
        var url=document.URL;
    }
	
    $(formObj).ajaxSubmit({
        url:url,
        type:"POST",
        success:function(data, st) {
            //                var data = eval("(" + data + ")");
            if(data.status==1){
                popup.success(data.info);
                setTimeout(function(){
                    popup.close("asyncbox_success");
                },2000);
            }else{
                popup.error(data.info);
                setTimeout(function(){
                    popup.close("asyncbox_error");
                },2000);
            }
            if(data.url&&data.url!=''){
                setTimeout(function(){
                    top.window.location.href=data.url;
                },2000);
            }
            if(data.url==''){
                setTimeout(function(){
                    top.window.location.reload();
                },1000);
            }
        }
    });
    return false;
}
/**
 * 妫�祴瀛楃涓叉槸鍚︽槸鐢靛瓙閭欢鍦板潃鏍煎紡
 * @param  {string} value    寰呮娴嬪瓧绗︿覆
 */
function isEmail(value){
    var Reg =/^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
    return Reg.test(value);
}

/**
 * 鍜孭HP涓�牱鐨勬椂闂存埑鏍煎紡鍖栧嚱鏁�
 * @param  {string} format    鏍煎紡
 * @param  {int}    timestamp 瑕佹牸寮忓寲鐨勬椂闂�榛樿涓哄綋鍓嶆椂闂�
 * @return {string}           鏍煎紡鍖栫殑鏃堕棿瀛楃涓�
 */
function date(format, timestamp){
    var a, jsdate=((timestamp) ? new Date(timestamp*1000) : new Date());
    var pad = function(n, c){
        if((n = n + "").length < c){
            return new Array(++c - n.length).join("0") + n;
        } else {
            return n;
        }
    };
    var txt_weekdays = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
    var txt_ordin = {
        1:"st",
        2:"nd",
        3:"rd",
        21:"st",
        22:"nd",
        23:"rd",
        31:"st"
    };
    var txt_months = ["", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    var f = {
        // Day
        d: function(){
            return pad(f.j(), 2)
        },
        D: function(){
            return f.l().substr(0,3)
        },
        j: function(){
            return jsdate.getDate()
        },
        l: function(){
            return txt_weekdays[f.w()]
        },
        N: function(){
            return f.w() + 1
        },
        S: function(){
            return txt_ordin[f.j()] ? txt_ordin[f.j()] : 'th'
        },
        w: function(){
            return jsdate.getDay()
        },
        z: function(){
            return (jsdate - new Date(jsdate.getFullYear() + "/1/1")) / 864e5 >> 0
        },

        // Week
        W: function(){
            var a = f.z(), b = 364 + f.L() - a;
            var nd2, nd = (new Date(jsdate.getFullYear() + "/1/1").getDay() || 7) - 1;
            if(b <= 2 && ((jsdate.getDay() || 7) - 1) <= 2 - b){
                return 1;
            } else{
                if(a <= 2 && nd >= 4 && a >= (6 - nd)){
                    nd2 = new Date(jsdate.getFullYear() - 1 + "/12/31");
                    return date("W", Math.round(nd2.getTime()/1000));
                } else{
                    return (1 + (nd <= 3 ? ((a + nd) / 7) : (a - (7 - nd)) / 7) >> 0);
                }
            }
        },

        // Month
        F: function(){
            return txt_months[f.n()]
        },
        m: function(){
            return pad(f.n(), 2)
        },
        M: function(){
            return f.F().substr(0,3)
        },
        n: function(){
            return jsdate.getMonth() + 1
        },
        t: function(){
            var n;
            if( (n = jsdate.getMonth() + 1) == 2 ){
                return 28 + f.L();
            } else{
                if( n & 1 && n < 8 || !(n & 1) && n > 7 ){
                    return 31;
                } else{
                    return 30;
                }
            }
        },

        // Year
        L: function(){
            var y = f.Y();
            return (!(y & 3) && (y % 1e2 || !(y % 4e2))) ? 1 : 0
        },
        //o not supported yet
        Y: function(){
            return jsdate.getFullYear()
        },
        y: function(){
            return (jsdate.getFullYear() + "").slice(2)
        },

        // Time
        a: function(){
            return jsdate.getHours() > 11 ? "pm" : "am"
        },
        A: function(){
            return f.a().toUpperCase()
        },
        B: function(){
            // peter paul koch:
            var off = (jsdate.getTimezoneOffset() + 60)*60;
            var theSeconds = (jsdate.getHours() * 3600) + (jsdate.getMinutes() * 60) + jsdate.getSeconds() + off;
            var beat = Math.floor(theSeconds/86.4);
            if (beat > 1000) beat -= 1000;
            if (beat < 0) beat += 1000;
            if ((String(beat)).length == 1) beat = "00"+beat;
            if ((String(beat)).length == 2) beat = "0"+beat;
            return beat;
        },
        g: function(){
            return jsdate.getHours() % 12 || 12
        },
        G: function(){
            return jsdate.getHours()
        },
        h: function(){
            return pad(f.g(), 2)
        },
        H: function(){
            return pad(jsdate.getHours(), 2)
        },
        i: function(){
            return pad(jsdate.getMinutes(), 2)
        },
        s: function(){
            return pad(jsdate.getSeconds(), 2)
        },
        //u not supported yet

        // Timezone
        //e not supported yet
        //I not supported yet
        O: function(){
            var t = pad(Math.abs(jsdate.getTimezoneOffset()/60*100), 4);
            if (jsdate.getTimezoneOffset() > 0) t = "-" + t; else t = "+" + t;
            return t;
        },
        P: function(){
            var O = f.O();
            return (O.substr(0, 3) + ":" + O.substr(3, 2))
        },
        //T not supported yet
        //Z not supported yet

        // Full Date/Time
        c: function(){
            return f.Y() + "-" + f.m() + "-" + f.d() + "T" + f.h() + ":" + f.i() + ":" + f.s() + f.P()
        },
        //r not supported yet
        U: function(){
            return Math.round(jsdate.getTime()/1000)
        }
    };

    return format.replace(/[\\]?([a-zA-Z])/g, function(t, s){
        if( t!=s ){
            // escaped
            ret = s;
        } else if( f[s] ){
            // a date function exists
            ret = f[s]();
        } else{
            // nothing special
            ret = s;
        }
        return ret;
    });
}


function clickCheckbox(){
    $(".chooseAll").click(function(){
        var status=$(this).prop('checked');
        $("tbody input[type='checkbox']").prop("checked",status);
        $(".chooseAll").prop("checked",status);
        $(".unsetAll").prop("checked",false);
    });
    $(".unsetAll").click(function(){
        var status=$(this).prop('checked');
        $("tbody input[type='checkbox']").each(function(){
            $(this).prop("checked",! $(this).prop("checked"));
        });
        $(".unsetAll").prop("checked",status);
        $(".chooseAll").prop("checked",false);
    });
}



function AddNew()//添加按钮
{
	ds= document.getElementById('url').value;
   // alert(ds);
	window.location.href=ds;
}

function doExcel(){//导出数据按钮
    de=document.getElementById('doexcel').value;
  // alert(de);
  window.location.href=de;
}



