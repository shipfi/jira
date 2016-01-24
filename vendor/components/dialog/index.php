<style type="text/css">
    .public-popup{position:fixed;top:0;bottom:0;left:0;width:100%;background-color:rgba(0,0,0,0.5);z-index:999999;font-size:14px;}
    .public-popup.dialog-loading{background-color:transparent;}
    .public-popup h4{font-size:16px;color:#222;font-weight: normal;}
    .public-popup .button {-webkit-box-flex:1;border-radius:0;width:50%;height: 50px;line-height: 50px;font-size: 16px;outline:none;color:#2b2b2b;}
    .public-popup .button.sure{font-weight:bold;}
    .public-popup .modal-header{line-height:32px;padding:0px 12px 0;}
    .public-popup .modal-body{padding:4px 12px 15px;font-size: 14px;color:#222; text-align: left;}
    .public-popup .modal-footer{ display:-webkit-box;border-top-width:1px;}
    .public-popup .modal-dialog{text-align:center;max-width:90%;}
    .public-popup .modal-content{background-color:#fff;border-radius:5px;overflow:hidden ;padding-top:12px;}
    .public-popup .modal-footer .cancel{border-right-width:1px;}
    .public-popup .button.active{background-color:#fe9440;color:#fff;}
    .public-popup .content-ajax{background-color:rgba(0,0,0,0.78);padding:15px 12px;text-align:center;color:#fff;line-height:1.8em;}
    .public-popup .dialog-btnbottom{width:100%;border-radius:0;color:#007aff;padding:8px;}
    .public-popup .dialog-btnbottom .modaldialog-ul{border-radius:12px;overflow:hidden;}
    .public-popup .dialog-btnbottom.trans{-webkit-transition:-webkit-transform 400ms ease-in-out;}
    .public-popup .dialog-btnbottom li{padding:7px 12px;line-height:2em;background-color:#fff;text-align:center;border-bottom-width:1px;font-size:20px;}
    .public-popup .dialog-btnbottom li.active{background-color:#d9d9d9;}
    .public-popup .dialog-btnbottom li[data-type="modaldialog-cancel"]{font-weight:bold;}
    .public-popup .dialog-btnbottom .modaldialog-ul:last-child{margin-top:8px;}
    .public-popup .ani{-webkit-transition:top 300ms linear}
    .public-popup .loading-pop-img{-webkit-animation:imgloading 1.2s infinite linear; animation: imgloading 1.2s infinite linear;-webkit-transform:translate3d(0,0,0)}
     @-webkit-keyframes imgloading{
        0%{-webkit-transform:rotate(0deg)}
        100%{-webkit-transform:rotate(360deg)}
    }
</style>
<script type="text/html" id="com-jstemp-dialog">
    <div class="modal {{defaultClass}} public-popup{{if loading}} dialog-loading{{/if}} {{addClass}}" {{if !loading&&!btns}}style="background-color:transparent"{{/if}}>
        {{if loading || btns}}
        <div class="modal-dialog" style="position:absolute;left:50%;">
            {{if loading}}
            <div class="modal-content content-ajax {{loading.type}}">
                {{if loading.type=="loading"}}<img height="16" width="16" class="loading-pop-img" src="_STATIC_/images/loading.png"/>{{/if}}
                <div class="dialog-loading-text">{{#loading.text}}</div>
            </div>
            {{else}}
            <div class="modal-content">
                {{if title}}
                <div class="modal-header">
                    <h4 class="modal-title">{{title}}</h4>
                </div>
                {{/if}}
                {{if content}}
                <div class="modal-body">{{#content}}</div>
                {{/if}}
                {{if btns}}
                <div class="modal-footer border-1px">
                    {{if btns.canceltext}}
                    <div class="button btn-default cancel border-1px" data-ac="active">{{btns.canceltext}}</div>
                    {{/if}}
                    {{if btns.suretext}}
                    <div class="button btn-primary sure" data-ac="active">{{btns.suretext}}</div>
                    {{/if}}
                </div>
                {{/if}}
            </div>
            {{/if}}
        </div>
        {{/if}}
    </div>
</script>
<script type="text/html" id="com-jstemp-modaldialog">
    <div class="modal public-popup {{addClass}} {{defaultClass}}">
        <div class="dialog-btnbottom" style="position:absolute;width:100%;bottom:0;">
                {{if btns}}
                    <ul class="modaldialog-ul">
                        {{each btns as val key}}
                            <li data-ac="active" data-type="{{val.type}}" class="btn-li border-1px">{{val.text}}</li>
                        {{/each}}
                    </ul>
                     <ul class="modaldialog-ul">
                         <li data-ac="active" data-type="modaldialog-cancel" class="btn-li border-1px">取消</li>
                     </ul>
                {{/if}}
        </div>
    </div>
</script>
<script type="text/javascript">
    mysoft.prepare(function() {
        var $ = Zepto || $;
        var base=function(obj){
            var option = $.extend(true,{
                title:"提 示",
                content:"确认操作",
                closetime:0,//自动关闭时间
                width:290,//"100,auto,100%"
                loading:null,
                btns:{
                    canceltext:"取消",
                    suretext:"确定",
                    sure:function(){},
                    cancel:function(){}
                },
                defaultClass:"dialog",
                addClass:"",
                closeCallback:function(){}
            },obj);
            var html = $(template("com-jstemp-dialog",option));
            if(option.closetime){
                setTimeout(function(){
                    html.remove();
                    option.closeCallback();
                },option.closetime);
            }
            $("body").append(html);
            html.on("touchmove",function(e){
                    if(!$(e.target).parents(".allowmove").size()){
                        e.preventDefault()
                    }
                }
            );
            if(option.loading || option.btns){
                var dialog = html.find(".modal-dialog").css({"margin-top":0,"width":option.width});
                if(dialog.height()< $(window).height()){
                    var top = $(window).height()/2 - dialog.height()/2;
                    dialog.css("top",top);
                }else{
                    var h1 = $(window).height(),h2 = $("body").height();
                    html.css({"position":"absolute","top":"0","left":0,"width":"100%","height":h1>h2?h1:h2});
                }
                dialog.css({left:0}).css({left:"50%","margin-left":-dialog.width()/2});
                html.find(".cancel,.close").on("click",function(){
                    var state = option.btns.cancel();
                    if((state!="preventDefault") && ( state!=false) ){
                        $(this).closest(".public-popup").remove();
                    };
                    $('body').removeClass('bodyHidden');
                });
                html.find(".sure").on("click",function(){
                    var state = option.btns.sure();
                    if((state!="preventDefault") && ( state!=false) ){
                        $(this).closest(".public-popup").remove();
                    }
                });
                if($(".modal-body").height()<60){
                    $(".modal-body").css({"text-align":"center"});
                }
            }
            html.css({"min-height":$(window).height()});
            return html;
        };
        $.dialog = function(obj){
            $.dialog.close();
            var option = $.extend(true,{
                defaultClass:"dialog"
            },obj);
            return base(option);
        }
        $.alert = function(obj){
            $.alert.close();
            var option =  $.extend({
                width:"80%",
                title:"提示",
                content:"提示内容",
                btntext:"确定",
                defaultClass:"alert",
                callback:function(){}
            },obj);
            option.btns={
                    canceltext:"",
                    suretext:option.btntext,
                    sure:option.callback,
                    cancel:function(){}
                };
            if(typeof obj == "string"){
                option.content=obj;
            }
            return base(option);
        };
        $.toast = function(obj){
            $.toast.close();
                var option =  $.extend({
                    loading:{
                        text:"未知错误，请重试"
                    },
                    width:"70%",
                    defaultClass:"toast",
                    closetime:2000
                },obj);
            if(typeof obj == "string"){
                option.loading.text = obj;
            }else{
                option.loading.text = option.text || option.loading.text;
            }
            return base(option);
        };
        $.loading = function(obj){
            $.loading.close();
            var option =  $.extend({
                loading:{
                    type:"loading",
                    text:"加载中…请稍后"
                },
                defaultClass:"loading",
                width:"auto"
            },obj);
            option.loading.text = option.text || option.loading.text;
            return base(option);
        };
        $.modaldialog = function(opt){
            var option = $.extend({
                btns:[{type:"type1",text:"确认"},{type:"type2",text:"确认2"}],
                defaultClass:"modaldialog",
                callback:function(str){}
            },opt);
            var html = $(template("com-jstemp-modaldialog",option));
            $(".public-popup.modaldialog").remove();
            $("body").append(html);
            html.find("[data-type='modaldialog-cancel']").on("click",function(){
                $.modaldialog.close();
            });
            var transdom =  html.find(".dialog-btnbottom");
            transdom.css({"-webkit-transform": "translate(0,"+transdom.height()+"px)"});
            setTimeout(function(){
                transdom.addClass("trans");
                transdom.css({"-webkit-transform": "translate(0,0)"});
            },100);
            html.on("click","li.btn-li",function(e){
                var type = $(this).data("type");
                option.callback.call(this,type);
            });
            html.on("touchmove",function(e){e.preventDefault()});
            html.on("click",function(e){
                if(e.target.tagName.toLowerCase() != "li"){
                    $.modaldialog.close();
                }
            });
        }
        $.modaldialog.close=function(){
            var transdom = $(".public-popup .dialog-btnbottom");
            transdom.css({"-webkit-transform": "translate(0,"+transdom.height()+"px)"});
            setTimeout(function(){$(".public-popup.modaldialog").remove()},300);
        };
        $.alert.close=function(){
            $(".public-popup.alert").remove();
        };
        $.loading.close = function(){
            $(".public-popup.loading").remove();
        }
        $.toast.close = function(){
            $(".public-popup.toast").remove();
        };
        $.dialog.close = function(){
            $(".public-popup.dialog").remove();
        };
        tplData.component = window.tplData.component || {};
        tplData.component.dialog = $.dialog;
        tplData.component.alert = $.alert;
        tplData.component.toast = $.toast;
        tplData.component.loading = $.loading;
        tplData.component.modaldialog = $.modaldialog;
    });
</script>