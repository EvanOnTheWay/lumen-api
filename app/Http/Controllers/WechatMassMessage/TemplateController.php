<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-04-11 16:08:39
 */

namespace App\Http\Controllers\WechatMassMessage;

use App\Http\ResponseStatus;
use App\Http\ResponseWrapper;
use App\Models\Dotnet\Representative;
use App\Models\WechatMassMessage\Template;
use App\Services\Wechat\MassMessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Lumen\Routing\Controller;

/**
 * 微信群发: 消息模板
 *
 * @package App\Http\Controllers\WechatMassMessage
 */
class TemplateController extends Controller
{
    /**
     * 查询消息模板列表
     *
     * @param Request $request
     * @return array
     */
    public function getTemplates(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "keyword" => "string|min:2",
            "state" => ["integer", Rule::in([Template::STATE_ENABLE, Template::STATE_DISABLE])]
        ], [
            "keyword.*" => "请输入正确格式的模板名称(至少 2 个字符)",
            "state.*" => "请输入正确的可用状态",
        ]);
        if ($validator->fails()) {
            return ResponseWrapper::failure(ResponseStatus::INVALID_PARAMETER, $validator->errors()->first());
        }

        $query = Template::orderBy("id", "desc");
        if ($request->has("keyword")) {
            $keyword = (string)$request->input("keyword");
            $query->where("name", "like", "%{$keyword}%");
        }
        if ($request->has("state")) {
            $query->where("state", (integer)$request->input("state"));
        }

        $templates = $query->get()->toArray();

        return ResponseWrapper::success(["templates" => $templates]);
    }

    /**
     * 创建消息模板
     *
     * @param Request $request
     * @return array
     */
    public function createTemplate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|string|between:6,30",
            "content" => "required|string|between:10,255"
        ], [
            "name.*" => "请输入正确格式的模板名称(长度 6-30 位)",
            "content.*" => "请输入正确格式的模板名称(长度 10-255 位)",
        ]);
        if ($validator->fails()) {
            return ResponseWrapper::failure(ResponseStatus::INVALID_PARAMETER, $validator->errors()->first());
        }

        $template = new Template();
        $template->name = (string)$request->input("name");
        $template->content = (string)$request->input("content");
        $template->save();

        return ResponseWrapper::success(["template" => $template]);
    }

    /**
     * 修改消息模板
     *
     * @param Request $request
     * @return array
     */
    public function modifyTemplate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id" => "required|integer|min:1",
            "name" => "required|string|between:6,30",
            "content" => "required|string|between:10,255"
        ], [
            "id.*" => "请输入正确的模板编号",
            "name.*" => "请输入正确格式的模板名称(长度 6-30 位)",
            "content.*" => "请输入正确格式的模板名称(长度 10-255 位)",
        ]);
        if ($validator->fails()) {
            return ResponseWrapper::failure(ResponseStatus::INVALID_PARAMETER, $validator->errors()->first());
        }

        $id = (int)$request->input("id");
        $template = Template::find($id);
        if (null === $template) {
            return ResponseWrapper::failure(ResponseStatus::WECHAT_MASS_MESSAGE_TEMPLATE_NOT_FOUND);
        }

        $template->setNameContent(
            (string)$request->input("name"),
            (string)$request->input("content")
        );

        return ResponseWrapper::success(["template" => $template]);
    }

    /**
     * 启用消息模板
     *
     * @param Request $request
     * @return array
     */
    public function enableTemplate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id" => "required|integer|min:1"
        ], [
            "id.*" => "请输入正确的模板编号"
        ]);
        if ($validator->fails()) {
            return ResponseWrapper::failure(ResponseStatus::INVALID_PARAMETER, $validator->errors()->first());
        }

        $id = (int)$request->input("id");
        $template = Template::find($id);
        if (null === $template) {
            return ResponseWrapper::failure(ResponseStatus::WECHAT_MASS_MESSAGE_TEMPLATE_NOT_FOUND);
        }

        $template->setEnable();

        return ResponseWrapper::success();
    }

    /**
     * 停用消息模板
     *
     * @param Request $request
     * @return array
     */
    public function disableTemplate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id" => "required|integer|min:1"
        ], [
            "id.*" => "请输入正确的模板编号"
        ]);
        if ($validator->fails()) {
            return ResponseWrapper::failure(ResponseStatus::INVALID_PARAMETER, $validator->errors()->first());
        }

        $id = (int)$request->input("id");
        $template = Template::find($id);
        if (null === $template) {
            return ResponseWrapper::failure(ResponseStatus::WECHAT_MASS_MESSAGE_TEMPLATE_NOT_FOUND);
        }

        $template->setDisable();

        return ResponseWrapper::success();
    }

    /**
     * 预览消息模板
     *
     * @param Request $request
     * @return array
     */
    public function previewTemplate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id" => "required|integer|min:1"
        ], [
            "id.*" => "请输入正确的模板编号"
        ]);
        if ($validator->fails()) {
            return ResponseWrapper::failure(ResponseStatus::INVALID_PARAMETER, $validator->errors()->first());
        }

        $template = Template::find((int)$request->input("id"));
        if (null === $template) {
            return ResponseWrapper::failure(ResponseStatus::WECHAT_MASS_MESSAGE_TEMPLATE_NOT_FOUND);
        }

        // 当前登录用户使用的专员身份
        $representativeId = $request->attributes->get("representative_id");
        $representativeData = Representative::select(['representativeName'])
            ->where('representativeId', $representativeId)
            ->first();

        $examples = [
            ["full_name" => "王凌妍", "last_name" => "王", "first_name" => "凌妍"],
            ["full_name" => "李增志", "last_name" => "李", "first_name" => "增志"],
            ["full_name" => "范丹峰", "last_name" => "范", "first_name" => "丹峰"],
            ["full_name" => "胡金梅", "last_name" => "胡", "first_name" => "金梅"],
            ["full_name" => "周珺", "last_name" => "周", "first_name" => "珺"],
            ["full_name" => "邓小波", "last_name" => "邓", "first_name" => "小波"],
            ["full_name" => "宋延涛", "last_name" => "潘", "first_name" => "月明"],
            ["full_name" => "潘月明", "last_name" => "杨", "first_name" => "英"],
            ["full_name" => "杨英", "last_name" => "李", "first_name" => "冬梅"],
            ["full_name" => "李冬梅", "last_name" => "吴", "first_name" => "媛"]
        ];

        $example = $examples[array_rand($examples, 1)];
        $example['rep_name'] = $representativeData->representativeName;

        $result = MassMessageService::newInstance()->renderTemplate($template->content, $example);

        return ResponseWrapper::success(["content" => $result]);
    }
}