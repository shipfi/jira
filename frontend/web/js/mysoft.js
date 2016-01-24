//js后置，将载入模板中函数放入initfunc中。js库底部加载完后执行。
window.addEventListener("pageshow",function(e){
    if (e.persisted) {
        window.location.reload();
    }
},false);
if(navigator.userAgent.match(/android/gi) && window.performance&&window.performance.navigation&&window.performance.navigation.type==2){
    window.location.reload();
}
var mysoft = mysoft || {};
mysoft.initfunc = [];
mysoft.prepare  = function(func){
    this.initfunc.push(func);
};
