<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\BaseController;
use think\facade\Db;
use think\facade\Lang;
use think\facade\Request;
use think\response\Json;
use app\common\Token;
/**
 * 测试控制器
 * @route('test')
 */
class Test extends BaseController
{
    /**
     * 测试
     * @route('index', 'get')
     */
    public function index(): Json
    {
        return json([
            'code' => 200,
            'msg' => Lang::get('common.test_successful'),
            'data' => null
        ]);
    }

    /**
     * 语言测试
     * @return \think\response\Json
     */
    public function lang()
    {
        $currentLang = Lang::getLangSet();
        $welcome = Lang::get('welcome');

        return json([
            'code' => 200,
            'msg' => Lang::get('common.test_successful'),
            'data' => [
                'lang' => $currentLang,
                'welcome' => $welcome,
                'time' => date('Y-m-d H:i:s'),
                'request_info' => [
                    'ip' => Request::ip(),
                    'method' => Request::method(),
                    'domain' => Request::domain(),
                    'url' => Request::url()
                ]
            ]
        ]);
    }


     /**
     * 测试列表分页功能
     *
     */
    public function testPagination(): Json
    {
        // if (!$this->isLogin()) {
        //     return $this->needLogin();
        // }

        // 获取测试参数
        $page = input('page', 1);
        $limit = input('limit', 10);
        $category_id = input('category_id', 0);

        // 记录测试参数
        \think\facade\Log::info('分页测试参数', [
            'page' => $page,
            'limit' => $limit,
            'category_id' => $category_id,
            'request_url' => request()->url(true)
        ]);

        // 构建查询
        $query = Db::name('blog_post');
        $where = [];

        // 如果有分类筛选
        if ($category_id) {
            $postIds = Db::name('blog_post_category')
                ->where('category_id', $category_id)
                ->column('post_id');

            if ($postIds) {
                $query->whereIn('id', $postIds);
            } else {
                // 没有该分类下的文章
                return json(['code' => 200, 'msg' => Lang::get('test.test_success_no_articles'), 'data' => [
                    'total' => 0,
                    'list' => [],
                    'page' => (int)$page,
                    'limit' => (int)$limit,
                    'pagination_status' => 'empty_result'
                ]]);
            }
        }

        // 使用统一的分页查询方法
        $result = $this->getPageList($query, $page,$limit,$where, ['is_top desc', 'sort desc', 'id desc']);

        // 验证分页数据结构
        $validationResult = $this->validatePaginationStructure($result, (int)$page, (int)$limit);

        // 添加测试结果信息
        $result['pagination_status'] = $validationResult['status'];
        if (isset($validationResult['message'])) {
            $result['pagination_message'] = $validationResult['message'];
        }

        return json(['code' => 200, 'msg' => Lang::get('test.pagination_test_completed'), 'data' => $result]);
    }

    /**
     * 验证分页数据结构
     * @param array $data 分页数据
     * @param int $requestedPage 请求的页码
     * @param int $requestedLimit 请求的每页数量
     * @return array 验证结果
     */
    private function validatePaginationStructure(array $data, int $requestedPage, int $requestedLimit): array
    {
        $result = ['status' => 'success'];

        // 检查必要的分页字段
        $requiredFields = ['list', 'total', 'page', 'limit'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                $result['status'] = 'error';
                $result['message'] = Lang::get('test.missing_pagination_field') . ": {$field}";
                return $result;
            }
        }

        // 验证页码和每页数量是否与请求一致
        if ($data['page'] != $requestedPage) {
            $result['status'] = 'warning';
            $result['message'] = Lang::get('test.page_mismatch') . ": {$data['page']} != {$requestedPage}";
            return $result;
        }

        if ($data['limit'] != $requestedLimit) {
            $result['status'] = 'warning';
            $result['message'] = Lang::get('test.limit_mismatch') . ": {$data['limit']} != {$requestedLimit}";
            return $result;
        }

        // 验证数据总数与列表长度的关系
        $total = (int)$data['total'];
        $listCount = count($data['list']);

        // 如果不是最后一页，列表长度应该等于每页数量
        $expectedItemCount = min($requestedLimit, max(0, $total - ($requestedPage - 1) * $requestedLimit));

        if ($listCount != $expectedItemCount) {
            $result['status'] = 'warning';
            $result['message'] = Lang::get('test.item_count_mismatch') . ": {$listCount} != {$expectedItemCount}";
            return $result;
        }

        // 验证是否有超出范围的页码请求
        $maxPage = $total > 0 ? ceil($total / $requestedLimit) : 1;
        if ($requestedPage > $maxPage) {
            $result['status'] = 'warning';
            $result['message'] = Lang::get('test.page_out_of_range') . ": {$requestedPage} > {$maxPage}";
        }

        return $result;
    }

    /**
     * 测试不同分页参数组合
     *
     */
    public function testPaginationCombinations(): Json
    {
        // if (!$this->isLogin()) {
        //     return $this->needLogin();
        // }

        $testCases = [
            ['page' => 1, 'limit' => 5],
            ['page' => 2, 'limit' => 5],
            ['page' => 1, 'limit' => 10],
            ['page' => 1, 'limit' => 20],
            ['page' => 999, 'limit' => 10], // 测试超出范围的页码
        ];

        $results = [];

        foreach ($testCases as $testCase) {
            $page = $testCase['page'];
            $limit = $testCase['limit'];

            // 构建查询
            $query = Db::name('blog_post');
            $where = [];

            // 手动设置分页参数（模拟请求）

            // 使用统一的分页查询方法
            $result = $this->getPageList($query,  $page, $limit,$where, ['is_top desc', 'sort desc', 'id desc']);

            // 验证分页数据结构
            $validationResult = $this->validatePaginationStructure($result, $page, $limit);

            $results[] = [
                'test_case' => sprintf(Lang::get('test.test_case_format'), $page, $limit),
                'pagination_status' => $validationResult['status'],
                'message' => $validationResult['message'] ?? Lang::get('test.validation_passed'),
                'data' => [
                    'total' => $result['total'],
                    'page' => $result['page'],
                    'limit' => $result['limit'],
                    'list_count' => count($result['list'])
                ]
            ];
        }

        return json(['code' => 200, 'msg' => Lang::get('test.pagination_combinations_test_completed'), 'data' => $results]);
    }


}