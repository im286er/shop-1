

	function slip(i){
		var actions;
		contents=eval("content"+i);
		titles=eval("title"+i);
		if(i!='0'){
			if (contents.style.display == "none")
			{
				contents.style.display = "";
				titles.background="images/on.gif";
			}else{
				contents.style.display = "none";
				titles.background="images/off.gif";
			}
		}else{
			if (contents.style.display == "none")
			{
				contents.style.display = "";
				titles.background="images/on_t.gif";
			}else{
				contents.style.display = "none";
				titles.background="images/off_t.gif";
			}
		}
	}

	function OpenWindow(Url,Width,Height){
		var i=window.open(Url,'','width='+Width+',height='+Height+',top='+(screen.height-Height)/2+',left='+(screen.width-Width)/2+',toolbar=no,menubar=no,resizable=yes,scrollbars=yes,location=no,status=no');
		if (i==null){
			alert('�����ĵ������ڱ����Ρ���ر�����������������ι��ܣ�����һ�Ρ�');
		}

	}

	function ShowMe(id){
		for(i=0;i<=5;i++){
			if (i!=id){
				eval("Rate"+i+".style.display='none'");
				eval("T"+i+".bgColor='#7788B6'");
			}
		}
		eval("Rate"+id+".style.display=''");
		eval("T"+id+".bgColor='#FFFFFF'");
	}

// =========================================================����ѡ��=====================================================================
var months = new Array("һ��", "����", "����", "����", "����", "����", "����", "����", "����", "ʮ��", "ʮһ��", "ʮ����"); 
var daysInMonth = new Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31); 
var days = new Array("��","һ", "��", "��", "��", "��", "��"); 
var today; 

document.writeln("<div id='Calendar' style='position:absolute; z-index:1; visibility: hidden; filter:\"progid:DXImageTransform.Microsoft.Shadow(direction=135,color=#999999,strength=3)\"'></div>");

function getDays(month, year)
{ 
//�������δ������жϵ�ǰ�Ƿ�������� 
if (1 == month) 
return ((0 == year % 4) && (0 != (year % 100))) || (0 == year % 400) ? 29 : 28; 
else 
return daysInMonth[month]; 
} 

function getToday() 
{ 
//�õ��������,��,�� 
this.now = new Date(); 
this.year = this.now.getFullYear(); 
this.month = this.now.getMonth(); 
this.day = this.now.getDate(); 
}

function getStringDay(str) 
{ 
//�õ���������,��,��
var str=str.split("-")

this.now = new Date(parseFloat(str[0]),parseFloat(str[1])-1,parseFloat(str[2])); 
this.year = this.now.getFullYear(); 
this.month = this.now.getMonth(); 
this.day = this.now.getDate(); 
}

function newCalendar() { 
var parseYear = parseInt(document.all.Year.options[document.all.Year.selectedIndex].value); 
var newCal = new Date(parseYear, document.all.Month.selectedIndex, 1); 
var day = -1; 
var startDay = newCal.getDay(); 
var daily = 0; 

if ((today.year == newCal.getFullYear()) &&(today.month == newCal.getMonth())) 
day = today.day; 

var tableCal = document.all.calendar; 
var intDaysInMonth =getDays(newCal.getMonth(), newCal.getFullYear());

for (var intWeek = 1; intWeek < tableCal.rows.length;intWeek++) 
for (var intDay = 0;intDay < tableCal.rows[intWeek].cells.length;intDay++) 
{ 
var cell = tableCal.rows[intWeek].cells[intDay]; 
if ((intDay == startDay) && (0 == daily)) 
daily = 1; 

if(day==daily) //���죬���ý����Class 
{
cell.style.background='#6699CC';
cell.style.color='#FFFFFF';
//cell.style.fontWeight='bold';
}
else if(intDay==6) //���� 
cell.style.color='green'; 
else if (intDay==0) //���� 
cell.style.color='red';

if ((daily > 0) && (daily <= intDaysInMonth)) 
{ 
cell.innerText = daily; 
daily++; 
} 
else 
cell.innerText = ""; 
} 
} 

