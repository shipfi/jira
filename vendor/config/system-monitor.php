<?php
return [
    //生成消息的模拟用户
    "fake_user"=>[
        'usercode'=>'ydsp_user_code',
        'openid'=>'ydsp_user_openid',
        'userguid'=>'ydsp_user_guid'
    ],

    //消息监控异常，配置邮件发送对象
    "monitor_mail"=>[],

    //线上调试时，配置指定租户
    "debug_tenant"=>[
        [
            "tenant_id"=>"zhangl",
            "tenant_name"=>"自动化测试_ERP307",
            "is_enable"=>"1"
        ]
    ],

    //检测间隔，单位：分钟
    "monitor_interval" => 12,

    //配置报表短信发送对象
    "rp_sms_to" => [
        '15827158090' //yzh
    ],

    //配置erp短信发送对象
    "erp_sms_to" => [
        '15927348126' //meil
    ],

    //配置消息短信发送对象
    "wf_sms_to" => [
        //'13986211671',//zhangl
        //'15527689013',//jiangwb
        //'18627801592',//yangz
        '13554495689',//jiangyy
        //'15172378545' //yanw
    ],
];