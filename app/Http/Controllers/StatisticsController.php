<?php /** @noinspection ALL */

namespace App\Http\Controllers;

use App\Http\ResponseStatus;
use App\Http\ResponseWrapper;
use App\Models\Statistics\StatisticsCron;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use App\Services\Statistics\StatisticsService;

class StatisticsController extends Controller
{
    /**
     * 获取医院统计所需内容
     *
     * @param Request $request
     * @return array
     */
    public function getProjectHospitalRequireContent(Request $request)
    {
        $taskName = "projectHospital";

        $operatorId = $request->attributes->get('user_id');

        $project = StatisticsService::newInstance()->getProject();

        $province = StatisticsService::newInstance()->getProvince();

        $isSubmit = StatisticsService::newInstance()->isSubmit($operatorId, $taskName);

        return ResponseWrapper::success([
            "project" => $project,
            "province" => $province,
            "is_submit" => $isSubmit
        ]);
    }

    /**
     * 获取医院统计任务列表
     *
     * @param Request $request
     * @return array
     */
    public function getProjectHospitalLists(Request $request)
    {
        $taskName = "projectHospital";
        // 分页
        $operatorId = $request->attributes->get('user_id');

        $limit = 10;
        if ($request->has('page')) {
            $page = (int)$request->input('page');
        } else {
            $page = 1;
        }

        $total = StatisticsService::newInstance()->getTaskCount($operatorId, $taskName);

        $tasks = StatisticsService::newInstance()->getTaskList($operatorId, $taskName, $page, $limit);

        return ResponseWrapper::success([
            "tasks" => $tasks,
            "total" => $total,
            "limit" => $limit,
        ]);
    }

    /**
     * 获取市
     *
     * @param Request $request
     * @return array
     */
    public function getCity(Request $request)
    {

        // 数据验证
        $validator = Validator::make($request->all(), [
            'province_id' => 'required|integer',
        ], [
            "province_id.*" => "请输入正确的省id",
        ]);

        if ($validator->fails()) {
            return ResponseWrapper::invalid($validator->errors()->first());
        }

        $provinceId = $request->input('province_id');

        $cityRes = DB::connection('dotnet')
            ->table('met_sys_region')
            ->where('regionGrade', 'City')
            ->where('parentRegionId', $provinceId)
            ->select('nameZH', 'regionId')
            ->get();

        $cityArr = [];

        foreach ($cityRes as $val) {
            $row['city_id'] = $val->regionId;
            $row['city_name'] = $val->nameZH;

            $cityArr[] = $row;
        }

        return ResponseWrapper::success(["city" => $cityArr]);
    }

