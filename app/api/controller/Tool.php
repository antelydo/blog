<?php
declare (strict_types = 1);

namespace app\api\controller;

use app\BaseController;
use app\common\model\AiTool;
use think\facade\Db;
use think\facade\Lang;
use think\response\Json;
use think\facade\Request;

/**
 * AI工具管理控制器
 * @route('aiTool')
 */
class Tool extends BaseController
{
    private string $uuid;
    private array $pricing_txt;

    /**
     * 初始化
     */
    protected function initialize(): void
    {

        $this->userType = 'user';
        $this->uuid = $this->getVisitorId();
        parent::initialize();
        $this->pricing_txt = [
            'free' => 'free',
            'freemium' => 'freemium',
            'paid' => 'paid'
        ];
    }


    /**
     * 工具列表
     * @route('list', 'get')
     */
    public function list(): Json
    {
        $page = (int)input('page', 1);
        $limit = (int)input('limit', 10);
        $keyword = input('keyword', '');
        $categoryId = input('category_id', 0);
        $tagId = input('tag_id', 0);
        $hot = input('hot', 0); // 是否热门
        $new = input('new', 0);// 是否最新
        $pricingType = input('pricing_type', '');
        $orderField = input('order_field', 'sort_order');
        $orderType = input('order_type', 'asc');
        $categoryIdsStr = input('category_ids_str', '');
        $status = input('status', '');
        $isRecommended = input('is_recommended', '');
        $isTop = input('is_top', '');

        // 构建查询条件
        $query = AiTool::withSearch([ 'category_id', 'status', 'is_recommended', 'is_top', 'pricing_type', 'tag_id', 'category_ids','views','likes','favorites','comments'], [
            'category_id' => $categoryId,
            'category_ids' => $categoryIdsStr, // 添加多分类筛选
            'status' => $status,
            'is_recommended' => $isRecommended !== '' ? (bool)$isRecommended : '',
            'is_top' => $isTop !== '' ? (bool)$isTop : '',
            'pricing_type' => $pricingType,
            'tag_id' => $tagId,
        ]);

        // 关键词搜索
        if($keyword){
            // 过滤关键词，防止SQL注入
            $keyword = addslashes(htmlspecialchars(trim($keyword)));
            $query->whereLike('name|description|short_description', "%{$keyword}%");
            $cat_toolIds =[];
            $tag_toolIds =[];
            //搜索分类名称相关的工具 根据模拟搜索分类并根据分类ID获取关系表中的工具
            $categoryId = Db::name('ai_tool_category')
                ->where('name', 'like', "%{$keyword}%")
                ->where('is_show', 1)
                ->column('id');
            if (!empty($categoryId)) {
                $cat_toolIds = Db::name('ai_tool_category_relation')
                    ->where('category_id', 'in', $categoryId)
                    ->column('tool_id');
            }
            //搜索标签名称相关的工具 根据模拟搜索标题并根据标签ID获取关系表中的工具
            $tagId = Db::name('ai_tool_tag')
                ->where('name', 'like', "%{$keyword}%")
                ->where('is_show', 1)
                ->column('id');
            if (!empty($tagId)) {
                $tag_toolIds = Db::name('ai_tool_tag_relation')
                    ->where('tag_id', 'in', $tagId)
                    ->column('tool_id');
            }
            //或者条件
            if (!empty($cat_toolIds) || !empty($tag_toolIds)) {
                $query->whereOr(function ($q) use ($cat_toolIds, $tag_toolIds) {
                    if (!empty($cat_toolIds)) {
                        $q->whereIn('id', $cat_toolIds);
                    }
                    if (!empty($tag_toolIds)) {
                        $q->whereIn('id', $tag_toolIds);
                    }
                });
            }

        }


        // 热门工具
        if ($hot) {
            //热门工具规则，点赞评价收藏浏览  置顶且推荐的
            $query->where('is_top', 1)->where('is_recommended', 1);
            //点赞评价收藏浏览 最多的
            $query->order('likes', 'desc')->order('favorites', 'desc')->order('comments', 'desc')->order('views', 'desc');
        }

        // 最新工具
        if ($new) {
            //最新工具 最新发布的
            $query->order('publish_time', 'desc');
        }

        // 排序
        $allowedOrderFields = ['id', 'create_time', 'update_time', 'publish_time', 'views', 'likes', 'favorites', 'comments', 'rating', 'sort_order'];
        if (in_array($orderField, $allowedOrderFields)) {
            $query->order($orderField, $orderType);
        } else {
            $query->order('create_time', 'desc');
        }

        // 分页查询
        $query= $query->with(['category']);
        $count = $query->count();
        $list = $query->page($page, $limit)->select();

        // 获取工具标签和分类
        $toolIds = $query->column('id');
        $tagRelations = [];
        $categoryRelations = [];

        if (!empty($toolIds)) {
            // 获取标签关联
            $tagRelations = Db::name('ai_tool_tag_relation')
                ->alias('r')
                ->join('ai_ai_tool_tag t', 'r.tag_id = t.id')
                ->field('r.tool_id, r.tag_id, t.name as tag_name')
                ->whereIn('r.tool_id', $toolIds)
                ->where('t.is_show', 1)
                ->select()
                ->toArray();

            // 获取分类关联
            $categoryRelations = Db::name('ai_tool_category_relation')
                ->alias('r')
                ->join('ai_ai_tool_category c', 'r.category_id = c.id')
                ->field('r.tool_id, r.category_id, c.name as category_name')
                ->whereIn('r.tool_id', $toolIds)
                ->select()
                ->toArray();
        }

        // 整理标签数据
        $toolTags = [];
        foreach ($tagRelations as $relation) {
            $toolTags[$relation['tool_id']][] = [
                'id' => $relation['tag_id'],
                'name' => $relation['tag_name'],
            ];
        }

        // 整理分类数据
        $toolCategories = [];
        foreach ($categoryRelations as $relation) {
            $toolCategories[$relation['tool_id']][] = [
                'id' => $relation['category_id'],
                'name' => $relation['category_name'],
            ];
        }

        // 将标签和分类数据添加到工具列表中
        foreach ($list as &$tool) {
            $tool['tags'] = $toolTags[$tool['id']] ?? [];
            $tool['categories'] = $toolCategories[$tool['id']] ?? [];

            // 翻译价格类型
            if (isset($tool['pricing_type']) && in_array($tool['pricing_type'], ['free', 'freemium', 'paid'])) {
                $tool['pricing_type'] = Lang::get('aiTool.'. $tool['pricing_type']);
            }
        }

        return json(['code' => 200, 'msg' => Lang::get('aiTool.get_tool_list_success'), 'data'=>['list'=>$list,'total'=>$count,'page'=>$page,'limit'=>$limit,'order_field'=>$orderField,'order_type'=>$orderType]]);
    }

