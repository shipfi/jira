<?php
/**
 * Created by IntelliJ IDEA.
 * User: yaoxy
 * Date: 2015/9/7
 * Time: 14:10
 * 微信中设置页面title
 *   // hack在微信等webview中无法修改document.title的情况
 */
?>
<style>
    #iframe-title{position:absolute;width:0;height:0;visibility:hidden;opacity:0;border:0;}
</style>
<script type="text/javascript">
    mysoft.prepare(function(){
        var $ = Zepto || $;
        tplData.component = window.tplData.component || {};
        $.setPageTitle = tplData.component.setPageTitle = function(str,func){
            var str = $.trim(str);
            if(str != undefined && str !=document.title){
                document.title = str;
                if(navigator.userAgent.match(/iphone/gi)){
                    var $iframe = $('<iframe frameborder="0" id="iframe-title" src="_STATIC_/images/border-1px.png"></iframe>');
                    $iframe.on('load',function() {
                        setTimeout(function() {
                            $iframe.off('load').remove();
                        }, 0);
                        if(typeof func == "function"){
                            func();
                        }
                    }).appendTo($("body"));
                }else{
                    if(typeof func == "function"){
                        func();
                    }
                }
            }
        }
    });
</script>