function GetDate(InputBox)
{ 
var sDate; 
//��δ��봦������������ 
if (event.srcElement.tagName == "TD") 
if (event.srcElement.innerText != "") 
{ 
sDate = document.all.Year.value + "-" + document.all.Month.value + "-" + event.srcElement.innerText;
eval("document.all."+InputBox).value=sDate;
HiddenCalendar();
} 
} 

function HiddenCalendar()
{
//�ر�ѡ�񴰿�
document.all.Calendar.style.visibility='hidden';
}

function ShowCalendar(InputBox)
{
var x,y,intLoop,intWeeks,intDays;
var DivContent;
var year,month,day;
var o=eval("document.all."+InputBox);
var thisyear; //�����Ľ������

thisyear=new getToday();
thisyear=thisyear.year;

today = o.value;
if(isDate(today))
today = new getStringDay(today);
else
today = new getToday(); 

//��ʾ��λ��
x=o.offsetLeft;
y=o.offsetTop;
while(o=o.offsetParent)
{
x+=o.offsetLeft;
y+=o.offsetTop;
}
document.all.Calendar.style.left=x+2;
document.all.Calendar.style.top=y+20;
document.all.Calendar.style.visibility="visible";

//���濪ʼ����������(border-color:#9DBAF7)
DivContent="<table border='0' cellspacing='0' style='border:1px solid #0066FF; background-color:#EDF2FC'>";
DivContent+="<tr>";
DivContent+="<td style='border-bottom:1px solid #0066FF; background-color:#C7D8FA'>";

//��
DivContent+="<select name='Year' id='Year' onChange='newCalendar()' style='font-family:Verdana; font-size:12px' class='Sel'>";
for (intLoop = 1900; intLoop < 2200; intLoop++) 
DivContent+="<option value= " + intLoop + " " + (today.year == intLoop ? "Selected" : "") + ">" + intLoop + "</option>"; 
DivContent+="</select>";

//��
DivContent+="<select name='Month' id='Month' onChange='newCalendar()' style='font-family:Verdana; font-size:12px' class='Sel'>";
for (intLoop = 0; intLoop < months.length; intLoop++) 
DivContent+="<option value= " + (intLoop + 1) + " " + (today.month == intLoop ? "Selected" : "") + ">" + months[intLoop] + "</option>"; 
DivContent+="</select>";

DivContent+="</td>";

DivContent+="<td style='border-bottom:1px solid #0066FF; background-color:#C7D8FA; font-weight:bold; font-family:Wingdings 2,Wingdings,Webdings; font-size:16px; padding-top:2px; color:#4477FF; cursor:hand' align='center' title='�ر�' onClick='javascript:HiddenCalendar()'>S</td>";
DivContent+="</tr>";

DivContent+="<tr><td align='center' colspan='2'>";
DivContent+="<table id='calendar' border='0' width='100%'>";

//����
DivContent+="<tr>";
for (intLoop = 0; intLoop < days.length; intLoop++) 
DivContent+="<td align='center' style='font-size:12px'>" + days[intLoop] + "</td>"; 
DivContent+="</tr>";

//��
for (intWeeks = 0; intWeeks < 6; intWeeks++)
{ 
DivContent+="<tr>"; 
for (intDays = 0; intDays < days.length; intDays++) 
DivContent+="<td onClick='GetDate(\"" + InputBox + "\")' style='cursor:hand; border-right:1px solid #BBBBBB; border-bottom:1px solid #BBBBBB; color:#215DC6; font-family:Verdana; font-size:12px' align='center'></td>"; 
DivContent+="</tr>"; 
} 
DivContent+="</table></td></tr></table>";

document.all.Calendar.innerHTML=DivContent;
newCalendar();
}