    /**
     * 工具详细
     * @route('detail', 'get')
     */
    public function detail(): Json
    {
        $id = input('id', 0);
        $is_more_tool = input('is_more_tool', 0);//获取更多工具推荐的
        $is_get_comment = input('is_get_comment', 0);
        $is_check_like = input('is_check_like', 0);
        $is_check_favorite = input('is_check_favorite', default: 0); // 是否检查收藏状态

        if (!$id) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.tool_not_exist')]);
        }

        $tool = AiTool::with(['category'])->find($id);
        $tool = $tool->toArray();
         //时间格式
        $tool['publish_time'] =$tool['publish_time'] ? date('Y-m-d H:i:s', $tool['publish_time']):"'";
        //转数组
        $tool['screenshots'] =$tool['screenshots']?json_decode($tool['screenshots'], true):[];
        $tool['features'] =$tool['features']?json_decode($tool['features'], true):[];
        $tool['pricing_info'] =$tool['pricing_info']?json_decode($tool['pricing_info'], true):[];
        $tool['platforms'] =$tool['platforms']?json_decode($tool['platforms'], true):[];
        $tool['languages'] =$tool['languages']?json_decode($tool['languages'], true):[];
        $tool['usage_tips'] =$tool['usage_tips']?json_decode($tool['usage_tips'], true):[];
        // 翻译价格类型
        if (isset($tool['pricing_type']) && in_array($tool['pricing_type'], ['free', 'freemium', 'paid'])) {
            $tool['pricing_type'] = Lang::get('aiTool.' . $tool['pricing_type']);
        }



