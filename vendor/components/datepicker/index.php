<div class="com-datepicker-wrap noswitchsinglepage">
    <style>
        .datapicker ul li{ list-style:none; }
        .datepicker-icon{ display:inline-block; vertical-align:-5px; height:24px; width:24px; -webkit-background-size:24px 24px; background-image:url(_STATIC_/images/calendar.png)}
        .datepicker-cover{ position:fixed; top:0; bottom:0; left:0; display:none; width:100%; background-color:rgba(0, 0, 0, 0.5); z-index:9990;}
        .datepicker{ position:fixed; top:50%; left:50%; z-index:9999; display:none; width:290px; margin-left:-145px; margin-top:-125px; font-size:18px; color:#222; background:#fff; font-family:sans-serif; border-radius:5px; }
        .datepicker .scroll-ul{padding-top:50px;padding-bottom:50px;position:absolute;width:100%;}
        .datepicker li{ width:100%; height:50px; line-height:50px; text-align:center; box-sizing:border-box; }
        .datepicker .date-head{ height:50px; line-height:50px; padding:0 15px; border-bottom-width:1px; box-sizing:border-box; }
        .datepicker-picked{ margin-left:5px; }
        .datepicker .date-foot{ height:50px; line-height:50px; text-align:center; box-sizing:border-box; }
        .datepicker .datepicker-confirm{ border-top-width:1px; box-sizing:border-box; border-bottom-left-radius:5px; border-bottom-right-radius:5px; }
        .datepicker-confirm:active{ background-color:#d9d9d9; }
        .datepicker .date-wrap{ display:-webkit-box; height:150px; margin:0 15px;}
        .datepicker .date-wrap > div{ position:relative; -webkit-box-flex:1; height:150px; overflow:hidden;}
        .datepicker .date-wrap > div.wrap-year-month-day{width:23%;}
        .datepicker .date-wrap > div:before{ content:""; position:absolute; pointer-events:none; top:50px; left:12px; right:12px; height:50px; border-top:1px solid #F79702; border-bottom:1px solid #F79702; }
        .datepicker .date-wrap > div:after{ content:""; position:absolute; pointer-events:none; top:0px; width:100%; left:0; bottom:0; background:-webkit-gradient(linear, left top, left bottom, color-stop(0%, rgba(255, 255, 255, 1)), color-stop(10%, rgba(255, 255, 255, 1)), color-stop(38%, rgba(255, 255, 255, 0)), color-stop(62%, rgba(255, 255, 255, 0)), color-stop(90%, rgba(255, 255, 255, 1)), color-stop(100%, rgba(255, 255, 255, 1))); background:-webkit-linear-gradient(top, rgba(255, 255, 255, 1) 0%, rgba(255, 255, 255, 1) 10%, rgba(255, 255, 255, 0) 38%, rgba(255, 255, 255, 0) 62%, rgba(255, 255, 255, 1) 90%, rgba(255, 255, 255, 1) 100%); background:linear-gradient(to bottom, rgba(255, 255, 255, 1) 0%, rgba(255, 255, 255, 1) 10%, rgba(255, 255, 255, 0) 38%, rgba(255, 255, 255, 0) 62%, rgba(255, 255, 255, 1) 90%, rgba(255, 255, 255, 1) 100%); }
        .datepicker .animate{-webkit-transition:-webkit-transform 600ms ease-out}
    </style>
    <script type="text/html" id="jstemp-datepicker-list">
        {{each list as val key}}
        <div class="wrap-{{val.type}}">
            <ul class="scroll-ul" data-type="{{val.type}}" style="-webkit-transform:translate3d(0,{{val.transnum*50}}px,0)">
                {{each val.list as val key}}
                <li data-value="{{val.value||val.text}}">{{val.text}}</li>
                {{/each}}
                {{each val.list as val key}}
                <li data-value="{{val.value||val.text}}">{{val.text}}</li>
                {{/each}}
                {{each val.list as val key}}
                <li data-value="{{val.value||val.text}}">{{val.text}}</li>
                {{/each}}
            </ul>
        </div>
        {{/each}}
    </script>
    <div class="datepicker-cover"></div>
    <div class="datepicker" id="datapicker">
        <div class="date-head border-1px">
            <i class="datepicker-icon"></i>
            <span class="datepicker-picked"></span>
        </div>
        <div class="date-wrap" id="datepicker-date-wrap"></div>
        <div class="date-foot">
            <div class="datepicker-confirm border-1px"  data-ac="active">确定</div>
        </div>
    </div>
    <script>
        mysoft.prepare(function () {
                var $ = Zepto || $;
                var now = new Date(), cur_format = "2010-10-10", renderdate, y = 0, flag = true, calcY = 0, target = null, t1 = 0, initarr = [], hashdata = {};
                var week = ['周日', '周一', '周二', '周三', '周四', '周五', '周六'], monthday = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
                $('#datapicker .date-wrap').on({
                    touchstart: function (e) {
                        e.preventDefault();
                        target = $(e.target).closest('ul');
                        var cur_obj = hashdata[target.data("type")];
                        cur_obj.lock = cur_obj.lock || 0;
                        if (target.length > 0 && !cur_obj.lock) {
                            y = e.touches[0].clientY;
                            flag = true;
                            t1 = +new Date();
                        } else {
                            flag = false;
                        }
                    },
                    touchmove: function (e) {
                        e.preventDefault();
                        if (flag) {
                            var y1 = e.touches[0].clientY;
                            calcY = y1 - y;
                            var cur_type = target.data("type");
                            trans(target, hashdata[cur_type].transnum * 50 + calcY);
                        }
                    },
                    touchend: function (e) {
                        e.preventDefault();
                        if (target && flag && !hashdata[target.data("type")].lock) {
                            target.addClass("animate");
                            var cur_type = target.data("type"), cur_y = hashdata[cur_type].transnum * 50 + calcY, t2 = +new Date();
                            flag = false;
                            hashdata[target.data("type")].lock = 1;
                            var v = (calcY / (t2 - t1)), ds = v * 250;
                            if (Math.abs(ds) > 8 * 50) {
                                if (ds < 0) {ds = -8 * 50;}
                                if (ds > 0) {ds = 8 * 50;}
                            }
                            hashdata[cur_type].transnum = Math.round((cur_y + ds) / 50);
                            cur_y = hashdata[cur_type].transnum * 50;
                            setTimeout(function(){hashdata[target.data("type")].lock=0},800);
                            refreshtime(target);
                            trans(target, cur_y);
                        }
                    }
                });
                $('#datapicker .date-wrap').on("webkitTransitionEnd", "ul", function (e) {
                    anitime($(this));
                    hashdata[$(this).data("type")].lock = 0;
                    setTime();
                });
                function refreshtime(target) {
                    var type = target.data("type"), cur = target.find("li").eq(Math.abs(hashdata[type].transnum)).data("value"), oldmonday = monthday[renderdate.getMonth()],cur_year = renderdate.getFullYear(),cur_day = renderdate.getDate();
                    if (type == "day") {
                        renderdate.setDate(cur);
                    }
                    else if (type == "year-month-day") {
                        var cur_d = new Date(cur);
                        renderdate.setFullYear(cur_d.getFullYear());
                        renderdate.setMonth(cur_d.getMonth());
                        renderdate.setDate(cur_d.getDate());
                    } else if (type == "hour") {
                        renderdate.setHours(cur);
                    }
                    else if (type == "min") {
                        renderdate.setMinutes(cur);
                    }
                    if (type == "year" || type == "month") {
                        setTimeout(function(){
                            var cur_year = renderdate.getFullYear(), cur_month = renderdate.getMonth(),cur_day = renderdate.getDate();
                            if(type == "year"){
                                cur_year = cur;
                            }else if(type="month"){
                                cur_month = cur -1;
                            }
                            if(cur_month == 1 && cur_day > 28 && (cur_year % 4 ==0 && cur_year % 1000 !=0)){
                                renderdate.setDate(29);
                            }else if(renderdate.getMonth() == 1 && cur_day > 28){
                                renderdate.setDate(28);
                            }else if(cur_day > monthday[cur_month]){
                                renderdate.setDate(monthday[cur_month]);
                            }
                            if(type == "year"){
                                renderdate.setFullYear(cur);
                            }else if(type="month"){
                                renderdate.setMonth(cur - 1);
                            }
                            hashdata.day.list = getday();
                            var dday = monthday[renderdate.getMonth()] - oldmonday;
                            hashdata["day"].transnum -= dday;
                            if(hashdata["day"].transnum>0) hashdata["day"].transnum=0;
                            var $list = $(template("jstemp-datepicker-list", {list: [hashdata.day]}));
                            var old_ul = $('#datapicker ul[data-type="day"]');
                            old_ul.after($list.find('ul[data-type="day"]'));
                            old_ul.remove();
                            var cur_dday = $('#datapicker ul[data-type="day"] li').eq(Math.abs(hashdata["day"].transnum)).data("value");
                            renderdate.setDate(parseInt(cur_dday));
                            setTime(target);
                        },20);
                    }
                }
                function anitime(target) {
                    target.removeClass("animate");
                    var type = target.data("type");
                    var dnum = {
                        year: function () {
                            return 30
                        }, month: function () {
                            return 12
                        }, day: function () {
                            return monthday[renderdate.getMonth()]
                        }, "year-month-day": function () {
                            return 30
                        }, hour: function () {
                            return 24
                        }, "min": function () {
                            return 60
                        }
                    }
                    if (type == "year") {
                        if (hashdata[type].transnum < -60) {
                            hashdata[type].transnum += 60;
                        }
                        if (hashdata[type].transnum > -30) {
                            hashdata[type].transnum -= 60;
                        }
                    } else if (type == "year-month-day") {
                        var cur = target.find("li").eq(Math.abs(hashdata[type].transnum)).data("value"), cur_time = new Date(cur);
                        hashdata["year-month-day"] = builddate({type: "year-month-day"});
                        var $list = $(template("jstemp-datepicker-list", {list: [hashdata["year-month-day"]]}));
                        var old_ul = $('#datapicker ul[data-type="year-month-day"]');
                        old_ul.after($list.find('ul[data-type="year-month-day"]'));
                        old_ul.remove();
                    } else {
                        var cur_obj = dnum[type];
                        if (hashdata[type].transnum < -2 * cur_obj()) {
                            hashdata[type].transnum = hashdata[type].transnum + cur_obj();
                        }
                        if (hashdata[type].transnum > -cur_obj()) {
                            hashdata[type].transnum -= cur_obj();
                        }
                    }
                    trans(target, hashdata[type].transnum * 50);
                }

                function init(option) {
                    initarr = [];
                    var reg = /\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}/g, reg2 = /\d{4}-\d{2}-\d{2}/g;
                    if($.trim(option.value)){
                        renderdate = new Date(option.value.replace(/-/g,"/"))
                    }else{
                        renderdate = now;
                    }
                    cur_format = option.format || cur_format;
                    if (reg.test(cur_format)) {
                        var curarr = ["year-month-day", "hour", "min"];
                    } else if (reg2.test(cur_format)) {
                        var curarr = ["year", "month", "day"];
                    };
                    for (var i = 0; i < curarr.length; i++) {
                        var cur_obj = builddate({type: curarr[i]});
                        hashdata[curarr[i]] = cur_obj;
                        initarr.push(cur_obj);
                    }
                    var $list = template("jstemp-datepicker-list", {list: initarr});
                    $("#datepicker-date-wrap").html($list);
                    setTime();
                    window.renderdate = renderdate;
                }

                function trans(tar, dy) {
                    if(dy >0) dy = 0;
                    setTimeout(function(){tar.css({"-webkit-transform": "translate3d(0," + dy + "px,0)"});},0);
                }
                function builddate(obj) {
                    var resobj = $.extend({
                        list: []
                    }, obj);
                    if (obj.type == "year-month-day") {
                        var min_time = +renderdate - 30 * 24000 * 60 * 60, max_time = +renderdate + 30 * 24000 * 60 * 60;
                        for (var di = min_time; di < max_time; di += 24000 * 60 * 60) {
                            var cur_t = new Date(di);
                            resobj.list.push({
                                text: (cur_t.getMonth() + 1) + "月" + (cur_t.getDate() + "日 ") + (week[cur_t.getDay()]),
                                value: +cur_t
                            });
                        }
                        resobj.transnum = -30;
                    } else if (obj.type == "hour") {
                        for (var i = 0; i < 24; i++) {
                            resobj.list.push({text: numfix(i), value: i});
                        }
                        resobj.transnum = -24 - renderdate.getHours();
                    } else if (obj.type == "min") {
                        for (var i = 0; i < 60; i++) {
                            resobj.list.push({text: numfix(i), value: i});
                        }
                        resobj.transnum = -(60) - Math.ceil(renderdate.getMinutes());
                    } else if (obj.type == "year") {
                        var min_time = +now - 365 * 30 * 24000 * 60 * 60, max_time = +now + 365 * 30 * 24000 * 60 * 60;
                        for (var di = min_time; di < max_time; di += 365 * 24000 * 60 * 60) {
                            var cur_t = new Date(di);
                            resobj.list.push({text: cur_t.getFullYear(), value: cur_t.getFullYear()});
                        }
                        resobj.transnum = -30 + now.getFullYear() - renderdate.getFullYear();
                    } else if (obj.type == "month") {
                        for (var i = 0; i < 12; i++) {
                            resobj.list.push({text: numfix(i + 1), value: i + 1});
                        }
                        resobj.transnum = -12 - renderdate.getMonth();
                    } else if (obj.type == "day") {
                        resobj.list = getday();
                        resobj.transnum = -monthday[renderdate.getMonth()] - renderdate.getDate() + 1;
                    }
                    return resobj;
                }

                function getday() {
                    var list = [];
                    var cur_year = renderdate.getFullYear(), cur_month = renderdate.getMonth();
                    if ((cur_year % 4 == 0 && cur_year % 100 != 0)||(cur_year % 100 == 0 && cur_year % 400 == 0)) {
                        monthday[1] = 29;
                    }else{
                        monthday[1] = 28;
                    }
                    for (var i = 0; i < monthday[cur_month]; i++) {
                        list.push({text: numfix(i + 1)});
                    }
                    return list;
                }

                function numfix(num) {
                    if (num >= 10) {
                        return String(num)
                    } else {
                        return String("0" + num);
                    }
                };
                function setTime(target) {
                    var str = renderdate.getFullYear() + "-" + numfix(renderdate.getMonth() + 1) + "-" + numfix(renderdate.getDate()), cur_wk = week[renderdate.getDay()], cur_hour = numfix(renderdate.getHours()) + ":" + numfix(renderdate.getMinutes());
                    if (cur_format.length > 12) {
                        str += " " + cur_wk + " " + cur_hour;
                    } else {
                        str += " " + cur_wk;
                    }
                    $(".datepicker-picked").html(str);
                }

                var trigger = null;
                $('.container').on('click', '[data-datepicker]', function () {
                    trigger = $(this);
                    init({
                        format: trigger.data("datepicker") || $.trim(trigger.val()),
                        value: $.trim(trigger.val())
                    });
                    $('.datepicker-cover,.datepicker').show();
                });

                $('.datepicker-cover').on('click', function () {
                    $('.datepicker-cover,.datepicker').hide();
                });
                $('.datepicker').on('click', '.datepicker-confirm', function () {
                    var me = $(this), str = renderdate.getFullYear() + "-" + numfix(renderdate.getMonth() + 1) + "-" + numfix(renderdate.getDate()), cur_wk = week[renderdate.getDay()], cur_hour = numfix(renderdate.getHours()) + ":" + numfix(renderdate.getMinutes());
                    trigger.data("time", +renderdate);
                    if (hashdata["year-month-day"]) {
                        str += " " + cur_hour;
                    }
                    trigger.val(str);
                    trigger.trigger('change');
                    hashdata = {};
                    setTimeout(function () {
                        $('.datepicker-cover,.datepicker').hide();
                    }, 100);
                });

                $('.datepicker ,.datepicker-cover').on("touchmove",function(e){
                        if(!$(e.target).parents(".allowmove").size()){
                            e.preventDefault()
                        }
                    }
                );
            });
    </script>
</div>