function isDate(dateStr)
{ 
var datePat = /^(\d{4})(\-)(\d{1,2})(\-)(\d{1,2})$/;
var matchArray = dateStr.match(datePat);
if (matchArray == null) return false; 
var month = matchArray[3];
var day = matchArray[5]; 
var year = matchArray[1]; 
if (month < 1 || month > 12) return false; 
if (day < 1 || day > 31) return false; 
if ((month==4 || month==6 || month==9 || month==11) && day==31) return false; 
if (month == 2)
{
var isleap = (year % 4 == 0 && (year % 100 != 0 || year % 400 == 0)); 
if (day > 29 || (day==29 && !isleap)) return false; 
} 
return true;
}
//=============================================================����ѡ�����===================================================

	//�������õȴ����ߴ�
	function changePadSize(){
		try{
			Msg.style.width=document.body.scrollWidth>document.body.clientWidth?document.body.scrollWidth:document.body.clientWidth;
			Msg.style.height=document.body.scrollHeight>document.body.clientHeight?document.body.scrollHeight:document.body.clientHeight;
			Msg1.style.width=Msg.style.width;
			Msg1.style.height=Msg.style.height;
			Msg2.style.left=(document.body.clientWidth-200)/2;
			Msg2.style.top=(document.body.clientHeight/2-50)/2;
		}catch(e){
		}finally{
		}
	}

	//��ʾ��Ϣ���
	function ShowMsgPad(strMsg){
		//try{
			Msg.style.display="";
			Msg1.style.display="";
			Msg2.style.display="";
			MsgContent.innerHTML='<b><font color="#800000">'+strMsg+'</font></b>';
		//}catch(e){
		//}finally{
		//}
	}
	//������Ϣ���
	function HideMsgPad(strMsg){
		try{
			Msg.style.display="none";
			Msg1.style.display="none";
			Msg2.style.display="none";
		}catch(e){
		}finally{
		}
	}


	//��ʾ�ȴ����
	function ShowPad(){
		try{
			Msg.style.display="";
			Msg1.style.display="";
		}catch(e){
		}finally{
		}
	}
	//���صȴ����
	function HidePad(){
		try{
			Msg.style.display="none";
			Msg1.style.display="none";
		}catch(e){
		}finally{
		}
	}


//============================================================================ʱ��ѡ�� ��ʼ======================================================


