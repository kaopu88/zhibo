<!DOCTYPE html>
<html>
    
<head>
            
    <meta charset="UTF-8">
            <title></title>
            
    <style type="text/css">

                    form
                     {
            width: 100%;
              height: 700px;
            margin-top: 0px;
             background: #008B8B;
        }

                    div
                     {
              display: inline-block;
             padding-top: 80px;
             padding-right: 20px;
        }

                    h2
                   {
             font-family: "微软雅黑";
             font-size: 40px;
            color: black;
        }


                    #log
                     {
            color: blue;
        }

                </style>
        
</head>
 <script src="http://libs.baidu.com/jquery/2.0.0/jquery.min.js"></script>   
<body>
        
<div name="login">
                
    <center>
                    
        <div>
                        <h2>
                               简易登录
                        </h2>
                        <p>
                            用户名:1<input id='username' type="text"/>
                        </p>
                        <p>
                            密&emsp;码:1<input id='password'  type="password"/>     
                        </p>
                        <p>
                           <button onclick="gologin()">登录</button>
                        </p>
                    
                        
        </div>
                    
    </center>
            
</div>
<script>
    function gologin()
    {
        var username1 = $("#username").val();
        var password1 = $("#password").val();
        $.post("/h5/user/login",{username:username1,password:password1},function(result){

           if(result.code==0){
                sessionStorage.setItem("access_token",result.data.access_token);
               window.location.href="http://v1.live.libx.com.cn/h5/share/live?id=12273";
           }else{
               alert('登录失败');
           }
        });
    //    document.getElementById("field2").value=document.getElementById("field1").value;
    }
</script>
    
</body>
</html>
