<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-05-09 17:03:43
 */

namespace App\Http\Controllers;

use App\Http\ResponseWrapper;
use App\Services\Dotnet\RegionService;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

/**
 * 行政区划控制器
 *
 * @package App\Http\Controllers
 */
class RegionController extends Controller
{
    /**
     * 返回下级行政区划列表
     *
     * @param Request $request
     * @return array
     */
    public function getSubRegions(Request $request)
    {
        $service = RegionService::newInstance();
        if ($request->has("parent_id")) {
            $regions = $service->getSubRegions(
                (string)$request->input("parent_id")
            );
        } else {
            $regions = $service->getSubRegions();
        }

        return ResponseWrapper::success(['regions' => $regions]);
    }

    /**
     * 返回树形结构的行政区划数据
     *
     * @return array
     */
    public function getRegionsTree()
    {
        $regionsTree = RegionService::newInstance()->getRegionsTree();

        return ResponseWrapper::success(['regions_tree' => $regionsTree]);
    }
}