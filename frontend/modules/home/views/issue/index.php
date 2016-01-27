<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
    <title>我的任务</title>
    <?= _asset('@web/AmazeUI/assets/css/amaze.min.css'); ?>
    <?= _asset('@web/AmazeUI/assets/css/amazeui.min.css'); ?>
    <style>
        html,
        body,
        .page {
            height: 100%;
        }

        #wrapper {
            position: absolute;
            top: 49px;
            bottom: 0;
            overflow: hidden;
            margin: 0;
            width: 100%;
            padding: 0 8px;
            background-color: #f8f8f8;
        }

        .am-list {
            margin: 0;
        }

        .am-list > li {
            background: none;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
        }

        .pull-action {
            text-align: center;
            height: 45px;
            line-height: 45px;
            color: #999;
        }

        .pull-action .am-icon-spin {
            display: none;
        }

        .pull-action.loading .am-icon-spin {
            display: block;
        }

        .pull-action.loading .pull-label {
            display: none;
        }

        。show
    </style>
</head>
<body>
<div class="page">
    <header class="demo-bar"><a href="<?= U(['index/index']) ?>" class="am-icon-home demo-icon-home"></a>

        <h1>分配给我的</h1></header>
    <div id="demo-view" data-backend-compiled="">
        <nav data-am-widget="menu" class="am-menu am-menu-dropdown1 am-no-layout" data-am-menu-collapse=""><a
                href="javascript: void(0)" class="am-menu-toggle"><img
                    src="data:image/svg+xml;charset=utf-8,&lt;svg xmlns=&quot;http://www.w3.org/2000/svg&quot; viewBox=&quot;0 0 42 26&quot; fill=&quot;%23fff&quot;&gt;&lt;rect width=&quot;4&quot; height=&quot;4&quot;/&gt;&lt;rect x=&quot;8&quot; y=&quot;1&quot; width=&quot;34&quot; height=&quot;2&quot;/&gt;&lt;rect y=&quot;11&quot; width=&quot;4&quot; height=&quot;4&quot;/&gt;&lt;rect x=&quot;8&quot; y=&quot;12&quot; width=&quot;34&quot; height=&quot;2&quot;/&gt;&lt;rect y=&quot;22&quot; width=&quot;4&quot; height=&quot;4&quot;/&gt;&lt;rect x=&quot;8&quot; y=&quot;23&quot; width=&quot;34&quot; height=&quot;2&quot;/&gt;&lt;/svg&gt;"
                    alt="Menu Toggle"></a>
            <ul class="am-menu-nav am-avg-sm-1 am-collapse">
                <li class=""><a href="<?= U(['index/logout']) ?>" class="">注销</a>
                    <ul class="am-menu-sub am-collapse">
                        <li class=""><a href="<?= U(['index/logout']) ?>" class="">注销</a></li>
                    </ul>
                </li>
                <li class=""><a href="<?= U(['index']) ?>" class="">刷新</a>
                    <ul class="am-menu-sub am-collapse">
                        <li class=""><a href="<?= U(['index']) ?>" class="">刷新</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
    </div>
    <div id="wrapper" data-am-widget="list_news"
         class="am-list-news am-list-news-default">
        <div class="am-list-news-bd">
            <div class="pull-action loading" id="pull-down">
        <span class="am-icon-arrow-down pull-label"
              id="pull-down-label"> 下拉刷新</span>
                <span class="am-icon-spinner am-icon-spin"></span>
            </div>
            <ul class="am-list" id="events-list">
                <li class="am-list-item-desced">
                    <div class="am-list-item-text">
                        正在加载内容...
                    </div>
                </li>
            </ul>
            <div class="pull-action" id="pull-up">
        <span class="am-icon-arrow-down pull-label"
              id="pull-up-label"> 上拉加载更多</span>
                <span class="am-icon-spinner am-icon-spin"></span>
            </div>
        </div>
    </div>
</div>

<?= _asset('@web/AmazeUI/assets/js/jquery.min.js'); ?>
<?= _asset('@web/AmazeUI/assets/js/handlebars.min.js'); ?>
<?= _asset('@web/AmazeUI/assets/js/amazeui.min.js'); ?>

