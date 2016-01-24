<?php
return [
    
    'p_tenants'=>[
        [
            'tenant_id'=>$this->tenantDb,
            'tenant_name'=>'test tenant db',
            'tenant_code'=>$this->tenantDb,
            'db_name'=>$this->tenantDb,
            'instance_id'=>1
        ], //默认插入一条租户库
        [
        'tenant_id'=>$this->configDb,
        'tenant_name'=>'test config db',
        'tenant_code'=>$this->configDb,
        'db_name'=>$this->configDb,
        'instance_id'=>1
        ] //增加一个配置库的配置，以支持multi_query
    ],
    
    'p_instance'=>[
        [
            'instance_id'=>1,
            'host'=>$this->testDbHost,
            'port'=>$this->testDbPort,
            'user_name'=>$this->testDbUser,
            'password'=>$this->testDbPasswd,
        ]
    ]
];