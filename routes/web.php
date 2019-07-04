<?php

use Laravel\Lumen\Routing\Router;

/** @var Router $router */

$router->get("/", "ExampleController@index");

$router->get('/wechat/loginMock', "WechatMassMessage\ClientController@loginMock");

$router->group(["middleware" => "dotnet"], function () use ($router) {
    // 行政区划数据接口
    $router->group(["prefix" => "regions"], function () use ($router) {
        $router->post("/getSubRegions", "RegionController@getSubRegions");
        $router->post("/getRegionsTree", "RegionController@getRegionsTree");
    });

    $router->group(["prefix" => "statistics"], function () use ($router) {
        // 项目医院相关接口
        $router->get("getProjectHospitalRequireContent", "StatisticsController@getProjectHospitalRequireContent");
        $router->get("getProjectHospitalLists", "StatisticsController@getProjectHospitalLists");
        $router->post("projectHospitalTask", "StatisticsController@projectHospitalTask");

        // 工单相关接口
        $router->get("getWorkOrderRequireContent", "StatisticsController@getWorkOrderRequireContent");
        $router->get("getWorkOrderLists", "StatisticsController@getWorkOrderLists");
        $router->post("workOrderTask", "StatisticsController@workOrderTask");

        // 医生相关接口
        $router->get("getDoctorRequireContent", "StatisticsController@getDoctorRequireContent");
        $router->get("getDoctorLists", "StatisticsController@getDoctorLists");
        $router->post("doctorTask", "StatisticsController@doctorTask");

        // 获取城市区县及下载相关接口
        $router->post("getCity", "StatisticsController@getCity");
        $router->post("getDistrict", "StatisticsController@getDistrict");
        $router->get("download/{taskId}", "StatisticsController@download");
    });

    $router->group(["prefix" => "project"], function () use ($router) {
        $router->post("getProjectLeaders", "ProjectController@getProjectLeaders");
        $router->post("getJoinedProjects", "ProjectController@getJoinedProjects");
    });

    $router->group(["prefix" => "wechat"], function () use ($router) {
    });

    // 微信消息群发接口
    $router->group(["prefix" => "wechatMassMessage", "namespace" => "WechatMassMessage"], function () use ($router) {
        // 模板相关接口
        $router->post("getTemplates", "TemplateController@getTemplates");
        $router->post("createTemplate", "TemplateController@createTemplate");
        $router->post("modifyTemplate", "TemplateController@modifyTemplate");
        $router->post("enableTemplate", "TemplateController@enableTemplate");
        $router->post("disableTemplate", "TemplateController@disableTemplate");
        $router->post("previewTemplate", "TemplateController@previewTemplate");

        // 批次相关接口
        $router->post("getBatch", "BatchController@getBatch");
        $router->post("createBatch", "BatchController@createBatch");
        $router->post("submitBatch", "BatchController@submitBatch");
        $router->post("rejectBatch", "BatchController@rejectBatch");
        $router->post("approveBatch", "BatchController@approveBatch");
        $router->post("executeBatch", "BatchController@executeBatch");
        $router->post("resendFailedMessages", "BatchController@resendFailedMessages");
        $router->post("getCreatorBatches", "BatchController@getCreatorBatches");
        $router->post("getAuditorBatches", "BatchController@getAuditorBatches");
        $router->post("modifyBatchTaskContent", "BatchController@modifyBatchTaskContent");

        // 微信账号接口
        $router->post("getWechatContacts", "ContactController@getWechatContacts");
        $router->post("getWechatLoginState", "ClientController@getWechatLoginState");
        $router->post("getWechatLoginQrcode", "ClientController@getWechatLoginQrcode");

        /**
         * @deprecated
         */
        $router->post("getProjectFriends", "ContactController@getWechatContacts");
    });
});

$router->group(["middleware" => "scrm"], function () use ($router) {
    //系统菜单相关接口
    $router->group(['prefix' => 'system/menu', 'namespace' => 'System'], function () use ($router) {
        $router->post("getUserMenuList", "MenuController@getUserMenuList");
        $router->post("getMenuList", "MenuController@getMenuList");
        $router->post("addMenu", "MenuController@addMenu");
        $router->post("editMenu", "MenuController@editMenu");
        $router->post("delMenu", "MenuController@delMenu");
        $router->post("getRoleMenuList", "MenuController@getRoleMenuList");
        $router->post("addRoleMenu", "MenuController@addRoleMenu");
        $router->post("updateMenuList", "MenuController@updateMenuList");
    });
    //系统角色相关接口
    $router->group(['prefix' => 'system/role', 'namespace' => 'System'], function () use ($router) {
        $router->post("getUserRoleList", "RoleController@getUserRoleList");
        $router->post("getRoleList", "RoleController@getRoleList");
        $router->post("addRole", "RoleController@addRole");
        $router->post("editRole", "RoleController@editRole");
        $router->post("delRole", "RoleController@delRole");
        $router->post("addUserRole", "RoleController@addUserRole");
        $router->post("getRoleById", "RoleController@getRoleById");
    });
    //系统用户相关接口
    $router->group(['prefix' => 'system/user', 'namespace' => 'System'], function () use ($router) {
        $router->post("getUserList", "UserController@getUserList");
        $router->post("getRepList", "UserController@getRepList");
        $router->post("getUserRepList", "UserController@getUserRepList");
        $router->post("addUserRep", "UserController@addUserRep");
        $router->post("getUserById", "UserController@getUserById");
        $router->post("getUserInfo", "UserController@getUserInfo");
        $router->post("changeUserRep", "UserController@changeUserRep");
        $router->post("loginOut", "UserController@loginOut");
    });
    //系统权限相关接口
    $router->group(['prefix' => 'system/permission', 'namespace' => 'System'], function () use ($router) {
        $router->post("getPermissionList", "PermissionController@getPermissionList");
        $router->post("addPermission", "PermissionController@addPermission");
        $router->post("editPermission", "PermissionController@editPermission");
        $router->post("delPermission", "PermissionController@delPermission");
        $router->post("addRolePermission", "PermissionController@addRolePermission");
    });
});