<script type="text/x-handlebars-template" id="tpi-list-item">
    {{#each this}}
    <li class="am-list-item-desced" data-id="{{id}}" data-key="{{key}}">
        <a class="am-list-item-hd" target="_blank" href="{{detail_url}}">{{key}}</a>

        <div class="am-list-item-text">{{fields.主题}}</div>
    </li>
    {{/each}}
</script>
<script>
    (function ($) {
        var EventsList = function (element, options) {
            var $main = $('#wrapper');
            var $list = $main.find('#events-list');
            var $pullDown = $main.find('#pull-down');
            var $pullDownLabel = $main.find('#pull-down-label');
            var $pullUp = $main.find('#pull-up');
            var topOffset = -$pullDown.outerHeight();

            this.compiler = Handlebars.compile($('#tpi-list-item').html());
            this.prev = this.next = this.start = options.params.start;
            this.total = null;

            this.getURL = function (params) {
                //console.log(params);
                var queries = [];
                for (var key in  params) {
                    if (key !== 'start') {
                        queries.push(key + '=' + params[key]);
                    }
                }
                return options.api + '?' + queries.join('&');
            };

            this.renderList = function (start, type) {
                var _this = this;
                var $el = $pullDown;

                if (type === 'load') {
                    $el = $pullUp;
                }

                $.getJSON(this.URL + '&start=' + start).then(function (data) {
                    data = data.result;
                    //console.log(data);
                    _this.total = data.total;
                    //console.log(data.issues);
                    var html = _this.compiler(data.issues);
                    //console.log(html);
                    if (type === 'refresh') {
                        $list.html(html);
                    } else if (type === 'load') {
                        $list.append(html);
                    } else {
                        $list.html(html);
                    }

                    // refresh iScroll
                    setTimeout(function () {

                        _this.iScroll.refresh();
                    }, 100);
                }, function () {
                    //console.log('Error...')
                }).always(function () {
                    _this.resetLoading($el);
                    if (type !== 'load') {
                        _this.iScroll.scrollTo(0, topOffset, 800, $.AMUI.iScroll.utils.circular);
                    }
                });
            };

            this.setLoading = function ($el) {
                $el.addClass('loading');
            };

            this.resetLoading = function ($el) {
                $el.removeClass('loading');
            };

            this.init = function () {
                $('.am-list-news-bd').css('min-height', $('.am-list-news-bd').parent().height() + 45)
                if ($('#events-list').height() <= $('.am-list-news-bd').parent().height()) {
                    $('#pull-up').hide();
                }
                var myScroll = this.iScroll = new $.AMUI.iScroll('#wrapper', {
                    click: true
                });
                myScroll.scrollTo(0, topOffset);
                var _this = this;
                var pullFormTop = false;
                var pullStart;

                this.URL = this.getURL(options.params);

                this.renderList(options.params.start);

                myScroll.on('scrollStart', function () {
                    $('#pull-up').show();
                    if (this.y >= topOffset) {
                        pullFormTop = true;
                    }

                    pullStart = this.y;
                    // console.log(this);
                });

                myScroll.on('scrollEnd', function () {
                    if (pullFormTop && this.directionY === -1) {
                        _this.handlePullDown();
                    }
                    pullFormTop = false;

                    // pull up to load more
                    if (pullStart === this.y && (this.directionY === 1)) {
                        _this.handlePullUp();
                    }
                });
            };

            this.handlePullDown = function () {
                this.setLoading($pullDown);
                this.renderList(0, 'refresh');
            };

            this.handlePullUp = function () {
                //console.log('handle pull up');
                console.log(this.next);
                if (this.next < this.total) {
                    this.setLoading($pullUp);
                    this.next += options.params.count;
                    this.renderList(this.next, 'load');
                } else {
                    //console.log(this.next);
                    this.iScroll.scrollTo(0, topOffset);
                }
            }
        };

        $(function () {
            var app = new EventsList(null, {
                api: '<?=U(['ajax-get-issues'])?>',
                params: {
                    start: 0,
                    count: 10,
                    total: 250
                }
            });
            app.init();
        });


        document.addEventListener('touchmove', function (e) {
            e.preventDefault();
        }, false);
    })(window.jQuery);
</script>
</body>
</html>
