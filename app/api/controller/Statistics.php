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
 * @route('statistics')
 */
class statistics extends BaseController {
    // 类名应该以大写字母开头，但为了兼容性保持原来的写法
    /**
     * 获取网站统计信息
     * @route('webStatistics', 'get')
     */
    public function webStatistics(): Json
    {
        $data = [
            'total_posts' => Db::name('blog_post')->count(),
            'total_comments' => Db::name('blog_comment')->count(),
            'total_likes' => Db::name('blog_like')->count(),
            'total_views_log' => Db::name('blog_post_view_log')->count(),
            'total_users' => Db::name('user')->count(),
            'total_index_files' => $this->countFrontendIndexFiles(),
            'total_tags' => Db::name('blog_tag')->count(),
            'total_categories' => Db::name('blog_category')->count(),
            'total_links' => Db::name('friendship_links')->count(),
            'total_messages' => Db::name('user_message')->count(),

            // 浏览时长统计
            'avg_post_view_duration' => $this->getAvgPostViewDuration(),
            'avg_site_view_duration' => $this->getAvgSiteViewDuration(),
            'total_view_time' => $this->getTotalViewTime(),

            // 最近7天的浏览量趋势
            'view_trend' => $this->getViewTrend(7),

            // 最受欢迎的文章（浏览量最高的5篇）
            'popular_posts' => $this->getPopularPosts(5),
        ];
        return json(['code' => 200, 'msg' => Lang::get('statistics.get_statistics_successful'), 'data' => $data]);
    }

    /**
     * 统计当前前端页面 相对路径下frontend\src\views\index文件夹下文件总数
     * @route('frontendFileCount', 'get')
     */
    public function countFrontendIndexFiles(): int
    {
        // 使用相对路径
        $relativePath = 'frontend/src/views/index';
        $rootPath = root_path();
        $directory = $rootPath . $relativePath;

        // 检查目录是否存在
        if (!is_dir($directory)) {
            return 0;
        }

        return $this->countFilesRecursively($directory);
    }

