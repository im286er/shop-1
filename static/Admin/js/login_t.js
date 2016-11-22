$(document).ready(function(){

    var form = $("#formContainersign");

    var uname = $("#username");
    var nameInfo = $("#nameInfo");

    var pass1 = $("#password");
    var pass1Info = $("#pass1Info");

    var verification = $("#verification");
    var verificationInfo = $("#imge_verification");
	
	/*
    uname.blur(validateName);
	
    email.blur(validateEmail);
    email_con.blur(validateEmail_confirm);


    pass1.blur(validatePass1);
    pass2.blur(validatePass2);

	invitecode.blur(validateInvite_code);

    verification.blur(validateverification);
	*/
 


    function trim(s)
    {
        return rtrim(ltrim(s));
    }

    function ltrim(s)
    {
        var l=0;
        while(l < s.length && s[l] == ' ')
        {
            l++;
        }
        return s.substring(l, s.length);
    }

    function rtrim(s)
    {
        var r=s.length -1;
        while(r > 0 && s[r] == ' ')
        {
            r-=1;
        }
        return s.substring(0, r+1);
    }

    function validateEmail(){     
        var a = $("#email").val();
        var filter = /^([a-zA-Z0-9_\.])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        var strValidChars = "-0123456789.";
        var firstem=(a.substring(0,1));
        var strEndChars = "-._";
        var err=0;
        var uerr=0;
        var derr=0;
        var valid =true;
        if(a.length > 8)
        {

            try
            {
                var sp = a.split("@");
                var secem=(sp[0].substring(sp[0].length-1));
                var threm=(sp[1].substring(0,1));
                var com = sp[1].split(".");
                var foem=(com[0].substring(com[0].length-1));
                var fiem=(com[1].substring(0,1));
                if(filter.test(a) && strValidChars.indexOf(firstem) == -1  && strEndChars.indexOf(secem) == -1 && strEndChars.indexOf(threm) == -1 && strEndChars.indexOf(foem) == -1 && strEndChars.indexOf(fiem) == -1)
                {
                    for(i=0;i<a.length;i++)
                    {
                        fiem=(a.substring(i,i+1));
                        if("_".indexOf(fiem) != -1)
                        {
                            uerr=uerr+1;
                        }
                        if(".".indexOf(fiem) != -1)
                        {
                            derr=derr+1;
                        }
                    }
                    for(i=0;i<sp[1].length;i++)
                    {
                        foem=(sp[1].substring(i,i+1));
                        if(strEndChars.indexOf(foem) != -1)
                        {
                            err=err+1;
                        }
                    }
                    if(uerr>2 || err>4 || derr>3)
                    {
                        valid =false;
                    }
                    else
                        valid =true;
                }
                else
                {
                    valid =false;
                }
            }
            catch(err)
            {
                document.getElementById('email').style.background='#f8dbdb';
                document.getElementById('emailInfo').style.color='#B62427';
                emailInfo.text(valid_email);
                email.focus();
                valid = false;
            }
        }
        else
        {
            valid =false;
        }
        if(valid)
        {
            document.getElementById('email').style.background='white';
            document.getElementById('emailInfo').style.color='#B5B5B5';
            emailInfo.text(valid_email);
            return true;
        }
        else
        {
            document.getElementById('email').style.background='#f8dbdb';
            document.getElementById('emailInfo').style.color='#B62427';
            emailInfo.text(valid_email);
            email.focus();
            return false;
        }
    }
	
    function validatesecQues(){
        var a = $("#security_ques").val();
        if(a!="default"){
            $(this).css({
                'background-color':'white'
            });
              
            document.getElementById('secQuesInfo').style.color='#B5B5B5';
            secQuesInfo.text("Select a Security Question");
            return true;
        }
        else{
            $(this).css({
                'background-color':'#f8dbdb'
            });
            document.getElementById('secQuesInfo').style.color='#B62427';
            secQuesInfo.text("Please Select a Security Question");
            secQues.focus();
            return false;
        }
    }
 
	
    function validatecoun(){

        var a = $("#country").val();
               
        if(a!="default"){
            $(this).css({
                'background-color':'white'
            });
              
            document.getElementById('counInfo').style.color='#B5B5B5';
            counInfo.text("Select Your Country");
            return true;
        }
        else{
            $(this).css({
                'background-color':'#f8dbdb'
            });
            document.getElementById('counInfo').style.color='#B62427';
            counInfo.text("Please Select Your Country.");
            coun.focus();
            return false;
        }
    }
	
	function validateInvite_code(){

        var a = $("#invitecode").val();
        if (a.length < 8){
			document.getElementById('invitecode').style.background='#f8dbdb';
            document.getElementById('inviteInfo').style.color='#B62427';
            inviteInfo.text(valid_invite_code);
            return false;
		}else{
			document.getElementById('invitecode').style.background='white';
            document.getElementById('inviteInfo').style.color='#B5B5B5';
            inviteInfo.text(valid_invite_code);
			return true;
		}
    }

    function validateEmail_confirm(){

        var a = $("#email").val();
        var b = $("#email_confirm").val();
        if(a==b){
            $(this).css({
                'background-color':'white'
            });
              
            document.getElementById('email_confirmInfo').style.color='#B5B5B5';
            email_confirmInfo.text(valid_email_confirm);
            return true;
        }
        else{
            $(this).css({
                'background-color':'#f8dbdb'
            });
            document.getElementById('email_confirmInfo').style.color='#B62427';
            email_confirmInfo.text(valid_email_confirm);
            email_con.focus();
            return false;
        }
    }
   

    function validateName(){
        var textbxval=uname.val();
        var filter = /^[A-Za-z0-9_]{5,30}$/;
      		
        if(filter.test(textbxval))
        {
            document.getElementById('username').style.background='white';
            nameInfo.text(valid_username);
            document.getElementById('nameInfo').style.color='#B5B5B5';
            return true;
        }
        else
        {
            document.getElementById('username').style.background='#f8dbdb';
            nameInfo.text(valid_username);
            document.getElementById('nameInfo').style.color='#B62427';
            uname.focus();
            return false;
        }
    }

    function validatePass1(){
        //if(trim($("#password").val()).length >= 8 && trim($("#password").val())!=trim(uname.val())){
		if(trim($("#password").val()).length >= 6){
            document.getElementById('password').style.background='white';
            document.getElementById('pass1Info').style.color='#B5B5B5';
            pass1Info.text(valid_password);
            //validatePass2();
            return true;
        }
        //it's not valid
        else{
            document.getElementById('password').style.background='#f8dbdb';
            document.getElementById('pass1Info').style.color='#B62427';
            pass1Info.text(valid_password);
            pass1.focus();
            return false;
        }
    }
 

    function validatePass2(){
  
        //are NOT valid
        if( pass1.val() != trim(pass2.val()) ){
            $(this).css({
                'background-color':'#f8dbdb'
            });
            document.getElementById('pass2Info').style.color='#B62427';
            pass2Info.text(valid_password_confirm);
            pass2.focus();
            return false;
        }
        //are valid
        else{
            $(this).css({
                'background-color':'white'
            });
            document.getElementById('pass2Info').style.color='#B5B5B5';
            pass2Info.text(valid_password_confirm);
            return true;
        }
    }


    function validateSec(){
        var textbxval=security_ans.val();
        textbxval= textbxval.replace(/^\s+|\s+$/g, '') ;

        //if it's NOT valid
        if(trim(security_ans.val()).length < 3){
            document.getElementById('security_ans').style.background='#f8dbdb';
            document.getElementById('security_ansInfo').style.color='#B62427';
            security_ansInfo.text("Enter Keyword with more than 3 letters!");
            security_ans.focus();
            return false;
        }
        //if it's valid
        else{
            if(textbxval!="")
            {
                document.getElementById('security_ans').style.background='white';
                document.getElementById('security_ansInfo').style.color='#B5B5B5';
                security_ansInfo.text("Enter Keyword with more than 3 letters!");
                return true;
            }
            else
            {
                $(this).css({
                    'background-color':'#f8dbdb'
                });
                document.getElementById('security_ansInfo').style.color='#B62427';
                security_ansInfo.text("Enter Keyword with more than 3 letters!");
                security_ans.focus();
                return false;
            }

        }
    }



    function validateverification(){

        if(verification.val().length < 5)
        {
            document.getElementById('verification').style.background='#f8dbdb';
            document.getElementById('imge_verification').style.color='#B62427';
            verificationInfo.text(valid_verify);
            verification.focus();
            return false;
        }
        else
        {
            document.getElementById('verification').style.background='white';
            document.getElementById('imge_verification').style.color='#B5B5B5';
            verificationInfo.text(valid_verify);
            return true;
        }
    }


    $(".buttonbg").click(function(){
        validateverification();
        //validatePass2();
        validatePass1() ;
        validateName() ;
        //validateEmail_confirm() ;
        //validateEmail();
		//validateInvite_code();
        if(validateverification() & validatePass1() & validateName())
        {
			$("#formContainersign").submit();
            return true;
        }
        else
        {
            return false;
        }
    });
});

/*
function checkdate()
{
    var monthfield=document.getElementById('month').value
    var dayfield=document.getElementById('day').value
    var yearfield=document.getElementById('year').value

    var dayobj = new Date(yearfield, monthfield-1, dayfield)

    if ((dayobj.getMonth()+1!=monthfield)||(dayobj.getDate()!=dayfield)||(dayobj.getFullYear()!=yearfield))
    {
        document.getElementById('tooltipContainer').innerHTML="Please Select a valid Date.";
        document.getElementById('default_error').value="Please Select a valid Date." ;
        return false;
    }
    else
    {
        document.getElementById('tooltipContainer').innerHTML=""	;
        document.getElementById('default_error').value="";
        return true;
    }
}
*/
		