        if (!$tool) {
            return json(['code' => 400, 'msg' => Lang::get('aiTool.tool_not_exist')]);
        }

        //获取工具点赞状态
        $tool['is_liked'] = false;
        //检查是否已点赞
        if($is_check_like>0) {
            if ($this->isLogin()) {
                $liked = Db::name('ai_tool_like')
                    ->where('tool_id', $id)
                    ->where('user_id', $this->userId)
                    ->where('user_type', $this->userType)
                    ->value('id');
                $tool['is_liked'] = (bool)$liked;
            } else {
                //检查访客是否点赞
                $liked = Db::name('ai_tool_like')
                    ->where('tool_id', $id)
                    ->where('user_id', 0)
                    ->where('user_type', 'guest')
                    ->where('uuid', $this->uuid)
                    ->value('id');
                $tool['is_liked'] = (bool)$liked;
            }
        }
        //获取工具点赞数
        $tool['like_count'] = Db::name('ai_tool_like')->where('tool_id', $id)->count();

        //获取工具收藏状态
        $tool['is_favorite'] = false;
        if($is_check_favorite>0) {
            if ($this->isLogin()) {
                $favorites = Db::name('ai_tool_favorite')
                    ->where('tool_id', $id)
                    ->where('user_id', $this->userId)
                    ->where('user_type', $this->userType)
                    ->value('id');
                $tool['is_favorite'] = (bool)$favorites;
            }
        }
        //获取工具收藏数
        $tool['favorite_count'] = Db::name('ai_tool_favorite')->where('tool_id', $id)->count();

        //工具回复评论列表
        if($is_get_comment){
            //获取评论点赞总数
            $tool['comments'] = $this->getCommentsByToolId($id, 0,$this->getUserId(),$this->getVisitorId(),$this->getUserId()>0?$this->userType:"guest");
        }

        //获取推荐更多相关工具
        $tool['more_tools'] =[];
        if($is_more_tool>0){
            $tool['more_tools']= $this->getMoreTools($id,4);
        }


        // 获取工具标签
        $tags = Db::name('ai_tool_tag_relation')
            ->alias('r')
            ->join('ai_ai_tool_tag t', 'r.tag_id = t.id')
            ->field('t.id, t.name')
            ->where('r.tool_id', $id)
            ->where('t.is_show', 1)
            ->select()
            ->toArray();

        // 获取工具分类
        $categories = Db::name('ai_tool_category_relation')
            ->alias('r')
            ->join('ai_ai_tool_category c', 'r.category_id = c.id')
            ->field('c.id, c.name')
            ->where('r.tool_id', $id)
            ->select()
            ->toArray();

        $tool['tags'] = $tags;
        $tool['categories'] = $categories;


        // 更新浏览量
        Db::name('ai_tool')->where('id', $id)->inc('views')->update();
        // 记录浏览日志
        $this->recordView($id);

