<?php
/**
 * Created by IntelliJ IDEA.
 * User: yaoxy
 * Date: 2015/9/22
 * Time: 17:30
 * 获取设置替换url参数
 */
?>
<script class="dourl">
    mysoft.prepare(function(){
        var $ = Zepto || $;
        tplData.component = window.tplData.component || {};
        tplData.component.doUlr = $.doUrl = function(str){
            var name,value,num=str.indexOf("?"),url_obj={},preurl="";
            if(num == -1){
                preurl = str;
            }else{
                preurl = str.substr(0,num);
            }
            str=str.substr(num+1); //取得所有参数
            var arr=str.split("&"); //各个参数放到数组里
            for(var i=0;i < arr.length;i++){
                num=arr[i].indexOf("=");
                if(num>0){
                    name=arr[i].substring(0,num);
                    value=arr[i].substr(num+1);
                    url_obj[name]=value;
                }
            }
            this.get = function(str){
                if(str){
                    return url_obj[str];
                }else{
                    return url_obj;
                }
            };
            this.set = function(obj){
                if(typeof  obj == "object"){
                    for(var key in obj){
                        if(obj[key]!==null){
                            url_obj[key]=obj[key];
                        }else{
                            delete url_obj[key];
                        }
                    }
                    return preurl+ "?" + $.param(url_obj);
                }else{
                    return str;
                }
            }
        }
    });
</script>