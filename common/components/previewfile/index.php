<?php
/**
 * Created by IntelliJ IDEA.
 * User: 彭飞
 * Date: 2015/8/19
 * Time: 15:49
 *  *功能：
 *
 * 文件预览
 * 在轻应用中中预览word、excel、powerpoint、pdf、txt、图片。
 *
 *参数说明：
 * @param {string}  fileUrl  要预览文件的地址,必须是一个完整的外网地址。
 * @param {string}  fileType 文件类型，如：'doc','pdf','jpg'。
 * @param {boolean} returnUrl 是否返回在线打开文件的url，true为返回，false为页面跳转，默认为false。
 * @param {int} fileSize 文件的大小，用来判断图片是否大于5M则不打开。
 *
 *示例：
 *
 * 在跳转的页面中进行预览文件
 *  previewFile('http://abc.com/word.doc','doc');
 *
 * 直接返回在线预览的url
 *  var previewFileUrl = previewFile('http://abc.com/word.doc','doc', true);
 *
 * PS：在微信中如有引入微信JS SDK，则调用微信的图片查看方法。
 *
 */
?>
<script type="text/javascript">
    mysoft.prepare(function(){
        function previewFile(fileUrl, fileType, returnUrl, fileSize) {
            var fType = fileType ? fileType.toLowerCase() : "",
                url = "",
                $fileUrl = fileUrl ? encodeURIComponent(fileUrl) : "",
                ua = navigator.userAgent.toLowerCase();
            if ($fileUrl == "" || fType == "") return;
            if (fType == "doc" || fType == "docx" || fType == "xls" || fType == "xlsx" || fType == "ppt" || fType == "pptx" || fType == "pdf") {
                //url = tplData.SITES.preview_site + "view.aspx?src=" + $fileUrl;
                //encodeURIComponent(url);
                url = tplData.SITES.api_site + "/api/file/iframeview?url=" + $fileUrl+"&ftype="+fType;
            }

//             if (fType == "pdf") {
//                 url = tplData.SITES.preview_site + "view.aspx?src=" + $fileUrl;
//             }

            if (fType == "bmp" || fType == "jpg" || fType == "gif" || fType == "png") {
                if(fileSize && parseInt(fileSize)> 5120){
                    fileUrl = '_STATIC_/images/picoutofsize.png';
                }
                if (window.wx && typeof window.wx == 'object' && tplData.ua.from == "wx") {
                    wx.previewImage({
                        current: fileUrl,
                        urls: [fileUrl]
                    });
                    return;
                } else if (tplData.ua.from == "wzs" && window.cordovaBridge && typeof window.cordovaBridge == 'object') {
                    tplData.appbridge.previewImage({
                        current: fileUrl,
                        urls: [fileUrl]
                    });
                    return;
                } else {
                    url = fileUrl;
                }
            }

            if (fType == "txt") {
                url = tplData.SITES.api_site + "/api/file/txt?url=" + $fileUrl;
            }

            if (returnUrl) {
                return url;
            } else {
                window.location.href = url;
            }
        }
        window.tplData.component = window.tplData.component ||{};
        window.tplData.component.previewFile=previewFile;
    });
</script>