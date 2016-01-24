## url处理方法 ##


在页面中引入组件

    <?= _include_once("@vendor/components/doUrl/index.php") ?>


当页面引入改组件后：
url参数获取组件有 **获取参数，增加参数，修改url，删除参数** 参数功能，具体如下：

1.获取url，或者字符串中的参数

    var urlobj = new $.doUrl("http://www.baidu.com?a=1&b=2");
	urlobj.get("a"); //此处返回'1'；
	urlobj.get("b"); //此处返回'2';
	url.obj.get()； //没带参数此处返回 对象 {a: "1", b: "2"}

2.添加url参数

    var urlobj = new $.doUrl("http://www.baidu.com?a=1&b=2");
	urlobj.set({"c":1}); //此处返回'http://www.baidu.com?a=1&b=2&c=1'；

	var urlobj = new $.doUrl("http://www.baidu.com");
	urlobj.set({"param":1}); //此处返回'http://www.baidu.com?param=1'；

3.修改url参数

    var urlobj = new $.doUrl("http://www.baidu.com?a=1&b=2");
	urlobj.set({"a":2}); //此处返回'http://www.baidu.com?a=2&b=2'；

4.删除url参数,将参数设置为null，即可删除参数

    var urlobj = new $.doUrl("http://www.baidu.com?a=1&b=2");
	urlobj.set({"a":null}); //此处返回'http://www.baidu.com?b=2'；

**增删改可以同时进行，如下：**

	var urlobj = new $.doUrl("http://www.baidu.com?a=1&b=2");
	urlobj.set({"a":null,b:3,c:4}); //返回http://www.baidu.com?b=3&c=4

	

	

	

