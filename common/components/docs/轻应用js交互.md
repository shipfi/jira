## 轻应用交互 ##

### 如何使用： ###


####php部分####

在各自页面的controller中,判断是否来自微信平台。通过

	$ua["from"]=="wx"

判断，如果是来自微信平台调用平台的 `jscfg()` 方法，会得到一个返回字符串，将返回字符串赋值
	 
		//获取js连接
		$wxconfig=jscfg();
		//将该连接赋值到模板
		return $this->render('index', ['wxconfig' => $wxconfig]);

####模板部分####

在布局文件模板中main.php中加上（如果main.php模板中已经加入该代码，不需要处理）：
 
		<?php if($ua['from']=="wx" && !empty($wxconfit)){;?>
	    	<script src="<?=$wxconfig?>" type="text/javascript"></script>
			<script type="text/javascript">
				wx.config({
		            debug: false,
		            appId: wxconfig.appId,
		            timestamp: wxconfig.timestamp,
		            nonceStr: wxconfig.nonceStr,
		            signature: wxconfig.signature,
		            jsApiList: ["chooseImage", "uploadImage","getNetworkType","previewImage"]
        		});
			</script>
	    <?php };?>	

对应的各个模板页面中加入：

    <script type="text/javascript" src="_STATIC_/js/app-bridge/appbridge.js"></script>

### 使用接口： ###

1.选着图片

    tplData.appBridge.chooseImage({
		    count: 1, // 默认9，默认最多上传张数
		    success: function (res) {
				//返回数据见下格式
		        // 返回选定照片的本地images列表，image可以作为img的src使用，localId可以作为调用图片预览接口 previewImage 时使用
		    },
			error:function(){}
	}) 

返回数据

    {
		"sourceType":"album",
		"errMsg":"chooseImage:ok"，
		images:[{image:"src||base64",localId:"wzsLocalResource:1714636915"},{image:"src||base64",localId:"wzsLocalResource:1714636915"}]
	}

2.预览图片

     tplData.appbridge.previewImage({
    	current:"localId",//上一步中，选取图片，返回的localId，表示当前在第几张图片
    	urls:[]  //上一步中，选取图片，返回的localId
	});

3.上传图片

	tplData.appbridge.uploadImage({
		localIds:["localId","localId"],//localId组成的数组
        success:function(data){
            //返回数据格式如下
        },
        progress:fucntion(obj){
            //进度相关信息
        },
        error:function(data){
            alert(JSON.stringify(data));
        }
    });

返回数据格式

	{
	    "errMsg":"uploadImage:ok"，
		"images":[{localId:"localId",url:"https://www.baidu.com/img/bdlogo.png"},{localId:"localId",url:"https://www.baidu.com/img/bdlogo.png"}] //服务端url数组列表
	}
	
	
4.获取网络状态

	tplData.appbridge.getNetworkType({
        success:function(data){
             var networkType = res.networkType; // 返回网络类型2g，3g，4g，wifi
            //返回数据格式如下
        },
        error:function(data){
            alert(JSON.stringify(data));
        }
    });

	
	