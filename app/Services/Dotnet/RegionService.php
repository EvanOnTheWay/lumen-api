<?php
/**
 * Created by PhpStorm.
 * @author Wenpeng
 * @email imwwp@outlook.com
 * @time 2019-05-10 16:09:01
 */

namespace App\Services\Dotnet;

use App\Models\Dotnet\Region;
use App\Services\BaseService;

/**
 * 行政区划业务逻辑
 *
 * @package App\Services
 */
class RegionService extends BaseService
{
    /**
     * 返回下级行政区划列表
     *
     * @param string|null $parentId
     * @return array
     */
    public function getSubRegions(?string $parentId = null): array
    {
        $columns = [
            'regionId as id',
            'nameZh as title',
            'parentRegionId as parent_id'
        ];

        $query = Region::select($columns);

        if (null === $parentId) {
            $query->whereNull("parentRegionId");
        } else {
            $query->where("parentRegionId", $parentId);
        }

        return $query->get()->toArray();
    }

    /**
     * 返回树形结构的行政区划数据
     *
     * @return array
     */
    public function getRegionsTree(): array
    {
        $columns = [
            'regionId as id',
            'nameZh as title',
            'parentRegionId as parent_id'
        ];

        $regions = Region::select($columns)->get();

        return $this->buildRegionsTree($regions->toArray());
    }

    /**
     * 返回指定行政区划的等级
     *
     * @param string $regionId
     * @return string|null
     */
    public function getRegionGrade(string $regionId): ?string
    {
        $region = Region::select(["regionGrade"])
            ->where("regionId", $regionId)
            ->first();
        if (null !== $region) {
            return $region->regionGrade;
        }

        return null;
    }

    /**
     * 构建树形结果的行政区划数据
     *
     * @param array $regions 行政区划列表
     * @param string|null $parentId 树结构根结点
     * @return array
     */
    protected function buildRegionsTree(array $regions, ?string $parentId = null): array
    {
        $result = [];

        foreach ($regions as $index => $region) {
            if ($region['parent_id'] === $parentId) {
                unset($regions[$index]);

                $children = $this->buildRegionsTree($regions, $region['id']);
                if (!empty($children)) {
                    $region['children'] = $children;
                }

                $result[] = $region;
            }
        }

        return $result;
    }
}