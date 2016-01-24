<?php
/**
 * Created by IntelliJ IDEA.
 * User: yaoxy
 * Date: 2015/9/16
 * Time: 9:38
 * 排序组件
 *     $("#list-ul").sortable({
 *          handle:".font-icon",//触发排序的目标元素
            draggable:"li",//排序实体
            sortted:function(){} //每次排序回调函数
 *      });
 */
?>
<script type="text/javascript" class="com-sortable">
    mysoft.prepare(function(){
        var $ = Zepto || $;
        $.fn.sortable=function(option){
            var me = $(this),startY= 0,target=null,draggable=null,dragcopy=null,offsetY = me.offset().top,dY= 0,idlist=[],itemH,oldInd= 0,items,target_ind=0;
            if(getComputedStyle(me[0]).position.toLowerCase()== "static"){me.css({"position":"relative"})};
            $("head").append($('<style type="text/css">.sortable-ani{-webkit-transition:-webkit-transform 150ms ease-out}</style>'));
            var opt = $.extend({
                handle:"li",
                draggable:"li",
                sortted:function(){}
            },option);
            items = me.find(opt.draggable).css({"-webkit-transform":"translate3d(0,0,0)"});
            me.on("touchstart",ontouchstart).on("touchmove",ontouchmove).on("touchend",ontouchend);
            function ontouchstart(e){
                var dtarget = $(parentIfText(e.target));
                if(dtarget.closest(opt.handle,me).size()){
                    e.preventDefault;
                    target = dtarget;
                    draggable = target.closest(opt.draggable,me);
                    itemH = draggable.addClass("dragging").height();
                    startY = e.touches[0].pageY;
                    target_ind = oldInd = draggable.index();
                    dY = draggable.offset().top-  me.offset().top;
                    if(!dragcopy){
                        dragcopy = draggable.clone().addClass("dragcopy").removeClass("dragging").css({"position":"absolute","top":0,"width":parseInt(getComputedStyle(draggable[0]).width)}).appendTo(me);
                        dragcopy.css({"-webkit-transform":"translate3d(0,"+dY+"px,0)"});
                    }
                    items.addClass("sortable-ani");
                    draggable.css({"visibility":"hidden"});
                }else{
                    target = null;draggable=null,dragcopy=null;startY=0;
                }
            }
            function ontouchmove(e){
                if(target && draggable){
                    e.preventDefault();
                    var cur_top = dY + e.touches[0].pageY - startY;
                    if(cur_top< 1) cur_top = 1;
                    if(cur_top > me.height() - dragcopy.height() -1) cur_top = me.height() - dragcopy.height() -1;
                    dragcopy.css({"-webkit-transform":"translate3d(0,"+cur_top+"px,0)"});
                    idlist.push(setTimeout(function(){
                        draggable &&  moveDom(cur_top + (draggable.height()/2));
                    },60));
                }
            }
            function moveDom(top){
                for(var i =0;i<idlist.length;i++){
                    clearTimeout(idlist[i]);
                }
                sortDom(top);
                idlist=[];
            }
            function sortDom(top){
                var ds = items.eq(0).height(),whole_h = ds;
                if(top > ds){
                    for(var i =1;i<items.length;i++){
                        var cur_ds = ds + items.eq(i).height();
                        whole_h += items.eq(i).height();
                        if(top < cur_ds && top > ds){
                            target_ind = i;
                        }
                        ds = cur_ds;
                    }
                    if(top >= whole_h){
                        target_ind = items.length -1;
                    }
                }else{
                    target_ind = 0;
                }
                if(target_ind == 0 && top > items.eq(0).height()){
                    return;
                }
                for(var i=0;i<items.length;i++){
                    if(target_ind > oldInd && i > oldInd && i<= target_ind){
                        translate(items.eq(i),-itemH);
                        opt.sortted();
                    }else if(target_ind < oldInd && i >=target_ind && i< oldInd){
                        translate(items.eq(i),itemH);
                        opt.sortted();
                    }else{
                        translate(items.eq(i),0);
                    }
                }
            }
            function ontouchend(e){
                me.find(".dragging").removeClass("dragging").css({"visibility":"visible"});
                me.find(".dragcopy").remove();
                items.removeClass("sortable-ani").css({"-webkit-transform":"translate3d(0,0,0)"});
                if(target_ind > oldInd){
                    items.eq(target_ind).after(draggable);
                }else if(target_ind < oldInd){
                    items.eq(target_ind).before(draggable);
                }
                if(draggable){
                    me.html(me.html());
                }
                items = me.find(opt.draggable);
                target = null;draggable=null,dragcopy=null;startY=0;
            }
            function translate(li,dy){
                li.addClass("sortable-ani").css({"-webkit-transform":"translate3d(0,"+dy+"px,0)"});
            }
            function parentIfText(node){return 'tagName' in node ? node : node.parentNode}
        }
    });
</script>