    /**
     * 递归统计目录下的文件数量
     * @param string $dir 目录路径
     * @return int 文件数量
     */
    private function countFilesRecursively(string $dir): int
    {
        $count = 0;
        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                $count += $this->countFilesRecursively($path);
            } else {
                $count++;
            }
        }

        return $count;
    }

    /**
     * 获取前端文件详细统计信息
     * @route('frontendFileStats', 'get')
     */
    public function getFrontendFileStats(): Json
    {
        // 使用相对路径
        $relativePath = 'frontend/src/views/index';
        $rootPath = root_path();
        $directory = $rootPath . $relativePath;

        // 检查目录是否存在
        if (!is_dir($directory)) {
            return json(['code' => 404, 'msg' => Lang::get('statistics.directory_not_found'), 'data' => null]);
        }

        $fileTypes = [];
        $dirCount = 0;
        $files = array_diff(scandir($directory), ['.', '..']);

        foreach ($files as $file) {
            $path = $directory . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                $dirCount++;
                $subFiles = $this->countFilesRecursively($path);
                $fileTypes['directories'][$file] = $subFiles;
            } else {
                $extension = pathinfo($path, PATHINFO_EXTENSION);
                if (!isset($fileTypes['extensions'][$extension])) {
                    $fileTypes['extensions'][$extension] = 0;
                }
                $fileTypes['extensions'][$extension]++;
            }
        }

        $data = [
            'total_files' => $this->countFilesRecursively($directory),
            'main_directory_files' => count($files) - $dirCount,
            'directories_count' => $dirCount,
            'file_types' => $fileTypes,
            'path' => $relativePath
        ];

        return json(['code' => 200, 'msg' => Lang::get('statistics.get_frontend_files_successful'), 'data' => $data]);
    }

    /**
     * 更新网站浏览时长
     * @route('update_site_duration', 'post')
     */
    public function updateSiteDuration(): Json
    {
        $pageType = input('page_type', 'site');
        $pageId = input('page_id', null);
        $duration = input('duration', 0);
        $isHeartbeat = input('is_heartbeat', false);

        if (!$duration) {
            return json(['code' => 400, 'msg' => Lang::get('statistics.invalid_params')]);
        }

        try {
            // 准备用户数据
            $userId = $this->userId ?? 0;
            $userType = $this->userId>0?$this->userType : 'guest';
            $ipAddress = Request::ip();
            $userAgent = Request::header('user-agent', '');
            $referer = Request::header('referer', '');

            // 如果是心跳更新，尝试更新最近的记录
            if ($isHeartbeat) {
                $where = [
                    ['ip_address', '=', $ipAddress],
                    ['page_type', '=', $pageType]
                ];

                if ($pageId) {
                    $where[] = ['page_id', '=', $pageId];
                }

                if ($userId > 0) {
                    $where[] = ['user_id', '=', $userId];
                    $where[] = ['user_type', '=', $userType];
                }

                // 找到最近的一条浏览记录
                $viewLog = Db::name('site_view_log')
                    ->where($where)
                    ->order('create_time', 'desc')
                    ->find();

                if ($viewLog) {
                    // 如果找到浏览记录，则更新浏览时长
                    Db::name('site_view_log')
                        ->where('id', $viewLog['id'])
                        ->update(['view_duration' => intval($duration)]);

                    return json(['code' => 200, 'msg' => Lang::get('statistics.update_duration_successful')]);
                }
            }

            // 如果是心跳更新但没找到记录，或者不是心跳更新，则创建新记录
            $viewLogData = [
                'user_id' => $userId,
                'user_type' => $userType,
                'page_type' => $pageType,
                'page_id' => $pageId,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'referer' => $referer,
                'view_duration' => intval($duration),
                'create_time' => time()
            ];

            // 插入浏览日志
            Db::name('site_view_log')->insert($viewLogData);

            return json(['code' => 200, 'msg' => Lang::get('statistics.update_duration_successful')]);
        } catch (\Exception $e) {
            return json(['code' => 500, 'msg' => Lang::get('statistics.update_duration_failed') . ': ' . $e->getMessage()]);
        }
    }

    /**
     * 获取平均文章浏览时长（秒）
     * @return float
     */
    private function getAvgPostViewDuration(): float
    {
        $result = Db::name('blog_post_view_log')
            ->where('view_duration', '>', 0)
            ->avg('view_duration');

        return round(floatval($result), 2);
    }

    /**
     * 获取平均网站浏览时长（秒）
     * @return float
     */
    private function getAvgSiteViewDuration(): float
    {
        $result = Db::name('site_view_log')
            ->where('view_duration', '>', 0)
            ->avg('view_duration');

        return round(floatval($result), 2);
    }

    /**
     * 获取总浏览时长（小时）
     * @return float
     */
    private function getTotalViewTime(): float
    {
        // 文章浏览总时长
        $postViewTime = Db::name('blog_post_view_log')
            ->sum('view_duration');

        // 网站浏览总时长
        $siteViewTime = Db::name('site_view_log')
            ->sum('view_duration');

        // 总时长（秒）
        $totalSeconds = floatval($postViewTime) + floatval($siteViewTime);

        // 转换为小时
        return round($totalSeconds / 3600, 2);
    }

    /**
     * 获取最近n天的浏览量趋势
     * @param int $days 天数
     * @return array
     */
    private function getViewTrend(int $days = 7): array
    {
        $result = [];
        $endDate = strtotime(date('Y-m-d'));
        $startDate = $endDate - ($days * 86400);

        // 按天统计文章浏览量
        for ($i = 0; $i < $days; $i++) {
            $dayStart = $startDate + ($i * 86400);
            $dayEnd = $dayStart + 86400;
            $date = date('Y-m-d', $dayStart);

            // 文章浏览量
            $postViews = Db::name('blog_post_view_log')
                ->where('create_time', '>=', $dayStart)
                ->where('create_time', '<', $dayEnd)
                ->count();

            // 网站浏览量
            $siteViews = Db::name('site_view_log')
                ->where('create_time', '>=', $dayStart)
                ->where('create_time', '<', $dayEnd)
                ->count();

            $result[] = [
                'date' => $date,
                'post_views' => $postViews,
                'site_views' => $siteViews,
                'total_views' => $postViews + $siteViews
            ];
        }

        return $result;
    }

    /**
     * 获取最受欢迎的文章
     * @param int $limit 数量限制
     * @return array
     */
    private function getPopularPosts(int $limit = 5): array
    {
        // 获取浏览量最高的文章
        $posts = Db::name('blog_post')
            ->field('id, title, views, thumbnail')
            ->where('status', 1)
            ->order('views', 'desc')
            ->limit($limit)
            ->select()
            ->toArray();

        // 获取每篇文章的平均浏览时长
        foreach ($posts as &$post) {
            $avgDuration = Db::name('blog_post_view_log')
                ->where('post_id', $post['id'])
                ->where('view_duration', '>', 0)
                ->avg('view_duration');

            $post['avg_duration'] = round(floatval($avgDuration), 2);
        }

        return $posts;
    }
}