    /**
     * 获取区
     *
     * @param Request $request
     * @return array
     */
    public function getDistrict(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'city_id' => 'required|integer',
        ], [
            "province_id.*" => "请输入正确的城市id",
        ]);

        if ($validator->fails()) {
            return ResponseWrapper::invalid($validator->errors()->first());
        }

        $cityId = $request->input('city_id');

        $districtRes = DB::connection('dotnet')
            ->table('met_sys_region')
            ->where('regionGrade', 'District')
            ->where('parentRegionId', $cityId)
            ->select('nameZH', 'regionId')
            ->get();


        $districtArr = [];

        foreach ($districtRes as $val) {
            $row['district_id'] = $val->regionId;
            $row['district_name'] = $val->nameZH;

            $districtArr[] = $row;
        }

        return ResponseWrapper::success(["district" => $districtArr]);
    }

    /**
     * 获取工单统计所需内容
     *
     * @param Request $request
     * @return array
     */
    public function getWorkOrderRequireContent(Request $request)
    {
        $taskName = "workOrder";

        $operatorId = $request->attributes->get('user_id');

        $project = StatisticsService::newInstance()->getProject();

        $province = StatisticsService::newInstance()->getProvince();

        $communicationStatus = StatisticsService::newInstance()->getCommunicationStatus();

        $taskType = [
            ["task_type" => "Doctor", "task_name" => "医生工单"],
            ["task_type" => "Mining", "task_name" => "挖掘工单"],
            ["task_type" => "Member", "task_name" => "用户工单"],
            ["task_type" => "Customer", "task_name" => "代表工单"],
            ["task_type" => "Hospital", "task_name" => "医院工单"],
        ];

        $isSubmit = StatisticsService::newInstance()->isSubmit($operatorId, $taskName);

        return ResponseWrapper::success([
            "project" => $project,
            "province" => $province,
            "communication_status" => $communicationStatus,
            "task_type" => $taskType,
            "is_submit" => $isSubmit
        ]);
    }

    /**
     * 获取工单统计任务列表
     *
     * @param Request $request
     * @return array
     */
    public function getWorkOrderLists(Request $request)
    {
        $taskName = "workOrder";
        // 分页
        $operatorId = $request->attributes->get('user_id');

        $limit = 10;
        if ($request->has('page')) {
            $page = (int)$request->input('page');
        } else {
            $page = 1;
        }

        $total = StatisticsService::newInstance()->getTaskCount($operatorId, $taskName);

        $tasks = StatisticsService::newInstance()->getTaskList($operatorId, $taskName, $page, $limit);

        return ResponseWrapper::success([
            "tasks" => $tasks,
            "total" => $total,
            "limit" => $limit,
        ]);
    }

    /**
     * 生成项目医院统计任务
     *
     * @param Request $request
     * @return array
     */
    public function projectHospitalTask(Request $request)
    {
        // 数据验证
        $validator = Validator::make($request->all(), [
            "project_code" => "required|array",
            "external_code" => "string",
            "hospital_name" => "string",
            "province_id" => "integer",
            "city_id" => "integer",
            "district_id" => "integer",
            "representative_name" => "string",
            "area" => "string",
        ], [
            "project_code.*" => "请输入正确的项目编码",
            "external_code.*" => "请输入正确的医院编号",
            "hospital_name.*" => "请输入正确的医院名称",
            "province_id.*" => "请输入正确的省id",
            "city_id.*" => "请输入正确的市id",
            "district_id.*" => "请输入正确的区id",
            "representative_name.*" => "请输入正确的DS姓名",
            "area.*" => "请输入正确的区域",
        ]);

        if ($validator->fails()) {
            return ResponseWrapper::invalid($validator->errors()->first());
        }

        $taskName = "projectHospital";

        // 操作人id
        $operatorId = $request->attributes->get('user_id');

        // 项目编号
        $projectCode = (array)$request->input('project_code');

        $projectCodeStr = "";

        foreach ($projectCode as $val) {
            $projectCodeStr .= "'" . $val . "',";
        }

        $projectCodeStr = rtrim($projectCodeStr, ',');

        // 首先判断该用户是否能够创建任务 (规则：一个统计功能只能在统计周期内创建一个，必须等上一个统计任务执行完毕后才能创建新统计任务)
        // 任务名
        $checkTaskRes = StatisticsCron::where('task_name', $taskName)
            ->where('operator_id', $operatorId)
            ->whereIn('status', [0, 1])
            ->select('id')
            ->first();

        if ($checkTaskRes !== null) {
            return ResponseWrapper::failure(ResponseStatus::TASK_NO_COMPLETED);
        }

        // 医院编号
        $externalCode = (string)$request->input('external_code');
        // 医院名称
        $hospitalName = (string)$request->input('hospital_name');
        // 省
        $provinceId = (int)$request->input('province_id');
        // 市
        $cityId = (int)$request->input('city_id');
        // 区
        $districtId = (int)$request->input('district_id');
        // DS
        $representativeName = (string)$request->input('representative_name');
        // 区域
        $area = (string)$request->input('area');

        // 拼接SQL
        $execSql = "";

        $execSql .= "SELECT";
        $execSql .= " h.HospitalId,p.ProjectName 项目,hc.ExternalCode 医院编号,hn.HospitalName 医院,h.ProvinceName 省,h.CityName 市,h.DistrictName 区,h.hospitalTypeKey 医院类型,h.HospitalGrade 医院级别,r.RepresentativeName 市场DS,group_concat(DISTINCT b.BrandName ORDER BY b.BrandCode separator '/') 品牌,group_concat(DISTINCT concat(b.BrandName,':',phb.TargetDoctorCount) ORDER BY b.BrandCode separator '/') 医生招募指标,group_concat(DISTINCT concat(b.BrandName,':',CASE hb.HasMedicine WHEN 1 THEN 'Y' WHEN 0 THEN 'N' END) ORDER BY b.BrandCode separator '/') 是否有药,ph.Area 区域,ph.Memo 项目医院备注,hc.Remark 客户备注";
        $execSql .= " FROM v_data_hospital h";
        $execSql .= " INNER JOIN ent_data_project p ON p.ProjectCode IN ($projectCodeStr)";
        $execSql .= " INNER JOIN ent_data_project_hospital ph ON p.ProjectId = ph.ProjectId AND h.HospitalId = ph.HospitalId AND ph.IsDeleted = 0";
        if ($provinceId) {
            $execSql .= " AND h.ProvinceId = $provinceId";
        }
        if ($cityId) {
            $execSql .= " AND h.CityId = $cityId";
        }
        if ($districtId) {
            $execSql .= " AND h.DistrictId = $districtId";
        }
        if ($area) {
            $execSql .= " AND ph.area = '$area'";
        }

        $execSql .= " LEFT JOIN ent_data_project_hospital_brand phb ON ph.ProjectHospitalId = phb.ProjectHospitalId";
        $execSql .= " LEFT JOIN ent_data_brand b ON phb.BrandId = b.BrandId";
        $execSql .= " LEFT JOIN ent_data_hospital_brand hb ON b.BrandId = hb.BrandId AND h.HospitalId = hb.HospitalId";
        $execSql .= " LEFT JOIN ent_data_representative_project_hospital rph ON ph.ProjectHospitalId = rph.ProjectHospitalId";
        $execSql .= " LEFT JOIN ent_data_representative r ON rph.RepresentativeId = r.RepresentativeId";
        $execSql .= " LEFT JOIN ent_ext_hospital_client hc ON h.HospitalId = hc.HospitalId AND p.ClientId = hc.ClientId";
        $execSql .= " LEFT JOIN v_data_hospital_name hn ON ifnull(hc.HospitalNameId,h.DefaultHospitalNameId) = hn.HospitalNameId";
        $execSql .= " WHERE 1=1";

        if ($representativeName) {
            $execSql .= " AND r.RepresentativeName = '$representativeName'";
        }

        if ($hospitalName) {
            $execSql .= " AND hn.HospitalName LIKE '%$hospitalName%'";
        }

        if ($externalCode) {
            $execSql .= " AND hc.ExternalCode = '$externalCode'";
        }

        $execSql .= " GROUP BY h.HospitalId,p.ProjectId";

//        $fileName = 'projectHospital_' . $projectCodeStr . '_' . Carbon::now()->toDateString() . '_' . $operatorId . '_' . Carbon::now()->timestamp;
        $fileName = 'XMYY_' . $projectCodeStr . '_' . Carbon::now()->timestamp;

        $insertData = [
            'task_name' => $taskName,
            'exec_sql' => $execSql,
            'file_name' => $fileName,
            'operator_id' => $operatorId,
            'created_at' => Carbon::now()
        ];

        StatisticsCron::insert($insertData);

        return ResponseWrapper::success();
    }

    /**
     * 生成工单统计任务
     *
     * @param Request $request
     * @return array
     */
    public function workOrderTask(Request $request)
    {
        // 验证数据
        $validator = Validator::make($request->all(), [
            "project_code" => "required|array",
            "exec_date" => "required|array",
            "task_code" => "string",
            "doctor_no" => "string",
            "doctor_name" => "string",
            "user_name" => "string",
            "representative_name" => "string",
            "external_code" => "string",
            "hospital_name" => "string",
            "purpose_name" => "string",
            "communication_status" => "array",
            "task_type" => "string",
            "memo" => "string",
            "is_connected" => "integer",
            "is_effective" => "integer",
            "is_success" => "integer",
            "is_phone_acceptance" => "integer",
            "is_refer_product" => "integer",
            "is_refer_key_message" => "integer",
        ], [
            "project_code.*" => "请输入正确的项目编码",
            "exec_date.*" => "请输入正确的起始时间",
            "task_code.*" => "请输入正确的工单编号",
            "doctor_no.*" => "请输入正确的医生编号",
            "doctor_name.*" => "请输入正确的医生姓名",
            "user_name.*" => "请输入正确的DS",
            "representative_name.*" => "请输入正确的专员",
            "external_code.*" => "请输入正确的医院编号",
            "hospital_name.*" => "请输入正确的医院名称",
            "communication_status.*" => "请输入正确的沟通状态",
            "task_type.*" => "请输入正确的工单类型",
            "memo.*" => "请输入正确的工单备注",
            "is_connected.*" => "请选择正确的是否接通电话",
            "is_effective.*" => "请选择正确的是否有效沟通",
            "is_success.*" => "请选择正确的是否成功沟通",
            "is_phone_acceptance.*" => "请选择正确的电话是否知情同意",
            "is_refer_product.*" => "请选择正确的是否提及产品",
            "is_refer_key_message.*" => "请选择正确的是否提价关键信息",
        ]);

        if ($validator->fails()) {
            return ResponseWrapper::invalid($validator->errors()->first());
        }

        $taskName = "workOrder";

        // 操作人id
        $operatorId = $request->attributes->get('user_id');

        // 项目编号
        $projectCode = (array)$request->input('project_code');
        $projectCodeStr = "";
        foreach ($projectCode as $val) {
            $projectCodeStr .= "'" . $val . "',";
        }
        $projectCodeStr = rtrim($projectCodeStr, ',');

        // 执行时间
        $execDate = (array)$request->input('exec_date');

        // 首先判断该用户是否能够创建任务 (规则：一个统计功能只能在统计周期内创建一个，必须等上一个统计任务执行完毕后才能创建新统计任务)
        $checkTaskRes = StatisticsCron::where('task_name', $taskName)
            ->where('operator_id', $operatorId)
            ->whereIn('status', [0, 1])
            ->select('id')
            ->first();

        if ($checkTaskRes !== null) {
            return ResponseWrapper::failure(['fail', '操作失败'], '存在相同任务尚未完成，请等待任务完成后再创建新任务');
        }

        // 工单编号
        $taskCode = (string)trim($request->input('task_code'));
        // 医生编号
        $doctorNo = (string)trim($request->input('doctor_no'));
        // 医生姓名
        $doctorName = (string)trim($request->input('doctor_name'));
        // DS
        $userName = (string)trim($request->input('user_name'));
        // 专员
        $representativeName = (string)trim($request->input('representative_name'));
        // 医院编号
        $externalCode = (string)trim($request->input('external_code'));
        // 医院名称
        $hospitalName = (string)trim($request->input('hospital_name'));
        // 工单目的
        $purposeName = (string)trim($request->input('purpose_name'));
        // 沟通状态
        $communicationStatus = (array)$request->input('communication_status');
        // 工单类型
        $taskType = (string)$request->input('task_type');
        // 工单备注
        $memo = (string)trim($request->input('memo'));
        // 是否接通电话
        $isConnected = (int)trim($request->input('is_connected'));
        // 是否有效沟通
        $isEffective = (int)trim($request->input('is_effective'));
        // 是否成功沟通
        $isSuccess = (int)trim($request->input('is_success'));
        // 电话是否知情同意
        $isPhoneAcceptance = (int)trim($request->input('is_phone_acceptance'));
        // 是否提及产品
        $isReferProduct = (int)trim($request->input('is_refer_product'));
        // 是否提价关键信息
        $isReferKeyMessage = (int)trim($request->input('is_refer_key_message'));

        // 拼接SQL
        $execSql = "";

        $execSql .= "SELECT";

        $execSql .= " trp.taskrecordprojectid 工单项目记录ID,tr.TaskRecordId 工单记录ID,t.TaskCode 工单编号,p.ProjectName 工单项目,d.DoctorNo 医生编号,REPLACE (REPLACE ( REPLACE ( d.DoctorName, CHAR ( 10 ), ' ' ), CHAR ( 13 ), ' ' ),CHAR ( 9 ),' ' ) 医生姓名,u.UserName DS,r.RepresentativeName 专员,p.ProjectName 项目,hc.ExternalCode 医院编号,hn.HospitalName 医院,h.ProvinceName 省,h.CityName 市,DistrictName 区,date_format( tr.ExecDate, '%Y-%m-%d %H:%i:%s' ) 执行日期,CASE tr.ChannelKey WHEN 'Mobile' THEN '手机' WHEN 'Telephone' THEN '座机' WHEN 'Wechat' THEN '微信' WHEN 'SMS' THEN '短信' END 沟通渠道,group_concat( DISTINCT pur.PurposeName SEPARATOR '/' ) 工单目的,CASE WHEN ph.IsDeleted = 0 THEN 'Y' WHEN ph.IsDeleted = 1 THEN 'N' END 是否项目医院内,CASE WHEN pd.IsDeleted = 0 THEN 'Y' WHEN pd.IsDeleted = 1 THEN 'N' END 是否项目内医生,p1.ProjectName 实际执行项目,cs.CommunicationStatusName 沟通状态,REPLACE ( REPLACE ( REPLACE ( tr.Memo, CHAR ( 10 ), ' ' ) , CHAR ( 13 ), ' ' ) , CHAR ( 9 ) , ' ' ) 工单备注,CASE t.TaskType WHEN 'Mining' THEN '挖掘' WHEN 'Doctor' THEN '医生' WHEN 'Customer' THEN '线下代表' WHEN 'Member' THEN '注册用户' END 工单类型,cs.IsConnected 是否电话接通,cs.IsEffective 是否有效沟通,cs.IsSuccess 是否成功沟通,trop.Duration / 1000 通话时长（秒）,CASE tr.CooperationType WHEN 'Positive' THEN '态度积极' WHEN 'Insipid' THEN '态度一般' WHEN 'Indifference' THEN '态度冷淡' WHEN 'Resist' THEN '态度抵触' END 配合度,CASE tr.AcceptanceType WHEN 'NoAcceptance' THEN '不接受' WHEN 'PartialAcceptance' THEN '部分接受' WHEN 'BasicAcceptance' THEN '基本接受' WHEN 'CompleteAcceptance' THEN '完全接受' END 医生接受度,CASE tr.IsPhoneAcceptance WHEN 1 THEN 1 ELSE 0 END 电话是否知情同意,CASE tr.IsReferProduct WHEN 1 THEN 1 ELSE 0 END 是否提及产品,CASE tr.IsReferKeyMessage WHEN 1 THEN 1 ELSE 0 END 是否提及关键信息,group_concat( DISTINCT CASE WHEN de.DemandType IN ( 'lcgjl' ) THEN de.DemandName END ORDER BY de.DemandName SEPARATOR '/' ) 临床工具类,group_concat( DISTINCT CASE WHEN de.DemandType IN ( 'lcgzl' ) THEN de.DemandName END ORDER BY de.DemandName SEPARATOR '/' ) 临床关注类,group_concat( DISTINCT sa.SalesAnalysisName ORDER BY sa.SalesAnalysisName SEPARATOR '/' ) 销量影响因素";

        $execSql .= " FROM ent_bpm_task_record tr";
        $execSql .= " INNER JOIN ent_bpm_task t ON tr.TaskId = t.TaskId";
        if ($taskCode) {
            $execSql .= " AND t.TaskCode = '$taskCode'";
        }

        if ($taskType) {
            $execSql .= " AND t.TaskType = '$taskType'";
        }
        $execSql .= " LEFT JOIN ent_bpm_task_record_project trp ON trp.TaskRecordId = tr.TaskRecordId";
        $execSql .= " LEFT JOIN ent_data_project p1 ON p1.ProjectId = trp.ProjectId";
        $execSql .= " LEFT JOIN ent_bpm_communication_status cs ON trp.CommunicationStatusId = cs.CommunicationStatusId";

        if ($memo) {
            $execSql .= " AND tr.memo LIKE '%$memo%'";
        }

        if ($isPhoneAcceptance != 0) {
            // 0=>全部 1->是 2=>否
            if ($isPhoneAcceptance == 1) {
                $execSql .= " AND tr.IsPhoneAcceptance = 1";
            } else if ($isPhoneAcceptance == 2) {
                $execSql .= " AND (tr.IsPhoneAcceptance IS NULL OR tr.IsPhoneAcceptance <> 1)";
            }
        }

        if ($isReferProduct != 0) {
            // 0=>全部 1->是 2=>否
            if ($isReferProduct == 1) {
                $execSql .= " AND tr.IsReferProduct = 1";
            } else if ($isReferProduct == 2) {
                $execSql .= " AND (tr.IsReferProduct IS NULL OR tr.IsReferProduct <> 1)";
            }
        }

        if ($isReferKeyMessage != 0) {
            // 0=>全部 1->是 2=>否
            if ($isReferKeyMessage == 1) {
                $execSql .= " AND tr.IsReferKeyMessage = 1";
            } else if ($isReferKeyMessage == 2) {
                $execSql .= " AND (tr.IsReferKeyMessage IS NULL OR tr.IsReferKeyMessage <> 1)";
            }
        }

        $execSql .= " LEFT JOIN ent_bpm_task_mining_reference tm ON t.TaskId = tm.TaskId AND tm.LogicName = 'Hospital'";
        $execSql .= " LEFT JOIN v_data_doctor_hospital_department dhd ON tr.DoctorHospitalDepartmentId = dhd.DoctorHospitalDepartmentId";
        $execSql .= " LEFT JOIN ent_data_representative r ON t.RepresentativeId = r.RepresentativeId";
        $execSql .= " LEFT JOIN ent_sys_user u ON tr.OperationUserId = u.UserId";
        $execSql .= " LEFT JOIN ent_bpm_task_record_project_purpose trppur ON trp.TaskRecordProjectId = trppur.TaskRecordProjectId";
        $execSql .= " INNER JOIN ent_bpm_task_record_of_phone trop ON tr.TaskRecordId = trop.TaskRecordId";
        $execSql .= " INNER JOIN ent_data_project p ON t.ProjectId = p.ProjectId AND p.ProjectCode IN ($projectCodeStr)";
        $execSql .= " LEFT JOIN v_data_hospital_department hd ON dhd.HospitalDepartmentId = hd.HospitalDepartmentId";
        $execSql .= " LEFT JOIN v_data_hospital h ON ifnull( hd.HospitalId, tm.TargetId ) = h.HospitalId";
        $execSql .= " LEFT JOIN ent_data_project_hospital ph ON ph.HospitalId = h.HospitalId and ph.ProjectId = p1.ProjectId";
        $execSql .= " LEFT JOIN ent_ext_hospital_client hc ON hc.HospitalId = h.HospitalId AND hc.ClientId = p.ClientId";
        $execSql .= " LEFT JOIN v_data_hospital_name hn ON ifnull( h.DefaultHospitalNameId, h.DefaultHospitalNameId ) = hn.HospitalNameId";
        $execSql .= " LEFT JOIN v_data_department dpt ON hd.DepartmentId = dpt.DepartmentId";
        $execSql .= " LEFT JOIN v_data_doctor d ON dhd.DoctorId = d.DoctorId";
        $execSql .= " LEFT JOIN ent_data_project_doctor pd ON pd.DoctorId = d.DoctorId and pd.ProjectId = p1.ProjectId";
        $execSql .= " LEFT JOIN met_bpm_purpose pur ON trppur.PurposeKey = pur.PurposeKey";
        $execSql .= " LEFT JOIN ent_sys_material sm ON trop.MaterialId = sm.MaterialId";
        $execSql .= " LEFT JOIN met_sys_material_target mt ON sm.MaterialTargetKey = mt.MaterialTargetKey";
        $execSql .= " LEFT JOIN ent_bpm_task_demand tde ON t.TaskId = tde.TaskId";
        $execSql .= " LEFT JOIN met_data_demand de ON de.DemandKey = tde.DemandKey";
        $execSql .= " LEFT JOIN ent_bpm_task_record_project_sales_analysis trpsa ON trpsa.TaskRecordProjectId = trp.TaskRecordProjectId";
        $execSql .= " LEFT JOIN ent_bpm_sales_analysis sa ON sa.SalesAnalysisId = trpsa.SalesAnalysisId";
        $execSql .= " WHERE ExecDate >= '$execDate[0] 00:00:00' AND ExecDate <= '$execDate[1] 23:59:59'";

        if ($communicationStatus) {
            $communicationStatusStr = "";
            foreach ($communicationStatus as $val) {
                $communicationStatusStr .= "'" . $val . "',";
            }

            $communicationStatusStr = rtrim($communicationStatusStr, ',');
            $execSql .= " AND cs.CommunicationStatusId IN ($communicationStatusStr)";
        }


        if ($isConnected != 0) {
            // 0=>全部 1=>接通 2=>未接通
            if ($isConnected == 1) {
                $execSql .= " AND cs.IsConnected = 1";
            } else if ($isConnected == 2) {
                $execSql .= " AND cs.IsConnected = 0";
            }

        }

        if ($isEffective != 0) {
            // 0=>全部 1->有效 2=>无效
            if ($isEffective == 1) {
                $execSql .= " AND cs.IsEffective = 1";
            } else if ($isEffective == 2) {
                $execSql .= " AND cs.IsEffective = 0";
            }

        }

        if ($isSuccess != 0) {
            // 0=>全部 1->成功 2=>失败
            if ($isSuccess == 1) {
                $execSql .= " AND cs.IsSuccess = 1";
            } else if ($isSuccess == 2) {
                $execSql .= " AND cs.IsSuccess = 0";
            }
        }

        if ($doctorNo) {
            $execSql .= " AND d.DoctorNo = $doctorNo";
        }
        if ($doctorName) {
            $execSql .= " AND d.DoctorName = '$doctorName'";
        }
        if ($userName) {
            $execSql .= " AND u.UserName = '$userName'";
        }
        if ($representativeName) {
            $execSql .= " AND r.RepresentativeName = '$representativeName'";
        }
        if ($externalCode) {
            $execSql .= " AND hc.ExternalCode = $externalCode";
        }
        if ($hospitalName) {
            $execSql .= " AND hn.HospitalName Like '%$hospitalName%'";
        }
        if ($purposeName) {
            $purposeNameArr = explode('/', $purposeName);
            $purposeNameStr = "";
            foreach ($purposeNameArr as $val) {
                $purposeNameStr .= "'" . $val . "',";
            }
            $purposeNameStr = rtrim($purposeNameStr, ',');
            $execSql .= " AND pur.PurposeName IN ($purposeNameStr)";
        }

        $execSql .= " GROUP BY tr.TaskRecordId,t.TaskCode,u.UserName,p.ProjectName,hn.HospitalName,tr.ExecDate,tr.ChannelKey,cs.CommunicationStatusName,sm.MaterialFileName,REPLACE ( REPLACE ( tr.Memo, CHAR ( 10 ), ' ' ), CHAR ( 13 ), ' ' ),t.TaskType";

//        $fileName = 'workOrder_' . $projectCodeStr . '_' . Carbon::now()->toDateString() . '_' . $operatorId . '_' . Carbon::now()->timestamp;
        $fileName = 'GD_' . $projectCodeStr . '_' . $execDate[0] . '~' . $execDate[1] . '_' . Carbon::now()->timestamp;

        $insertData = [
            'task_name' => $taskName,
            'exec_sql' => $execSql,
            'file_name' => $fileName,
            'operator_id' => $operatorId,
            'created_at' => Carbon::now()
        ];

        StatisticsCron::insert($insertData);

        return ResponseWrapper::success();
    }

    /**
     * 下载excel
     *
     * @param $taskId
     * @return array
     */
    public function download(int $taskId)
    {
        $fileRes = StatisticsCron::select(['file_name'])
            ->where('id', $taskId)
            ->first();

        $fileName = $fileRes->file_name . '.csv';
        $filePath = "/tmp/export/{$fileName}";

        if (file_exists($filePath)) {
            return response()->download($filePath, $fileName);
        }

        return response('File not found', 404);
    }

    /**
     * 生成医生统计任务
     *
     * @param Request $request
     * @return array
     */
    public function doctorTask(Request $request)
    {
        // 验证数据
        $validator = Validator::make($request->all(), [
            "project_code" => "required|array",
            "external_code" => "string",
            "hospital_name" => "string",
            "province_id" => "integer",
            "city_id" => "integer",
            "market_representative_name" => "string",
            "doctor_no" => "string",
            "doctor_name" => "string",
            "lost_status" => "integer",
            "is_deleted" => "integer",
            "representative_name" => "string",
            "is_add_wechat" => "integer",
            "phone_communicate_date" => "array",
            "wechat_communicate_date" => "array",
            "add_wechat_date" => "array",
        ], [
            "project_code.*" => "请输入正确的项目编码",
            "external_code.*" => "请输入正确的医院编号",
            "hospital_name.*" => "请输入正确的医院名称",
            "province_id.*" => "请输入正确的市id",
            "city_id.*" => "请输入正确的区id",
            "market_representative_name.*" => "请输入正确的市场DS",
            "doctor_no.*" => "请输入正确的医生编号",
            "doctor_name.*" => "请输入正确的医生姓名",
            "lost_status.*" => "请选择正确的是否脱落",
            "is_deleted.*" => "请选择正确的是否项目内",
            "representative_name.*" => "请输入正确的主管DS",
            "is_add_wechat.*" => "请选择正确的是否加微信",
            "phone_communicate_date.*" => "请输入正确的电话沟通时间",
            "wechat_communicate_date.*" => "请输入正确的微信沟通时间",
            "add_wechat_date.*" => "请输入正确的加微信时间",
        ]);

        if ($validator->fails()) {
            return ResponseWrapper::invalid($validator->errors()->first());
        }

        // 任务名
        $taskName = "doctor";

        // 操作人id
        $operatorId = $request->attributes->get('user_id');

        // 项目编号
        $projectCode = (array)$request->input('project_code');
        $projectCodeStr = "";
        foreach ($projectCode as $val) {
            $projectCodeStr .= "'" . $val . "',";
        }
        $projectCodeStr = rtrim($projectCodeStr, ',');
        // 医院编号
        $externalCode = (string)trim($request->input('external_code'));
        // 医院名称
        $hospitalName = (string)trim($request->input('hospital_name'));
        // 省
        $provinceId = (int)$request->input('province_id');
        // 市
        $cityId = (int)$request->input('city_id');
        // 市场DS
        $marketRepresentativeName = (string)trim($request->input('market_representative_name'));
        // 医生编号
        $doctorNo = (string)trim($request->input('doctor_no'));
        // 医生姓名
        $doctorName = (string)trim($request->input('doctor_name'));
        // 是否脱落
        $lostStatus = (int)$request->input('lost_status');
        // 是否项目内
        $isDeleted = (int)$request->input('is_deleted');
        // 主管DS
        $representativeName = (string)trim($request->input('representative_name'));
        // 是否加微信
        $isAddWechat = (int)$request->input('is_add_wechat');
        // 电话沟通时间
        $phoneCommunicateDate = (array)$request->input('phone_communicate_date');
        // 微信沟通时间
        $wechatCommunicateDate = (array)$request->input('wechat_communicate_date');
        // 加微信时间
        $addWechatDate = (array)$request->input('add_wechat_date');

        $execSql = "";

        $execSql .= "SELECT";

        $execSql .= " h.HospitalId,p.ProjectCode 项目,hc.ExternalCode 医院编号,hn.HospitalName 医院,h.ProvinceName 省,h.CityName 市,dc.ExternalCode 客户编号,d.DoctorNo 医生编号,d.DoctorName 医生姓名,CASE WHEN pd.LostStatus = 'Active' THEN 0 ELSE 1 END 是否脱落,CASE pd.IsDeleted WHEN 0 THEN 1 WHEN 1 THEN 0 END 是否项目内,pd.DoctorGroup 医生分组,pd.DoctorPhase 阶段,de.DepartmentName 科室,r.RepresentativeName 市场DS,mr.RepresentativeName 主管DS,CASE WHEN wf.DoctorId <> '' THEN 1 ELSE 0 END 是否加微信,wf.RepresentativeName 微信好友,wf.`Status`  微信状态,DATE_FORMAT(wf.RelationEstablishDate,'%Y-%m-%d %H:%i:%s') 加微信时间,CASE m.`Status` WHEN 'Registered' THEN '已注册' WHEN 'Unregistered' THEN '未注册' WHEN 'Certified' THEN '已认证' WHEN 'Auditing' THEN '审核中' WHEN 'Interested' THEN '未注册' WHEN 'Refused' THEN '审核不通过' END 微网站状态,DATE_FORMAT(MIN(m.AddedDate),'%Y-%m-%d %H:%i:%s') 关注时间,DATE_FORMAT(MIN(m.CertifiedDate),'%Y-%m-%d %H:%i:%s') 认证时间,tr.Executed 外呼量,tr.IsConnected 接通量,tr.IsEffective 有效沟通量,tr.IsSuccess 成功沟通量,wc.Send 微信发送,wc.Recived 微信回复,DATE_FORMAT(lastcall.lastcall,'%Y-%m-%d %H:%i:%s') 末次电话成功沟通时间,DATE_FORMAT(lastwechat.lastsend,'%Y-%m-%d %H:%i:%s') 末次微信发送时间";

        $execSql .= " FROM v_data_doctor d";
        $execSql .= " INNER JOIN v_data_doctor_hospital_department dhd ON d.DoctorId = dhd.DoctorId";
        $execSql .= " INNER JOIN v_data_hospital_department hd ON dhd.HospitalDepartmentId = hd.HospitalDepartmentId";
        $execSql .= " INNER JOIN v_data_hospital h ON hd.HospitalId = h.HospitalId";

        if ($provinceId) {
            $execSql .= " AND h.ProvinceId = $provinceId";
        }

        if ($cityId) {
            $execSql .= " AND h.CityId = $cityId";
        }

        $execSql .= " INNER JOIN ent_data_project p ON p.ProjectCode IN ($projectCodeStr)";
        $execSql .= " LEFT JOIN ent_ext_hospital_client hc ON h.HospitalId = hc.HospitalId AND p.ClientId = hc.ClientId";
        $execSql .= " LEFT JOIN v_data_hospital_name hn ON ifnull(hc.HospitalNameId,h.DefaultHospitalNameId) = hn.HospitalNameId";
        $execSql .= " INNER JOIN ent_data_project_hospital ph ON p.ProjectId = ph.ProjectId AND h.HospitalId = ph.HospitalId AND ph.IsDeleted = 0";
        $execSql .= " LEFT JOIN ent_data_project_doctor pd ON p.ProjectId = pd.ProjectId AND d.DoctorId = pd.DoctorId";
        $execSql .= " LEFT JOIN ent_ext_doctor_client dc ON d.DoctorId = dc.DoctorId AND p.ClientId = dc.ClientId";
        $execSql .= " LEFT JOIN v_data_department de ON hd.DepartmentId = de.DepartmentId";
        $execSql .= " LEFT JOIN ( SELECT dr.DoctorId,rp.ProjectId,GROUP_CONCAT(distinct wfr.RepresentativeName ORDER BY wfr.RepresentativeName separator '/') RepresentativeName,GROUP_CONCAT(CASE wf.`Status` WHEN 'Normal' THEN '已加好友' WHEN 'Blocked' THEN '被拉黑' WHEN 'Pending' THEN '待通过' WHEN 'Deleted'  THEN '已删除' END ORDER BY wfr.RepresentativeName separator '/') `Status`,MIN(wf.RelationEstablishDate) RelationEstablishDate FROM ( SELECT DISTINCT rph.RepresentativeId,ph.ProjectId FROM ent_data_representative_project_hospital rph INNER JOIN ent_data_project_hospital ph ON rph.ProjectHospitalId = ph.ProjectHospitalId AND ph.IsDeleted = 0 ) rp INNER JOIN ent_data_representative wfr ON wfr.RepresentativeId = rp.RepresentativeId INNER JOIN ent_data_doctor_representative dr ON rp.RepresentativeId = dr.RepresentativeId INNER JOIN v_data_wechat_friend wf ON wf.DoctorRepresentativeId = dr.DoctorRepresentativeId AND wf.`Status` <> 'Pending' GROUP BY dr.DoctorId,rp.ProjectId ) wf ON wf.DoctorId = d.DoctorId AND wf.ProjectId = p.ProjectId";
        $execSql .= " LEFT JOIN ent_data_representative_project_hospital rph ON ph.ProjectHospitalId = rph.ProjectHospitalId";
        $execSql .= " LEFT JOIN ent_data_representative r ON rph.RepresentativeId = r.RepresentativeId";
        $execSql .= " LEFT JOIN ent_data_doctor_representative mdr ON d.MajorDoctorRepresentativeId = mdr.DoctorRepresentativeId";
        $execSql .= " LEFT JOIN ent_data_representative mr ON mdr.RepresentativeId = mr.RepresentativeId";
        $execSql .= " LEFT JOIN ent_cms_project_bundle pbu ON p.ProjectId = pbu.ProjectId";
        $execSql .= " LEFT JOIN ent_cms_site_bundle sb ON pbu.BundleId = sb.BundleId";
        $execSql .= " LEFT JOIN ent_cms_site s ON sb.SiteId = s.SiteId";
        $execSql .= " LEFT JOIN ent_crm_member_of_doctor mofd ON d.DoctorId = mofd.DoctorId";
        $execSql .= " LEFT JOIN ent_crm_member m ON mofd.MemberId = m.MemberId AND m.SiteId = s.SiteId AND m.`Status` = 'Certified'";
        $execSql .= " LEFT JOIN ( SELECT tr.DoctorHospitalDepartmentId,t.ProjectId,SUM(CASE WHEN tr.ExecDate IS NOT NULL THEN 1 ELSE 0 END) Executed,SUM(IsConnected) IsConnected,SUM(IsEffective) IsEffective,SUM(IsSuccess) IsSuccess FROM ent_bpm_task_record tr INNER JOIN ent_bpm_task t ON tr.TaskId = t.TaskId LEFT JOIN ent_bpm_communication_status cs ON tr.CommunicationStatusId = cs.CommunicationStatusId WHERE tr.DoctorHospitalDepartmentId <> '' AND tr.ExecDate IS NOT NULL GROUP BY tr.DoctorHospitalDepartmentId,t.ProjectId ) tr ON tr.DoctorHospitalDepartmentId = dhd.DoctorHospitalDepartmentId AND tr.ProjectId = p.ProjectId";
        $execSql .= " LEFT JOIN ( SELECT dr.DoctorId,rp.ProjectId,SUM(CASE wc.IsSend WHEN 1 THEN 1 END) Send,SUM(CASE wc.IsSend WHEN 0 THEN 1 END) Recived FROM ent_bpm_wechat_chat wc INNER JOIN v_data_wechat_friend wf ON wc.WechatFriendId = wf.WechatFriendId AND wf.DoctorRepresentativeId <> '' INNER JOIN ent_data_doctor_representative dr ON wf.DoctorRepresentativeId = dr.DoctorRepresentativeId INNER JOIN ( SELECT DISTINCT rph.RepresentativeId,ph.ProjectId FROM ent_data_representative_project_hospital rph INNER JOIN ent_data_project_hospital ph ON rph.ProjectHospitalId = ph.ProjectHospitalId AND ph.IsDeleted = 0 ) rp ON dr.RepresentativeId = rp.RepresentativeId WHERE wc.Content NOT LIKE '%我通过了你的&友验证%' AND wc.Content NOT LIKE '%请先发送朋友验证请求%' AND wc.Content NOT LIKE '%现在可以开始聊天了%' AND wc.content NOT LIKE '%撤回了一条消息%'";

        if ($wechatCommunicateDate) {
            $execSql .= " AND wc.ChatTime >= '$wechatCommunicateDate[0] 00:00:00' AND wc.ChatTime <= '$wechatCommunicateDate[1] 23:59:59'";
        }

        $execSql .= " GROUP BY dr.DoctorId,rp.ProjectId ) wc ON wc.DoctorId = d.DoctorId AND wc.ProjectId = p.ProjectId";
        $execSql .= " LEFT JOIN ( SELECT tr.DoctorHospitalDepartmentId,t.ProjectId,MAX(tr.ExecDate) lastcall
FROM ent_bpm_task_record tr INNER JOIN ent_bpm_task t ON tr.TaskId = t.TaskId INNER JOIN ent_bpm_communication_status cs ON tr.CommunicationStatusId = cs.CommunicationStatusId AND cs.IsSuccess = 1 WHERE tr.DoctorHospitalDepartmentId <> '' AND tr.ExecDate IS NOT NULL";

        if ($phoneCommunicateDate) {
            $execSql .= " AND tr.ExecDate >= '$phoneCommunicateDate[0] 00:00:00' AND tr.ExecDate <= '$phoneCommunicateDate[1] 23:59:59'";
        }

        $execSql .= " GROUP BY tr.DoctorHospitalDepartmentId,t.ProjectId ) lastcall ON lastcall.DoctorHospitalDepartmentId = dhd.DoctorHospitalDepartmentId AND lastcall.ProjectId = p.ProjectId";
        $execSql .= " LEFT JOIN ( SELECT dr.DoctorId,rp.ProjectId,MAX(wc.ChatTime) lastsend FROM ent_bpm_wechat_chat wc INNER JOIN v_data_wechat_friend wf ON wc.WechatFriendId = wf.WechatFriendId AND wf.DoctorRepresentativeId <> '' INNER JOIN ent_data_doctor_representative dr ON wf.DoctorRepresentativeId = dr.DoctorRepresentativeId INNER JOIN (SELECT DISTINCT rph.RepresentativeId,ph.ProjectId FROM ent_data_representative_project_hospital rph INNER JOIN ent_data_project_hospital ph ON rph.ProjectHospitalId = ph.ProjectHospitalId AND ph.IsDeleted = 0 ) rp ON dr.RepresentativeId = rp.RepresentativeId WHERE wc.Content NOT LIKE '%我通过了你的&友验证%' AND wc.Content NOT LIKE '%请先发送朋友验证请求%' AND wc.Content NOT LIKE '%现在可以开始聊天了%' AND wc.content NOT LIKE '%撤回了一条消息%' AND wc.IsSend = 1 GROUP BY dr.DoctorId,rp.ProjectId ) lastwechat ON lastwechat.DoctorId = d.DoctorId AND lastwechat.ProjectId = p.ProjectId";

        $execSql .= " WHERE 1=1";

        if ($externalCode) {
            $execSql .= " AND hc.ExternalCode = '$externalCode'";
        }

        if ($hospitalName) {
            $execSql .= " AND hn.HospitalName LIKE '%$hospitalName%'";
        }

        if ($provinceId) {
            $execSql .= " AND hn.HospitalName LIKE '%$hospitalName%'";
        }

        if ($marketRepresentativeName) {
            $execSql .= " AND r.RepresentativeName = '$marketRepresentativeName'";
        }

        if ($doctorNo) {
            $execSql .= " AND d.DoctorNo = $doctorNo";
        }

        if ($doctorName) {
            $execSql .= " AND d.DoctorName = '$doctorName'";
        }

        if ($lostStatus) {
            if ($lostStatus == 1) {
                $execSql .= " AND (pd.LostStatus <> 'Active' OR pd.LostStatus IS NULL)";
            } else if ($lostStatus == 2) {
                $execSql .= " AND pd.LostStatus = 'Active'";
            }
        }

        if ($isDeleted) {
            if ($isDeleted == 1) {
                $execSql .= " AND pd.IsDeleted = 0";
            } else if ($isDeleted == 2) {
                $execSql .= " AND pd.IsDeleted = 1";
            }
        }

        if ($representativeName) {
            $execSql .= " AND mr.RepresentativeName = '$representativeName'";
        }

        if ($isAddWechat) {
            if ($isDeleted == 1) {
                $execSql .= " AND wf.DoctorId <> ''";
            } else if ($isDeleted == 2) {
                $execSql .= " AND (wf.DoctorId = '' OR wf.DoctorId IS NULL)";
            }
        }

        if ($addWechatDate) {
            $execSql .= " AND date_format( wf.RelationEstablishDate, '%Y-%m-%d %H:%i:%s' ) >= '$addWechatFrom[0] 00:00:00' AND date_format( wf.RelationEstablishDate, '%Y-%m-%d %H:%i:%s' ) <= '$addWechatTo[1] 23:59:59'";
        }

        $execSql .= " GROUP BY p.ProjectCode,hc.ExternalCode,h.HospitalId,hn.HospitalName,h.ProvinceName,h.CityName,d.DoctorNo,d.DoctorName";

//        $fileName = 'doctor_' . $projectCodeStr . '_' . Carbon::now()->toDateString() . '_' . $operatorId . '_' . Carbon::now()->timestamp;
        $fileName = 'YS_' . $projectCodeStr . '_' . Carbon::now()->timestamp;

        $insertData = [
            'task_name' => $taskName,
            'exec_sql' => $execSql,
            'file_name' => $fileName,
            'operator_id' => $operatorId,
            'created_at' => Carbon::now()
        ];

        StatisticsCron::insert($insertData);

        return ResponseWrapper::success();
    }

    /**
     * 获取医生统计所需内容
     *
     * @param Request $request
     * @return array
     */
    public function getDoctorRequireContent(Request $request)
    {
        $taskName = "doctor";

        $operatorId = $request->attributes->get('user_id');

        $project = StatisticsService::newInstance()->getProject();

        $province = StatisticsService::newInstance()->getProvince();

        $isSubmit = StatisticsService::newInstance()->isSubmit($operatorId, $taskName);

        return ResponseWrapper::success([
            "project" => $project,
            "province" => $province,
            "is_submit" => $isSubmit
        ]);
    }

    /**
     * 获取医生统计列表
     *
     * @param Request $request
     * @return array
     */
    public function getDoctorLists(Request $request)
    {
        $taskName = "doctor";
        // 分页
        $operatorId = $request->attributes->get('user_id');

        $limit = 10;
        if ($request->has('page')) {
            $page = (int)$request->input('page');
        } else {
            $page = 1;
        }

        $total = StatisticsService::newInstance()->getTaskCount($operatorId, $taskName);

        $tasks = StatisticsService::newInstance()->getTaskList($operatorId, $taskName, $page, $limit);

        return ResponseWrapper::success([
            "tasks" => $tasks,
            "total" => $total,
            "limit" => $limit,
        ]);
    }
}
