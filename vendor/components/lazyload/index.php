<?php
/**
 * Created by IntelliJ IDEA.
 * User: yaoxy
 * Date: 2015/9/18
 * Time: 10:18
 * 图片懒加载，包括图片或者div的背景图片
 * 给要懒加载的图片或者div添加lazyloadsrc属性，滚动时，会计算位置，然后会按需给图片设置src或background-img
 * 初始化时，请触发容器的滚动
 */
?>
<script>
    mysoft.prepare(function(){
        var $ = Zepto || $;
        $(function(){
            $.fn.lazyload= function(){
                var me = $(this),top = 0, bottom = $(window).height(),setids = [];
                if(me[0] != window){
                    top = me[0].getBoundingClientRect().top;
                    bottom = top + me[0].getBoundingClientRect().height;
                }
                me.on("scroll",function(){
                    for(var i = 0;i<setids.length;i++){
                        clearTimeout(setids[i]);
                    }
                    setids.push(setTimeout(checkscrollimg,100));
                });
                function checkscrollimg(){
                    var wrap = me;
                    if(me[0] == window){wrap = $(document)};
                    wrap.find("[lazyloadsrc]").each(function(){
                        var $dom = $(this),bound = $dom[0].getBoundingClientRect();
                        if(bound.top + bound.height > top && bound.top < bottom && $dom.attr("lazyloadsrc")){
                            if($dom[0].tagName.toLowerCase()=="img"){
                                $dom.attr("src",$dom.attr("lazyloadsrc"));
                            }else {
                                $dom.css({"background-image":$dom.attr("lazyloadsrc")});
                            }
                            $dom.removeAttr("lazyloadsrc");
                        }
                    });
                    setids = [];
                }
                checkscrollimg();
            }
            $(window).lazyload();
        });
    });
</script>