function atCalendarControl(){
  var calendar=this;
  this.calendarPad=null;
  this.prevMonth=null;
  this.nextMonth=null;
  this.prevYear=null;
  this.nextYear=null;
  this.goToday=null;
  this.calendarClose=null;
  this.calendarAbout=null;
  this.head=null;
  this.body=null;
  this.today=[];
  this.currentDate=[];
  this.sltDate;
  this.target;
  this.source;

  /************** ���������װ弰��Ӱ *********************/
  this.addCalendarPad=function(){
   document.write("<div id='divCalendarpad' style='position:absolute;top:100;left:0;width:255;height:187;display:none;'>");
   document.write("<iframe frameborder=0 height=189 width=250></iframe>");
   document.write("<div style='position:absolute;top:2;left:2;width:250;height:187;background-color:#336699;'></div>");
   document.write("</div>");
   calendar.calendarPad=document.all.divCalendarpad;
  }
  /************** ����������� *********************/
  this.addCalendarBoard=function(){
   var BOARD=this;
   var divBoard=document.createElement("div");
   calendar.calendarPad.insertAdjacentElement("beforeEnd",divBoard);
   divBoard.style.cssText="position:absolute;top:0;left:0;width:250;height:187;border:0 outset;background-color:buttonface;";

   var tbBoard=document.createElement("table");
   divBoard.insertAdjacentElement("beforeEnd",tbBoard);
   tbBoard.style.cssText="position:absolute;top:2;left:2;width:248;height:10;font-size:9pt;";
   tbBoard.cellPadding=0;
   tbBoard.cellSpacing=1;

  /************** ���ø����ܰ�ť�Ĺ��� *********************/
   /*********** Calendar About Button ***************/
   trRow = tbBoard.insertRow(0);
   calendar.calendarAbout=calendar.insertTbCell(trRow,0,"-","center");
   calendar.calendarAbout.title="���� ��ݼ�:H";
   calendar.calendarAbout.onclick=function(){calendar.about();}
   /*********** Calendar Head ***************/
   tbCell=trRow.insertCell(1);
   tbCell.colSpan=5;
   tbCell.bgColor="#99CCFF";
   tbCell.align="center";
   tbCell.style.cssText = "cursor:default";
   calendar.head=tbCell;
   /*********** Calendar Close Button ***************/
   tbCell=trRow.insertCell(2);
   calendar.calendarClose = calendar.insertTbCell(trRow,2,"x","center");
   calendar.calendarClose.title="�ر� ��ݼ�:ESC��X";
   calendar.calendarClose.onclick=function(){calendar.hide();}

   /*********** Calendar PrevYear Button ***************/
   trRow = tbBoard.insertRow(1);
   calendar.prevYear = calendar.insertTbCell(trRow,0,"<<","center");
   calendar.prevYear.title="��һ�� ��ݼ�:��";
   calendar.prevYear.onmousedown=function(){
    calendar.currentDate[0]--;
    calendar.show(calendar.target,calendar.returnTime,calendar.currentDate[0]+"-"+calendar.formatTime(calendar.currentDate[1])+"-"+calendar.formatTime(calendar.currentDate[2]),calendar.source);
   }
   /*********** Calendar PrevMonth Button ***************/
   calendar.prevMonth = calendar.insertTbCell(trRow,1,"<","center");
   calendar.prevMonth.title="��һ�� ��ݼ�:��";
   calendar.prevMonth.onmousedown=function(){
    calendar.currentDate[1]--;
    if(calendar.currentDate[1]==0){
     calendar.currentDate[1]=12;
     calendar.currentDate[0]--;
    }
    calendar.show(calendar.target,calendar.returnTime,calendar.currentDate[0]+"-"+calendar.formatTime(calendar.currentDate[1])+"-"+calendar.formatTime(calendar.currentDate[2]),calendar.source);
   }
   /*********** Calendar Today Button ***************/
   calendar.goToday = calendar.insertTbCell(trRow,2,"����","center",3);
   calendar.goToday.title="ѡ����� ��ݼ�:T";
   calendar.goToday.onclick=function(){
	 if(calendar.returnTime)  
	    calendar.sltDate=calendar.today[0]+"-"+calendar.formatTime(calendar.today[1])+"-"+calendar.formatTime(calendar.today[2])+" "+calendar.formatTime(calendar.today[3])+":"+calendar.formatTime(calendar.today[4])
	 else
	    calendar.sltDate=calendar.today[0]+"-"+calendar.formatTime(calendar.today[1])+"-"+calendar.formatTime(calendar.today[2]);
    calendar.target.value=calendar.sltDate;
    calendar.hide();
    //calendar.show(calendar.target,calendar.today[0]+"-"+calendar.today[1]+"-"+calendar.today[2],calendar.source);
   }
   /*********** Calendar NextMonth Button ***************/
   calendar.nextMonth = calendar.insertTbCell(trRow,3,">","center");
   calendar.nextMonth.title="��һ�� ��ݼ�:��";
   calendar.nextMonth.onmousedown=function(){
    calendar.currentDate[1]++;
    if(calendar.currentDate[1]==13){
     calendar.currentDate[1]=1;
     calendar.currentDate[0]++;
    }
    calendar.show(calendar.target,calendar.returnTime,calendar.currentDate[0]+"-"+calendar.formatTime(calendar.currentDate[1])+"-"+calendar.formatTime(calendar.currentDate[2]),calendar.source);
   }
   /*********** Calendar NextYear Button ***************/
   calendar.nextYear = calendar.insertTbCell(trRow,4,">>","center");
   calendar.nextYear.title="��һ�� ��ݼ�:��";
   calendar.nextYear.onmousedown=function(){
    calendar.currentDate[0]++;
    calendar.show(calendar.target,calendar.returnTime,calendar.currentDate[0]+"-"+calendar.formatTime(calendar.currentDate[1])+"-"+calendar.formatTime(calendar.currentDate[2]),calendar.source);

   }

   trRow = tbBoard.insertRow(2);
   var cnDateName = new Array("��","һ","��","��","��","��","��");
   for (var i = 0; i < 7; i++) {
    tbCell=trRow.insertCell(i)
    tbCell.innerText=cnDateName[i];
    tbCell.align="center";
    tbCell.width=35;
    tbCell.style.cssText="cursor:default;border:1 solid #99CCCC;background-color:#99CCCC;";
   }

   /*********** Calendar Body ***************/
   trRow = tbBoard.insertRow(3);
   tbCell=trRow.insertCell(0);
   tbCell.colSpan=7;
   tbCell.height=97;
   tbCell.vAlign="top";
   tbCell.bgColor="#F0F0F0";
   
   var tbBody=document.createElement("table");
   tbCell.insertAdjacentElement("beforeEnd",tbBody);
   tbBody.style.cssText="position:relative;top:0;left:0;width:245;height:103;font-size:9pt;"
   tbBody.cellPadding=0;
   tbBody.cellSpacing=1;
   calendar.body=tbBody;
	
   /*********** Time Body ***************/
   trRow = tbBoard.insertRow(4);
   tbCell=trRow.insertCell(0);
   calendar.prevHours = calendar.insertTbCell(trRow,0,"-","center");
   calendar.prevHours.title="Сʱ���� ��ݼ�:Home";
   calendar.prevHours.onmousedown=function(){
		calendar.currentDate[3]--;
		if(calendar.currentDate[3]==-1) calendar.currentDate[3]=23;
		calendar.bottom.innerText=calendar.formatTime(calendar.currentDate[3])+":"+calendar.formatTime(calendar.currentDate[4]);
	}
   tbCell=trRow.insertCell(1);
   calendar.nextHours = calendar.insertTbCell(trRow,1,"+","center");
   calendar.nextHours.title="Сʱ���� ��ݼ�:End";
   calendar.nextHours.onmousedown=function(){
		calendar.currentDate[3]++;
		if(calendar.currentDate[3]==24) calendar.currentDate[3]=0;
		calendar.bottom.innerText=calendar.formatTime(calendar.currentDate[3])+":"+calendar.formatTime(calendar.currentDate[4]);
	}
   tbCell=trRow.insertCell(2);
   tbCell.colSpan=3;
   tbCell.bgColor="#99CCFF";
   tbCell.align="center";
   tbCell.style.cssText = "cursor:default";
   calendar.bottom=tbCell;
   tbCell=trRow.insertCell(3);
   calendar.prevMinutes = calendar.insertTbCell(trRow,3,"-","center");
   calendar.prevMinutes.title="���ӵ��� ��ݼ�:PageUp";
   calendar.prevMinutes.onmousedown=function(){
		calendar.currentDate[4]--;
		if(calendar.currentDate[4]==-1) calendar.currentDate[4]=59;
		calendar.bottom.innerText=calendar.formatTime(calendar.currentDate[3])+":"+calendar.formatTime(calendar.currentDate[4]);
	}
   tbCell=trRow.insertCell(4);
   calendar.nextMinutes = calendar.insertTbCell(trRow,4,"+","center");
   calendar.nextMinutes.title="���ӵ��� ��ݼ�:PageDown";
   calendar.nextMinutes.onmousedown=function(){
		calendar.currentDate[4]++;
		if(calendar.currentDate[4]==60) calendar.currentDate[4]=0;
		calendar.bottom.innerText=calendar.formatTime(calendar.currentDate[3])+":"+calendar.formatTime(calendar.currentDate[4]);
	}

}
  
/************** ���빦�ܰ�ť������ʽ *********************/
  this.insertTbCell=function(trRow,cellIndex,TXT,trAlign,tbColSpan){
   var tbCell=trRow.insertCell(cellIndex);
   if(tbColSpan!=undefined) tbCell.colSpan=tbColSpan;

   var btnCell=document.createElement("button");
   tbCell.insertAdjacentElement("beforeEnd",btnCell);
   btnCell.value=TXT;
   btnCell.style.cssText="width:100%;border:1 outset;background-color:buttonface;";
   btnCell.onmouseover=function(){
   btnCell.style.cssText="width:100%;border:1 outset;background-color:#F0F0F0;";

   }
   btnCell.onmouseout=function(){
    btnCell.style.cssText="width:100%;border:1 outset;background-color:buttonface;";
   }
  // btnCell.onmousedown=function(){
  //  btnCell.style.cssText="width:100%;border:1 inset;background-color:#F0F0F0;";
  // }
   btnCell.onmouseup=function(){
    btnCell.style.cssText="width:100%;border:1 outset;background-color:#F0F0F0;";
   }
   btnCell.onclick=function(){
    btnCell.blur();
   }
   return btnCell;
  }
  
  this.setDefaultDate=function(){
   var dftDate=new Date();
   calendar.today[0]=dftDate.getYear();
   calendar.today[1]=dftDate.getMonth()+1;
   calendar.today[2]=dftDate.getDate();
   calendar.today[3]=dftDate.getHours();
   calendar.today[4]=dftDate.getMinutes();
  }

  /****************** Show Calendar *********************/
  this.show=function(targetObject,returnTime,defaultDate,sourceObject){
   if(targetObject==undefined) {
    alert("δ����Ŀ�����. \n����: ATCALENDAR.show(obj Ŀ�����,boolean �Ƿ񷵻�ʱ��,string Ĭ������,obj �������);\n\nĿ�����:�������ڷ���ֵ�Ķ���.\nĬ������:��ʽΪ\"yyyy-mm-dd\",ȱʡΪ��ǰ����.\n�������:���������󵯳�calendar,Ĭ��ΪĿ�����.\n");
    return false;
   }
   else calendar.target=targetObject;
   
   if(sourceObject==undefined) calendar.source=calendar.target;
   else calendar.source=sourceObject;

   if(returnTime) calendar.returnTime=true;
   else calendar.returnTime=false;

   var firstDay;
   var Cells=new Array();
   if((defaultDate==undefined) || (defaultDate=="")){
    var theDate=new Array();
    calendar.head.innerText = calendar.today[0]+"-"+calendar.formatTime(calendar.today[1])+"-"+calendar.formatTime(calendar.today[2]);
    calendar.bottom.innerText = calendar.formatTime(calendar.today[3])+":"+calendar.formatTime(calendar.today[4]);
	
    theDate[0]=calendar.today[0]; theDate[1]=calendar.today[1]; theDate[2]=calendar.today[2];
	theDate[3]=calendar.today[3]; theDate[4]=calendar.today[4];
   }
   else{
    var Datereg=/^\d{4}-\d{1,2}-\d{2}$/
    var DateTimereg=/^(\d{1,4})-(\d{1,2})-(\d{1,2}) (\d{1,2}):(\d{1,2})$/
    if((!defaultDate.match(Datereg)) && (!defaultDate.match(DateTimereg))){
     alert("Ĭ������(ʱ��)�ĸ�ʽ����ȷ��\t\n\nĬ�Ͽɽ��ܸ�ʽΪ:\n1��yyyy-mm-dd \n2��yyyy-mm-dd hh:mm\n3��(��)");
	 calendar.setDefaultDate();
     return;
    }
	
	if(defaultDate.match(Datereg)) defaultDate=defaultDate+" "+calendar.today[3]+":"+calendar.today[4];
	var strDateTime=defaultDate.match(DateTimereg);
	var theDate=new Array(4)
	theDate[0]=strDateTime[1];
	theDate[1]=strDateTime[2];
	theDate[2]=strDateTime[3];
	theDate[3]=strDateTime[4];
	theDate[4]=strDateTime[5];
    calendar.head.innerText = theDate[0]+"-"+calendar.formatTime(theDate[1])+"-"+calendar.formatTime(theDate[2]);
    calendar.bottom.innerText = calendar.formatTime(theDate[3])+":"+calendar.formatTime(theDate[4]);
	}
   calendar.currentDate[0]=theDate[0];
   calendar.currentDate[1]=theDate[1];
   calendar.currentDate[2]=theDate[2];
   calendar.currentDate[3]=theDate[3];
   calendar.currentDate[4]=theDate[4];
   
   theFirstDay=calendar.getFirstDay(theDate[0],theDate[1]);
   theMonthLen=theFirstDay+calendar.getMonthLen(theDate[0],theDate[1]);
   //calendar.setEventKey();

   calendar.calendarPad.style.display="";
   var theRows = Math.ceil((theMonthLen)/7);
   //����ɵ�����;
   while (calendar.body.rows.length > 0) {
    calendar.body.deleteRow(0)
   }
   //�����µ�����;
   var n=0;day=0;
   for(i=0;i<theRows;i++){
    theRow=calendar.body.insertRow(i);
    for(j=0;j<7;j++){
     n++;
     if(n>theFirstDay && n<=theMonthLen){
      day=n-theFirstDay;
      calendar.insertBodyCell(theRow,j,day);
     }

     else{
      var theCell=theRow.insertCell(j);
      theCell.style.cssText="background-color:#F0F0F0;cursor:default;";
     }
    }
   }

   //****************��������λ��**************//
   var offsetPos=calendar.getAbsolutePos(calendar.source);//��������λ��;
   if((document.body.offsetHeight-(offsetPos.y+calendar.source.offsetHeight-document.body.scrollTop))<calendar.calendarPad.style.pixelHeight){
    var calTop=offsetPos.y-calendar.calendarPad.style.pixelHeight;
   }
   else{
    var calTop=offsetPos.y+calendar.source.offsetHeight;
   }
   if((document.body.offsetWidth-(offsetPos.x+calendar.source.offsetWidth-document.body.scrollLeft))>calendar.calendarPad.style.pixelWidth){
    var calLeft=offsetPos.x;
   }
   else{
    var calLeft=calendar.source.offsetLeft+calendar.source.offsetWidth;
   }
   //alert(offsetPos.x);
   calendar.calendarPad.style.pixelLeft=calLeft;
   calendar.calendarPad.style.pixelTop=calTop;
  }
  /****************** ��������λ�� *************************/
  this.getAbsolutePos = function(el) {
   var r = { x: el.offsetLeft, y: el.offsetTop };
   if (el.offsetParent) {
    var tmp = calendar.getAbsolutePos(el.offsetParent);
    r.x += tmp.x;
    r.y += tmp.y;
   }
   return r;
  };

  //************* �������ڵ�Ԫ�� **************/
  this.insertBodyCell=function(theRow,j,day,targetObject){
   var theCell=theRow.insertCell(j);
   if(j==0) var theBgColor="#FF9999";
   else var theBgColor="#FFFFFF";
   if(day==calendar.currentDate[2]) var theBgColor="#CCCCCC";
   if(day==calendar.today[2]) var theBgColor="#99FFCC";
   theCell.bgColor=theBgColor;
   theCell.innerText=day;
   theCell.align="center";
   theCell.width=35;
   theCell.style.cssText="border:1 solid #CCCCCC;cursor:hand;";
   theCell.onmouseover=function(){ 
    theCell.bgColor="#FFFFCC"; 
    theCell.style.cssText="border:1 outset;cursor:hand;";
   }
   theCell.onmouseout=function(){ 
    theCell.bgColor=theBgColor; 
    theCell.style.cssText="border:1 solid #CCCCCC;cursor:hand;";
   }
   theCell.onmousedown=function(){ 
    theCell.bgColor="#FFFFCC"; 
    theCell.style.cssText="border:1 inset;cursor:hand;";
   }
   theCell.onclick=function(){
	 if(calendar.returnTime)  
	    calendar.sltDate=calendar.currentDate[0]+"-"+calendar.formatTime(calendar.currentDate[1])+"-"+calendar.formatTime(day)+" "+calendar.formatTime(calendar.currentDate[3])+":"+calendar.formatTime(calendar.currentDate[4])
	 else
	    calendar.sltDate=calendar.currentDate[0]+"-"+calendar.formatTime(calendar.currentDate[1])+"-"+calendar.formatTime(day);
    calendar.target.value=calendar.sltDate;
    calendar.hide();
   }
  }
  /************** ȡ���·ݵĵ�һ��Ϊ���ڼ� *********************/
  this.getFirstDay=function(theYear, theMonth){
   var firstDate = new Date(theYear,theMonth-1,1);
   return firstDate.getDay();
  }
  /************** ȡ���·ݹ��м��� *********************/

  this.getMonthLen=function(theYear, theMonth) {
   theMonth--;
   var oneDay = 1000 * 60 * 60 * 24;
   var thisMonth = new Date(theYear, theMonth, 1);
   var nextMonth = new Date(theYear, theMonth + 1, 1);
   var len = Math.ceil((nextMonth.getTime() - thisMonth.getTime())/oneDay);
   return len;
  }
  /************** �������� *********************/
  this.hide=function(){
   //calendar.clearEventKey();
   calendar.calendarPad.style.display="none";
   
  }
  /************** �����￪ʼ *********************/
  this.setup=function(defaultDate){
   calendar.addCalendarPad();
   calendar.addCalendarBoard();
   calendar.setDefaultDate();
  }
  /************** ��ʽ��ʱ�� *********************/
 this.formatTime = function(str) {
  str = ("00"+str);
  return str.substr(str.length-2);
 }

/************** ����AgetimeCalendar *********************/
  this.about=function(){
   var strAbout = "\nWeb ����ѡ������ؼ�����˵��:\n\n";
   strAbout+="-\t: ����\n";
   strAbout+="x\t: ����\n";
   strAbout+="<<\t: ��һ��\n";
   strAbout+="<\t: ��һ��\n";

   strAbout+="����\t: ���ص�������\n";
   strAbout+=">\t: ��һ��\n";
   strAbout+="<<\t: ��һ��\n";
   strAbout+="\nWeb����ѡ������ؼ�\tVer:v1.0\t\nDesigned By:wxb \t\t2004.11.22\t\n";
   alert(strAbout);
  }
  
document.onkeydown=function(){
	if(calendar.calendarPad.style.display=="none"){
		window.event.returnValue= true;
		return true ;
	}
	switch(window.event.keyCode){
		case 27 : calendar.hide(); break; //ESC
		case 37 : calendar.prevMonth.onmousedown(); break;//��
		case 38 : calendar.prevYear.onmousedown();break; //��
		case 39 : calendar.nextMonth.onmousedown(); break;//��
		case 40 : calendar.nextYear.onmousedown(); break;//��
		case 84 : calendar.goToday.onclick(); break;//T
		case 88 : calendar.hide(); break;   //X
		case 72 : calendar.about(); break;   //H	
		case 36 : calendar.prevHours.onmousedown(); break;//Home
		case 35 : calendar.nextHours.onmousedown(); break;//End
		case 33 : calendar.prevMinutes.onmousedown();break; //PageUp
		case 34 : calendar.nextMinutes.onmousedown(); break;//PageDown
		} 
		window.event.keyCode = 0;
		window.event.returnValue= false;
		}

  calendar.setup();
 }
  
var CalendarWebControl = new atCalendarControl();

//=============================================================================ʱ��ѡ�� ����============================================================

