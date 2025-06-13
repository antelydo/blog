<?php

declare(strict_types=1);

namespace app;

use think\App;
use think\exception\ValidateException;
use think\Validate;
use think\facade\Lang;
use think\facade\Request;
use think\facade\Db;
use think\db\Query;
use app\common\Token;
use think\facade\Log;


/**
 * 控制器基础类
 */
abstract class BaseController
{
    /**
     * Request实例
     * @var \think\Request
     */
    protected \think\Request $request;

    /**
     * 应用实例
     * @var App
     */
    protected App $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected bool $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected array $middleware = [];

    /**
     * 当前登录用户ID
     * @var int
     */
    protected int $userId = 0;

    /**
     * 当前用户信息
     * @var array
     */
    protected array $user = [];

    /**
     * 用户类型
     * @var string
     */
    protected string $userType = 'user';

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->request = $this->app->request;

        // 控制器初始化
        $this->initialize();
    }

    // 初始化
    protected function initialize()
    {
        // 检查Token并获取用户信息
        $this->checkToken();
    }


    /**
     * 检查Token并获取用户信息
     */
    protected function checkToken()
    {
        // 从请求头或参数获取Token
        // 检查Token是否符合规则 去掉 "Bearer  用于获取 换缓存
        $token = Token::getRequestToken();
      //  DeBugLog::recordController('BaseController', 'checkToken', ['token'=>$token], $token, 1000, '从请求头或参数获取Token');
        if ($token) {
            // 使用Token类验证token
            $user = Token::verifyToken($token, $this->userType);
           // DeBugLog::recordController('BaseController', 'verifyToken', ['user'=>$user], json_encode(['type'=>$this->userType,'user'=>$user,]), 1000, '使用Token类验证token验证的用户');
            if ($user) {
                $this->userId = $user['id'];
                $this->user = $user;

                // 更新最后活跃时间
                Db::name($this->userType == 'admin' ? 'admin' : 'user')->where('id', $this->userId)->update([
                    'last_active_time' => time()
                ]);
            }
        }
    }

    /**
     * 创建Token
     * @param int $userId 用户ID
     * @return string
     */
    protected function createToken(int $userId): string
    {
        $userData = Db::name($this->userType == 'admin' ? 'admin' : 'user')
            ->where('id', $userId)
            ->find();

        if (!$userData) {
            return '';
        }

        return Token::generateToken($userData, $this->userType);
    }

    /**
     * 验证数据
     * @access protected
     * @param  array        $data     数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array        $message  提示信息
     * @param  bool         $batch    是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }

    /**
     * 检查是否登录
     * @return bool
     */
    protected function isLogin(): bool
    {
        return $this->userId > 0;
    }

    /**
     * 需要登录
     * @return \think\response\Json
     */
    protected function needLogin()
    {
        return json([
            'code' => 401,
            'msg' => Lang::get('user.please_login_first')
        ]);
    }

    /**
     * 无权限访问
     * @return \think\response\Json
     */
    protected function forbidden()
    {
        return json([
            'code' => 403,
            'msg' => Lang::get('user.access_denied')
        ]);
    }


    /**
     * 统一分页查询方法
     * @param mixed $query 查询构造器 (think\db\Query|think\facade\Db)
     * @param int $page 第几页 默认1
     * @param int $limit 当前页显示数量 默认10
     * @param array $where 查询条件
     * @param array $order 排序
     * @return array
     */
    protected function getPageList($query, int $page = 1, int $limit = 10, array $where = [], array $order = ['id' => 'desc']): array
    {
        // 确保传入的查询对象是 think\db\Query 类型
        if (!$query instanceof Query) {
            Log::info('传入getPageList的查询对象类型不是Query，正在尝试转换');
            // 如果不是Query对象，尝试进行转换
            if (is_string($query)) {
                // 如果传入的是字符串，则使用Db::name()创建查询对象
                $query = Db::name($query);
            }
            // 转换后再次检查
            if (!$query instanceof Query) {
                Log::error('无法将查询对象转换为Query类型');
                return [
                    'list' => [],
                    'total' => 0,
                    'page' => $page,
                    'limit' => $limit
                ];
            }
        }

        // 获取总数
        $total = $query->where($where)->count();

        // 处理排序数组
        // 如果是索引数组形式的排序条件，例如 ['id desc', 'create_time desc']
        // 转换为 ThinkPHP 的排序数组格式
        if (!empty($order) && isset($order[0])) {
            $processedOrder = [];
            foreach ($order as $item) {
                // 检查是否是字符串排序条件（如 'id desc'）
                if (is_string($item) && strpos($item, ' ') !== false) {
                    list($field, $direction) = explode(' ', $item);
                    $processedOrder[$field] = $direction;
                } else {
                    // 如果已经是关联数组格式，则直接使用
                    if (is_array($item)) {
                        $processedOrder = array_merge($processedOrder, $item);
                    }
                }
            }

            // 如果处理后有排序条件，则使用处理后的
            if (!empty($processedOrder)) {
                $order = $processedOrder;
            }
        }

        // 记录排序参数
        Log::info('分页查询排序参数: ' . json_encode($order));

        // 执行查询
        $list = $query->where($where)
            ->order($order)
            ->page($page, $limit)
            ->select()
            ->toArray();

        //记录查询结果
        $data = [
            'total' => $total,
            'current_count' => count($list),
            'page' => $page,
            'limit' => $limit
        ];
        Log::info('分页查询结果' . json_encode($data));

        return [
            'list' => $list,
            'total' => $total,
            'page' => $page,
            'limit' => $limit
        ];
    }

    /**
     * 成功返回
     * @param mixed $data 返回数据
     * @param string $msg 提示信息
     * @return \think\response\Json
     */
    protected function success($data = [], string $msg = 'Success')
    {
        return json(data: [
            'code' => 200,
            'msg' => $msg,
            'data' => $data
        ]);
    }

    /**
     * 失败返回
     * @param string $msg 提示信息
     * @param mixed $data 返回数据
     * @return \think\response\Json
     */
    protected function error(string $msg = 'Error', $data = [])
    {
        return json([
            'code' => 0,
            'msg' => $msg,
            'data' => $data
        ]);
    }

    /**
     * 记录用户活动
     * @param int $user_id 用户ID
     * @param string $userType 用户类型
     * @param string $action 操作类型
     * @param string $description 描述
     * @param array $data 附加数据
     */
    protected function recordActivity(int $user_id, string $userType, string $action, string $description, array $data = []): void
    {
        if (!$this->isLogin()) {
            return;
        }

        $activity = [
            'user_id' => $user_id ?? $this->userId,
            'user_type' => $userType ?? $this->userType,
            'action' => $action,
            'description' => $description,
            'ip' => Request::ip(),
            'user_agent' => Request::header('user-agent'),
            'data' => $data ? json_encode($data, JSON_UNESCAPED_UNICODE) : '',
            'create_time' => time()
        ];

        Db::name('user_activity_log')->insert($activity);
    }

    /**
     * 获取用户ID
     * @return int
     */
    protected function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * 获取用户类型
     * @return string
     */
    protected function getUserType(): string
    {
        return $this->userType;
    }


    /**
     * 用户文章博客
     * 获取用户UUID
     * @return string
     */
    protected function getUuid(): string
    {
        try {
            return Request::header('uuid')??'';
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * 用户AI工具
     * 获取访客UUID
     * @return string
     */
    protected function getVisitorId(): string
    {
        $result ='';    
        try {
            $result = Request::header('x-visitor-id')??Request::header('tool_uuid')??Request::header('uuid');            
        } catch (Exception $e) {
            return $result;
        }
    }



    /**
     * 获取网站配置
     * @return array
     */
    public function getWebConfig(): array
    {
        $config =db::name('website_config')->select()->toArray();
        if ($config) {
            foreach ($config as $k => $v) {
                $config[$v['name']] = $v;
                unset($config[$k]);
            }
        }
        return $config;
    }


    /**
     * 用于递归寻找子级评论
     * 获取评论和回复 并按parent_id进行层级组建评论与回复
     * @param int $postId 文章ID
     * @param int $parentId 父评论ID
     * @param bool $isGetLikeCount 是否获取点赞数量
     * @return array
     */
    protected function getCommentsByPostId(int $postId, int $parentId = 0, bool $isGetLikeCount = false): array
    {
        $comments = Db::name('blog_comment')
            ->where('post_id', $postId)
            ->where('parent_id', $parentId)
            ->where('status', 1)
            ->order('create_time', 'desc')
            ->select()
            ->toArray();

        // 如果需要获取点赞数量且有评论
        if ($isGetLikeCount && !empty($comments)) {
            // 收集评论 ID
            $commentIds = array_column($comments, 'id');

            // 获取评论点赞数量
            $likeCounts = Db::name('comment_like')
                ->field('comment_id, COUNT(*) as like_count')
                ->where('comment_id', 'in', $commentIds)
                ->group('comment_id')
                ->select()
                ->toArray();

            // 将结果转换为以评论 ID 为键的数组
            $likeCountMap = [];
            foreach ($likeCounts as $count) {
                $likeCountMap[$count['comment_id']] = $count['like_count'];
            }
        }

        foreach ($comments as &$comment) {
            //根据评论者类型获取评论者信息 以及头像 昵称 用户名
            switch ($comment['user_type']){
                case 'admin':
                    $comment['user'] = Db::name('admin')->field('id, username, nickname, avatar')->find($comment['user_id']);
                    break;
                case 'user':
                    $comment['user'] = Db::name('user')->field('id, username, nickname, avatar')->find($comment['user_id']);
                    break;
                case  'guest':
                    $comment['user'] = ['id' => 0, 'username' => $comment['user_name'], 'nickname' => $comment['user_name'], 'avatar' => ''];
                    break;
            }
            $comment['author'] = $comment['user']['username'] ?? Lang::get('blog.unknown');
            $comment['avatar'] = $comment['user']['avatar'] ?? '';
            $comment['nickname'] = $comment['user']['nickname'] ?? '';
            $comment['create_time'] = $comment['create_time']?date('Y-m-d H:i:s', $comment['create_time']):'';
            $comment['update_time'] = $comment['update_time']?date('Y-m-d H:i:s', $comment['update_time']):'';

            // 添加点赞数量
            if ($isGetLikeCount) {
                $comment['like_count'] = $likeCountMap[$comment['id']] ?? 0;
            }

            // 递归获取子评论，并传递是否获取点赞数量参数
            $comment['replies'] = $this->getCommentsByPostId($postId, $comment['id'], $isGetLikeCount);
        }

        return $comments;
    }

    /**
     * 用于递归寻找子级评论
     * 获取评论和回复 并按parent_id进行层级组建评论与回复
     * @param int $toolId 工具ID
     * @param int $parentId 父评论ID
     * @return array
     */
    protected function getCommentsByToolId(int $toolId, int $parentId = 0,int $userId = 0,string $uuid = '',string $userType = ''): array
    {
        $comments = Db::name('ai_tool_comment')
            ->where('tool_id', $toolId)
            ->where('parent_id', $parentId)
            ->where('status', 'approved')
            ->order('create_time', 'desc')
            ->select()
            ->toArray();
        if (!$comments) {
            return [];
        }
        foreach ($comments as &$comment) {
            //根据评论者类型获取评论者信息 以及头像 昵称 用户名
            switch ($comment['user_type']){
                case 'admin':
                    $comment['user'] = Db::name('admin')->field('id, username, nickname, avatar')->find($comment['user_id']);
                    break;
                case 'user':
                    $comment['user'] = Db::name('user')->field('id, username, nickname, avatar')->find($comment['user_id']);
                    break;
                case  'guest':
                    $comment['user'] = ['id' => 0, 'username' => '匿名用户', 'nickname' => '访客', 'avatar' => ''];
                    break;
            }
            $comment['author'] = $comment['user']['username'];
            $comment['avatar'] = $comment['user']['avatar'] ?? '';
            $comment['nickname'] = $comment['user']['nickname'] ?? '';
            $comment['create_time'] = $comment['create_time']?date('Y-m-d H:i:s', $comment['create_time']):'';
            $comment['update_time'] = $comment['update_time']? date('Y-m-d H:i:s', $comment['update_time']):'';

            //检查当前用户或者访客是否已经店在哪
            $comment['is_liked'] = false;
            if ($this->isLogin()) {
                $liked = Db::name('ai_tool_comment_like')
                    ->where('comment_id', $comment['id'])
                    ->where('user_id', $this->userId)
                    ->where('user_type', $this->userType)
                    ->value('id');
                $comment['is_liked'] = (bool)$liked;
            } else {
                $liked = Db::name('ai_tool_comment_like')
                    ->where('comment_id', $comment['id'])
                    ->where('user_id', 0)
                    ->where('user_type', 'guest')
                    ->where('uuid', $uuid)
                    ->value('id');
                $comment['is_liked'] = (bool)$liked;
            }

            // 递归获取子评论，并传递是否获取点赞数量参数
            $comment['replies'] = $this->getCommentsByToolId($toolId, $comment['id'],$userId,$uuid,$userType);
        }

        return $comments;
    }



}

