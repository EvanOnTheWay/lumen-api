<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-11 23:40:35
 */

namespace App\Http\Controllers\WechatMassMessage;

use App\Http\ResponseStatus;
use App\Http\ResponseWrapper;
use App\Models\Dotnet\Doctor;
use App\Models\Dotnet\Representative;
use App\Models\WechatMassMessage\Batch;
use App\Models\WechatMassMessage\Task;
use App\Models\WechatMassMessage\Template;
use App\Services\Wechat\MassMessageService;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Lumen\Routing\Controller;
use Log;

/**
 * 微信群发: 批次和任务
 *
 * @package App\Http\Controllers\WechatMassMessage
 */
class BatchController extends Controller
{
    /**
     * 读取指定的一个群发任务
     *
     * @param Request $request
     * @return array
     */
    public function getBatch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'batch_id' => "required|integer|min:1",
        ], [
            "batch_id.*" => "请输入正确的批次编号"
        ]);
        if ($validator->fails()) {
            return ResponseWrapper::invalid($validator->errors()->first());
        }

        $batchId = (integer)$request->input('batch_id');

        $found = Batch::find($batchId);
        if (null === $found) {
            return ResponseWrapper::failure(ResponseStatus::WECHAT_MASS_MESSAGE_BATCH_NOT_FOUND);
        }

        // 格式化批次信息
        $batch = MassMessageService::newInstance()->formatBatch($found->toArray());

        // 取出批次中的任务列表，格式化后并入批次信息中
        $tasks = Task::where('batch_id', $batchId)->get()->toArray();
        $batch['task_count'] = [
            'total' => count($tasks),
            'pending' => 0,
            'running' => 0,
            'success' => 0,
            'failure' => 0,
        ];
        foreach ($tasks as $task) {
            if ($task['execute_state'] === Task::EXECUTE_RUNNING) {
                $batch['task_count']['running']++;
            } elseif ($task['execute_state'] === Task::EXECUTE_SUCCESS) {
                $batch['task_count']['success']++;
            } elseif ($task['execute_state'] === Task::EXECUTE_FAILURE) {
                $batch['task_count']['failure']++;
            } else {
                $batch['task_count']['pending']++;
            }
        }

        $batch["tasks"] = MassMessageService::newInstance()->formatTasks($tasks);


        return ResponseWrapper::success(["batch" => $batch]);
    }

    /**
     * 保存新创建的群发任务
     *
     * @param Request $request
     * @return array
     */
    public function createBatch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "project_id" => "required|string|uuid",
            "auditor_id" => "required|string|uuid",
            "doctor_ids" => "required|array",
            "template_id" => "required|integer|min:0",
        ], [
            "project_id.*" => "请输入正确的项目编号",
            "auditor_id.*" => "请输入正确的审核人编号",
            "template_id.*" => "请输入正确的模板编号",
            "doctor_ids.*" => "请输入正确的医生编号列表",
        ]);
        if ($validator->fails()) {
            return ResponseWrapper::invalid($validator->errors()->first());
        }

        $projectId = (string)$request->input("project_id");
        $auditorId = (string)$request->input("auditor_id");
        $templateId = (integer)$request->input("template_id");
        $doctorIds = (array)$request->input("doctor_ids");
        if (empty($doctorIds)) {
            return ResponseWrapper::invalid("请至少选择一名医生");
        }

        // 当前登录用户使用的专员身份
        $userId = $request->attributes->get("user_id");
        $representativeId = $request->attributes->get('representative_id');
        $representativeName = Representative::select(['representativeName'])
            ->where('representativeId', $representativeId)
            ->first()
            ->representativeName;

        // TODO 入参有效性验证
        $batch = new Batch();
        $batch->project_id = $projectId;
        $batch->template_id = $templateId;
        $batch->creator_id = $userId;
        $batch->auditor_id = $auditorId;
        $batch->representative_id = $representativeId;
        $batch->save();

        $tasks = [];
        $doctors = Doctor::select(["doctorId", "doctorName"])
            ->whereIn("doctorId", $doctorIds)
            ->get();
        $template = Template::find($templateId);

        $service = MassMessageService::newInstance();
        foreach ($doctors as $doctor) {
            $args = [
                "full_name" => $doctor->doctorName,
                "last_name" => Str::substr($doctor->doctorName, 0, 1),
                "first_name" => Str::substr($doctor->doctorName, 1, null),
                "rep_name" => $representativeName
            ];
            $tasks[] = [
                'batch_id' => $batch->id,
                "doctor_id" => $doctor->doctorId,
                "template_id" => $template->id,
                "content" => $service->renderTemplate($template->content, $args),
            ];
        }

        Task::insert($tasks);

        return ResponseWrapper::success(['batch_id' => $batch->id]);
    }

    /**
     * 提审一个指定的群发任务
     *
     * @param Request $request
     * @return array
     */
    public function submitBatch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'batch_id' => "required|integer|min:1"
        ], [
            "batch_id.*" => "请输入正确的批次编号"
        ]);
        if ($validator->fails()) {
            return ResponseWrapper::invalid($validator->errors()->first());
        }

        $batchId = (integer)$request->input('batch_id');

        // 当前登录用户使用的专员身份
        $representativeId = $request->attributes->get('representative_id');

        $batch = Batch::where("id", $batchId)
            ->where("audit_state", Batch::AUDIT_PENDING)
            ->where('representative_id', $representativeId)
            ->first();
        if (null === $batch) {
            return ResponseWrapper::failure(ResponseStatus::WECHAT_MASS_MESSAGE_BATCH_NOT_FOUND);
        }

        $batch->audit_state = Batch::AUDIT_AUDITING;
        $batch->save();

        return ResponseWrapper::success();
    }

    /**
     * 驳回一个指定的群发任务
     *
     * @param Request $request
     * @return array
     */
    public function rejectBatch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'batch_id' => "required|integer|min:1"
        ], [
            "batch_id.*" => "请输入正确的批次编号"
        ]);
        if ($validator->fails()) {
            return ResponseWrapper::invalid($validator->errors()->first());
        }

        $userId = $request->attributes->get("user_id");
        $batchId = (integer)$request->input('batch_id');

        $batch = Batch::where("id", $batchId)
            ->where("auditor_id", $userId)
            ->where("audit_state", Batch::AUDIT_AUDITING)
            ->first();
        if (null === $batch) {
            return ResponseWrapper::failure(ResponseStatus::WECHAT_MASS_MESSAGE_BATCH_NOT_FOUND);
        }

        $batch->audit_state = Batch::AUDIT_REJECTED;
        $batch->save();

        return ResponseWrapper::success();
    }

    /**
     * 批准指定的一个群发任务
     *
     * @param Request $request
     * @return array
     */
    public function approveBatch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'batch_id' => "required|integer|min:1"
        ], [
            "batch_id.*" => "请输入正确的批次编号"
        ]);
        if ($validator->fails()) {
            return ResponseWrapper::invalid($validator->errors()->first());
        }

        $userId = $request->attributes->get("user_id");
        $batchId = (integer)$request->input('batch_id');

        $batch = Batch::where("id", $batchId)
            ->where("auditor_id", $userId)
            ->where("audit_state", Batch::AUDIT_AUDITING)
            ->first();
        if (null === $batch) {
            return ResponseWrapper::failure(ResponseStatus::WECHAT_MASS_MESSAGE_BATCH_NOT_FOUND);
        }

        $batch->audit_state = Batch::AUDIT_APPROVED;
        $batch->save();

        return ResponseWrapper::success();
    }

    /**
     * 执行指定的一个群发任务
     *
     * @param Request $request
     * @return array
     */
    public function executeBatch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'batch_id' => "required|integer|min:1",
        ], [
            "batch_id.*" => "请输入正确的批次编号"
        ]);
        if ($validator->fails()) {
            return ResponseWrapper::invalid($validator->errors()->first());
        }

        $batchId = (integer)$request->input('batch_id');

        // 当前登录用户使用的专员身份
        $representativeId = $request->attributes->get('representative_id');

        $batch = Batch::where('representative_id', $representativeId)
            ->where("execute_state", Batch::EXECUTE_PENDING)
            ->where("audit_state", Batch::AUDIT_APPROVED)
            ->where("id", $batchId)
            ->first();
        if (null === $batch) {
            return ResponseWrapper::failure(ResponseStatus::WECHAT_MASS_MESSAGE_BATCH_NOT_FOUND);
        }

        $sourceMessages = Task::where('batch_id', $batchId)
            ->where('execute_state', Task::EXECUTE_PENDING)
            ->get(['id', 'content', 'doctor_id'])
            ->toArray();

        $doctorIds = array_column($sourceMessages, 'doctor_id');
        $remarkNames = MassMessageService::newInstance()->multiQueryDoctorWechatRemarkName($doctorIds);

        $filteredMessages = [];
        $filteredMessageIds = [];
        $discardedMessageIds = [];
        foreach ($sourceMessages as $message) {
            if (isset($remarkNames[$message['doctor_id']])) {
                $filteredMessages[] = [
                    'id' => $message['id'],
                    'rep_id' => $representativeId,
                    'batch_id' => $batchId,
                    'content' => $message['content'],
                    'remark_name' => $remarkNames[$message['doctor_id']]
                ];
                $filteredMessageIds[] = $message['id'];
            } else {
                $discardedMessageIds[] = $message['id'];
            }
        }

        // 标记放弃投递的消息
        if (!empty($discardedMessageIds)) {
            // 批量标记消息为"执行失败"
            Task::multiUpdateExecuteState($discardedMessageIds, Task::EXECUTE_FAILURE, '专员的微信联系人中未找到消息接收人');
        }

        if (!empty($filteredMessages)) {
            try {
                $response = (new Client())->post('http://127.0.0.1:8000/wechatWebRobot/sendTextMessage', [
                    'json' => [
                        'rep_id' => $representativeId,
                        'messages' => $filteredMessages
                    ]
                ]);

                // 标记消息批次为"正在执行"
                $batch->updateExecuteState(Batch::EXECUTE_RUNNING);

                // 批量标记消息为"正在执行"
                // Task::multiUpdateExecuteState($filteredMessageIds, Task::EXECUTE_RUNNING, '正在执行');

                $responseData = json_decode($response->getBody()->getContents(), JSON_OBJECT_AS_ARRAY);
                switch ($responseData['code']) {
                    case 200:
                        break;
                    case 403:
                        return ResponseWrapper::failure(ResponseStatus::WECHAT_MASS_MESSAGE_ISERVICE_NOT_ONLINE);
                    case 400:
                        return ResponseWrapper::invalid();
                    default:
                        Log::error('微信群发:业务异常', $responseData);
                        return ResponseWrapper::failure(ResponseStatus::WECHAT_MASS_MESSAGE_ISERVICE_CODE_UNDEFINED);
                }
            } catch (TransferException $exception) {
                Log::error('微信群发:服务异常', [
                    'exception' => $exception->getMessage(),
                    'trace_str' => $exception->getTraceAsString(),
                ]);
                return ResponseWrapper::failure(ResponseStatus::WECHAT_MASS_MESSAGE_ISERVICE_UNREACHED);
            }
        }

        return ResponseWrapper::success();
    }

    /**
     * 重新发送之前失败的消息
     *
     * @param Request $request
     * @return array
     */
    public function resendFailedMessages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'batch_id' => "required|integer|min:1",
        ], [
            "batch_id.*" => "请输入正确的批次编号"
        ]);
        if ($validator->fails()) {
            return ResponseWrapper::invalid($validator->errors()->first());
        }

        $batchId = (integer)$request->input('batch_id');

        // 当前登录用户使用的专员身份
        $representativeId = $request->attributes->get('representative_id');

        $batch = Batch::where('representative_id', $representativeId)
            ->where("execute_state", Batch::EXECUTE_FAILURE)
            ->where("audit_state", Batch::AUDIT_APPROVED)
            ->where("id", $batchId)
            ->first();
        if (null === $batch) {
            return ResponseWrapper::failure(ResponseStatus::WECHAT_MASS_MESSAGE_BATCH_NOT_FOUND);
        }

        $sourceMessages = Task::where('batch_id', $batchId)
            ->where('execute_state', Task::EXECUTE_PENDING)
            ->get(['id', 'content', 'doctor_id'])
            ->toArray();

        if (empty($sourceMessages)) {
            return ResponseWrapper::success();
        }

        $doctorIds = array_column($sourceMessages, 'doctor_id');
        $remarkNames = MassMessageService::newInstance()->multiQueryDoctorWechatRemarkName($doctorIds);

        $filteredMessages = [];
        $filteredMessageIds = [];
        $discardedMessageIds = [];
        foreach ($sourceMessages as $message) {
            if (isset($remarkNames[$message['doctor_id']])) {
                $filteredMessages[] = [
                    'id' => $message['id'],
                    'rep_id' => $representativeId,
                    'batch_id' => $batchId,
                    'content' => $message['content'],
                    'remark_name' => $remarkNames[$message['doctor_id']]
                ];
                $filteredMessageIds[] = $message['id'];
            } else {
                $discardedMessageIds[] = $message['id'];
            }
        }

        // 标记放弃投递的消息
        if (!empty($discardedMessageIds)) {
            // 批量标记消息为"执行失败"
            Task::multiUpdateExecuteState($discardedMessageIds, Task::EXECUTE_FAILURE, '专员的微信联系人中未找到消息接收人');
        }

        if (!empty($filteredMessages)) {
            try {
                // 标记消息批次为"正在执行"
                $batch->updateExecuteState(Batch::EXECUTE_RUNNING);

                // 批量标记消息为"正在执行"
                // Task::multiUpdateExecuteState($filteredMessageIds, Task::EXECUTE_RUNNING, '正在执行');

                // return ResponseWrapper::success();

                $response = (new Client())->post('http://127.0.0.1:8000/wechatWebRobot/sendTextMessage', [
                    'json' => [
                        'rep_id' => $representativeId,
                        'messages' => $filteredMessages
                    ]
                ]);

                $responseData = json_decode($response->getBody()->getContents(), JSON_OBJECT_AS_ARRAY);
                switch ($responseData['code']) {
                    case 200:
                        break;
                    case 403:
                        return ResponseWrapper::failure(ResponseStatus::WECHAT_MASS_MESSAGE_ISERVICE_NOT_ONLINE);
                    case 400:
                        return ResponseWrapper::invalid();
                    default:
                        Log::error('微信群发:业务异常', $responseData);
                        return ResponseWrapper::failure(ResponseStatus::WECHAT_MASS_MESSAGE_ISERVICE_CODE_UNDEFINED);
                }
            } catch (TransferException $exception) {
                Log::error('微信群发:服务异常', [
                    'exception' => $exception->getMessage(),
                    'trace_str' => $exception->getTraceAsString(),
                ]);
                return ResponseWrapper::failure(ResponseStatus::WECHAT_MASS_MESSAGE_ISERVICE_UNREACHED);
            }
        }

        return ResponseWrapper::success();
    }

    /**
     * 查询被指定用户创建的批次列表
     *
     * @param Request $request
     * @return array
     */
    public function getCreatorBatches(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "project_id" => "string|uuid",
            "audit_state" => ["integer", Rule::in([
                Batch::AUDIT_PENDING,
                Batch::AUDIT_AUDITING,
                Batch::AUDIT_APPROVED,
                Batch::AUDIT_REJECTED
            ])],
            "start_date" => "date_format:Y-m-d",
            "end_date" => "date_format:Y-m-d",
        ], [
            "project_id.*" => "请输入正确的项目编号",
            "audit_state.*" => "请输入正确的审核状态",
            "start_date.*" => "请输入正确的开始日期",
            "end_date.*" => "请输入正确的结束日期",
        ]);
        if ($validator->fails()) {
            return ResponseWrapper::invalid($validator->errors()->first());
        }

        // 当前登录用户使用的专员身份
        $representativeId = $request->attributes->get('representative_id');

        $query = Batch::where('representative_id', $representativeId);
        if ($request->has("project_id")) {
            $query->where("project_id", (string)$request->input("project_id"));
        }
        if ($request->has("audit_state")) {
            $query->where("audit_state", (integer)$request->input("audit_state"));
        }
        if ($request->has("start_date")) {
            $query->where("created_at", ">=", (string)$request->input("start_date"));
        }
        if ($request->has("end_date")) {
            $query->where("created_at", "<=", (string)$request->input("end_date"));
        }

        $matches = $query->orderBy("created_at", "desc")->get()->toArray();

        // 格式化查询到的批次数据列表
        $batches = MassMessageService::newInstance()->formatBatches($matches);

        return ResponseWrapper::success(["batches" => $batches]);
    }

    /**
     * 查询需指定用户审核的批次列表
     *
     * @param Request $request
     * @return array
     */
    public function getAuditorBatches(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "project_id" => "string|uuid",
            "audit_state" => ["integer", Rule::in([
                Batch::AUDIT_AUDITING,
                Batch::AUDIT_APPROVED,
                Batch::AUDIT_REJECTED
            ])],
            "start_date" => "date_format:Y-m-d",
            "end_date" => "date_format:Y-m-d",
        ], [
            "project_id.*" => "请输入正确的项目编号",
            "audit_state.*" => "请输入正确的审核状态",
            "start_date.*" => "请输入正确的开始日期",
            "end_date.*" => "请输入正确的结束日期",
        ]);
        if ($validator->fails()) {
            return ResponseWrapper::invalid($validator->errors()->first());
        }

        // 当前登录用户的用户编号
        $userId = $request->attributes->get("user_id");

        $query = Batch::where("auditor_id", $userId);
        if ($request->has("project_id")) {
            $query->where("project_id", (string)$request->input("project_id"));
        }
        if ($request->has("audit_state")) {
            $query->where("audit_state", (integer)$request->input("audit_state"));
        } else {
            $query->where("audit_state", '!=', Batch::AUDIT_PENDING);
        }
        if ($request->has("start_date")) {
            $query->where("created_at", ">=", (string)$request->input("start_date"));
        }
        if ($request->has("end_date")) {
            $query->where("created_at", "<=", (string)$request->input("end_date"));
        }

        $matches = $query->orderBy("created_at", "desc")->get()->toArray();

        // 格式化查询到的批次数据列表
        $batches = MassMessageService::newInstance()->formatBatches($matches);

        return ResponseWrapper::success(["batches" => $batches]);
    }

    /**
     * 修改一个任务的消息内容(限所属批次未审核时)
     *
     * @param Request $request
     * @return array
     */
    public function modifyBatchTaskContent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "task_id" => "required|integer|min:1",
            "content" => "required|string|between:10, 255",
        ], [
            "task_id.*" => "请输入正确的项目编号",
            "content.*" => "请输入正确的消息内容(10 ~ 255 个字符)"
        ]);
        if ($validator->fails()) {
            return ResponseWrapper::invalid($validator->errors()->first());
        }

        $task = Task::find((integer)$request->input("task_id"));
        if (null === $task) {
            return ResponseWrapper::failure(ResponseStatus::WECHAT_MASS_MESSAGE_TASK_NOT_FOUND);
        }

        // 当前登录用户使用的专员身份
        $representativeId = $request->attributes->get('representative_id');

        $batch = Batch::find($task->batch_id);
        if ($batch->representative_id !== $representativeId) {
            return ResponseWrapper::failure(ResponseStatus::WECHAT_MASS_MESSAGE_BATCH_UNAUTHORIZED);
        }
        if ($batch->audit_state !== Batch::AUDIT_PENDING) {
            return ResponseWrapper::failure(ResponseStatus::WECHAT_MASS_MESSAGE_TASK_MODIFY_FAILED);
        }

        $task->content = (string)$request->input("content");
        $task->save();

        return ResponseWrapper::success();
    }
}