        return json(['code' => 200, 'msg' => Lang::get('aiTool.get_tool_info_success'), 'data' => $tool]);
    }



    /**
     * 记录浏览日志
     */
    private function recordView(mixed $id): void
    {
        try {
            // 获取客户端信息
            $userAgent = Request::header('user-agent', '');
            $ipAddress = Request::ip();
            $referer = Request::header('referer', '');

            // 准备用户数据
            $userId = $this->userId ?? 0;
            $userType = $userId>0?$this->userType: 'guest';
            if ($this->isLogin()) {
                $userId = $this->userId;
                $userType = $this->userType;
            }

            // 准备日志数据
            $viewLogData = [
                'tool_id' => $id,
                'user_id' => $userId,
                'user_type' => $userType,
                'ip' => $ipAddress,
                'user_agent' => $userAgent,
                'referer' => $referer,
                'create_time' => time()
            ];

            // 插入浏览日志
            Db::name('ai_tool_visit_log')->insert($viewLogData);
        } catch (\Exception $e) {
            // 记录浏览日志失败，但不影响文章显示，所以只记录日志
            trace('记录浏览日志失败: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * 获取更多相关工具
     */
    private function getMoreTools(mixed $id,int $limit): array
    {
        $tool =[];
        //根据标签
        $toolIds = Db::name('ai_tool_tag_relation')
            ->alias('r')
            ->join('ai_ai_tool_tag t', 'r.tag_id = t.id')
            ->field('r.tool_id')
            ->where('r.tool_id', '<>', $id)
            ->where('r.tag_id', 'in', function ($query) use ($id) {
                $query->name('ai_tool_tag_relation')
                    ->field('tag_id')
                    ->where('tool_id', $id);
            })
            ->group('r.tool_id')
            ->column('tool_id');
        //根据分类
        $toolIds2 = Db::name('ai_tool_category_relation')
            ->alias('r')
            ->join('ai_ai_tool_category c', 'r.category_id = c.id')
            ->field('r.tool_id')
            ->where('r.tool_id', '<>', $id)
            ->where('r.category_id', 'in', function ($query) use ($id) {
                $query->name('ai_tool_category_relation')
                    ->field('category_id')
                    ->where('tool_id', $id);
            })
            ->group('r.tool_id')
            ->column('tool_id');

        //合并
        $toolIds = array_merge($toolIds, $toolIds2);
        //去重
        $toolIds = array_unique($toolIds);
        if ($toolIds) {
            //根据工具ID 获取工具列表
            $more_tools= Db::name('ai_tool')
                ->where('id', 'in', $toolIds)
                ->where('status', 'published')
                ->limit($limit)
                ->select()
                ->toArray();
            //获取对应的标签 和分类
            if($more_tools){
                $toolIds = array_column($more_tools, 'id');
                // 获取标签关联
                $tagRelations = Db::name('ai_tool_tag_relation')
                    ->alias('r')
                    ->join('ai_ai_tool_tag t', 'r.tag_id = t.id')
                    ->field('r.tool_id, r.tag_id, t.name as tag_name')
                    ->whereIn('r.tool_id', $toolIds)
                    ->where('t.is_show', 1)
                    ->select()
                    ->toArray();
                // 获取分类关联
                $categoryRelations = Db::name('ai_tool_category_relation')
                    ->alias('r')
                    ->join('ai_ai_tool_category c', 'r.category_id = c.id')
                    ->field('r.tool_id, r.category_id, c.name as category_name')
                    ->whereIn('r.tool_id', $toolIds)
                    ->select()
                    ->toArray();
                // 整理标签数据
                $toolTags = [];
                foreach ($tagRelations as $relation) {
                    $toolTags[$relation['tool_id']][] = [
                        'id' => $relation['tag_id'],
                        'name' => $relation['tag_name'],
                    ];
                }
                // 整理分类数据
                $toolCategories = [];
                foreach ($categoryRelations as $relation) {
                    $toolCategories[$relation['tool_id']][] = [
                        'id' => $relation['category_id'],
                        'name' => $relation['category_name'],
                    ];
                }
                // 将标签和分类数据添加到工具列表中
                foreach ($more_tools as &$tool) {
                    $tool['tags'] = $toolTags[$tool['id']] ?? [];
                    $tool['categories'] = $toolCategories[$tool['id']] ?? [];
                }
                unset($tool);
                $tool = $more_tools;
            }
        }
        return  $tool;
    }


}
