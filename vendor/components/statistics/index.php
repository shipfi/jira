<?php
/**
 * Created by IntelliJ IDEA.
 * User: vank
 * 记录日志*
 * Date: 2015/9/25
 * Time: 14:04
 */?>
<script type="text/javascript" class="statistics" async="true">
(function(){  
    window._mylog={};
    var ua = navigator.userAgent; 
    var isMobile = {  
        Android: function() {  
            return /Android/i.test(ua);  
        },  
        BlackBerry: function() {  
            return /BlackBerry/i.test(ua);  
        },  
        iOS: function() {  
            return /iPhone|iPad|iPod/i.test(ua);  
        },  
        Windows: function() {  
            return /IEMobile/i.test(ua);  
        },  
        any: function() {  
            return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Windows());  
        }  
    };  
    //cs=cursys
    var cs = function(){
        if(isMobile.Android()) return "android";
        if(isMobile.iOS()) return "ios";
        if(isMobile.Windows()) return "windows";
        if(isMobile.BlackBerry()) return "blackberry";
        return "pc";
    };
    //csv=cursysbersion
    var csv = function(){
        if(isMobile.Android()) return ua.toLowerCase().match(/android [\d].\d[.\d]*/i);
        if(isMobile.iOS()) return ua.toLowerCase().match(/os [\d]_\d[_\d]*/i);
        if(isMobile.Windows()) return ua.toLowerCase().match(/Windows Phone [\d].\d[.\d]*/i);
        if(isMobile.BlackBerry()) return "blackberry";
        return "pc";
    };
    var q={
        dm:"<?= Yii::$app->params['pv_url']; ?>",
        t:"<?= Yii::$app->params['pv_url']; ?>/api/statistics/pv",
        u:"window.location",   //url
        su:"document.referrer",  //ref
        ck: 1,  //是否支持cookie1:0
        cl:"32-bit", //颜色深度
        ds:"640*320",    //屏幕尺寸
        ep:0,   //初始值0，时间变量，反映停留时间
        fl:"",  //flash版本
        ja:"",  //java支持1:0
        ln:"zh-cn", //语言zh-cn
        lt:0,   //日期time,time(),首次请求没有
        sb:0,   //当前手机系统
        sbv:0,   //浏当前手机系统版本
        sbd:"",   //浏览器详细信息
        mto:"", //统计分类1
        mts:"", //统计分类2
        mtt:"",  //统计分类3
        et:"",   //事件类型
        o:"<?= I('__orgcode'); ?>",
    };
    //TODO:先简单处理，后续改进成访问各属性获取方式及调用形式
    q.u = encodeURIComponent(document.location.href);
    q.su = encodeURIComponent(document.referrer);
    q.ck = navigator.cookieEnabled;
    q.cl = window.screen.colorDepth || 0 + "-bit";
    q.ds = (window.screen.width || 0) + "x" + (window.screen.height || 0);
    q.ln = navigator.language || navigator.browserLanguage || navigator.systemLanguage || navigator.userLanguage || "";
    q.ja = navigator.javaEnabled();
    q.et = "view";
    q.sb = cs();
    q.sbv = csv();
    q.sbd = encodeURIComponent(navigator.userAgent.toLowerCase());
    //url参数化
    q.cq = function(){
        //参数字符串
        qsp = "u su ck cl ds ep fl ja ln lt mto mts mtt et o sb sbv sbd".split(" ");
        qs = "?";
        for(var tp in qsp)
        {
            qs = qs + qsp[tp]+'='+q[qsp[tp]]+'&';
        }
        qs = qs.substr(0,qs.length-1);
        return qs;
    };
    
    _mylog.push = function(a){
        this.pv={
            _cqs:function(){
                var src = "http://" + q.t + q.cq();
                return src;
            },
            _img:function(){
                var i = document.createElement("img");
                i.setAttribute("src", this._cqs());
                i.style.display = "none";
                i.style.width = "1";
                i.style.height = "1";
                document.body.appendChild(i);
                i.onload=function(){
                    document.body.removeChild(i);
                };
            },
            _te:function(o,s,t){
                q.mto = o || "";
                q.mts = s || "";
                q.mtt = t || "";
                //TODO:采用什么方式调用src
                this._img();
            },
            _tpv:function(o,s,t){
                q.mto = o || "";
                q.mts = s || "";
                q.mtt = t || "";
                //TODO:采用什么方式调用src
                this._img();
            },
            _sap:function(){
                
            }
        };
        //解析数组
        if(a instanceof Array){
            var myevent = ['_trackEvent','_trackPageview','_setAutoPageview'] ;
            if(myevent.indexOf(a[0]) > -1){
                switch(a[0]){
                    case "_trackEvent":
                        //动作点收集事件，统计类型分类1，2，3
                        q.et = 'click';
                        this.pv._te(a[1],a[2],a[3]);
                        break;
                    case "_trackPageview":
                        //PV收集事件
                        q.et = 'view';
                        this.pv._tpv(a[1],a[2],a[3]);
                        break;
                    case "_setAutoPageview":    
                        //设置是否需要收集PV
                        this.pv._scv();
                        break;
                    default:
                        break;
                }
            }
        };
    };
    //TODO：目前加载时自动使用，后续看是否需要改成主动式
(function(){
        if(window.jQuery || window.Zepto){
            $(function(){
                setTimeout(function(){
                    _mylog.push(['_trackPageview','应用活跃度']);
                },20);
            })
        }
    })();
})();
</script>