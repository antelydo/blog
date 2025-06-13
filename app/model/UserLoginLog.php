<?php
declare(strict_types=1);

namespace app\model;

use think\Model;

/**
 * 用户登录日志模型
 */
class UserLoginLog extends Model
{
    // 设置表名
    protected $name = 'user_login_log';

    // 设置主键
    protected $pk = 'id';

    // 自动写入时间戳
    protected $autoWriteTimestamp = false;

    // 类型转换
    protected $type = [
        'id' => 'integer',
        'user_id' => 'integer',
        'status' => 'integer',
        'create_time' => 'timestamp',
    ];

    /**
     * 获取登录状态文本
     * @param int $value 状态值
     * @return string
     */
    public function getStatusTextAttr($value, $data)
    {
        $status = [
            0 => '失败',
            1 => '成功'
        ];
        return $status[$data['status']] ?? '未知';
    }

    /**
     * 格式化创建时间
     * @param int $value 时间戳
     * @return string
     */
    public function getCreateTimeAttr($value){
        if (!$value) {
            return '';
        }

        // Handle DateTime object from ThinkPHP
        if ($value instanceof \think\model\type\DateTime) {
            return $value->format('Y-m-d H:i:s');
        }

        // Handle timestamp integer
        if (is_numeric($value)) {
            return date('Y-m-d H:i:s', (int)$value);
        }

        // Handle string date
        if (is_string($value)) {
            return $value;
        }

        return '';
    }

    /**
     * 添加登录日志
     * @param int $userId 用户ID
     * @param string $username 用户名
     * @param string $ipAddress IP地址
     * @param string $device 设备信息
     * @param string $location 位置信息
     * @param string $userAgent 用户代理
     * @param int $status 状态 0失败 1成功
     * @param string $remark 备注
     * @return bool
     */
    public static function addLog(
        int $userId,
        string $username,
        string $ipAddress,
        string $device,
        string $location,
        string $userAgent,
        int $status = 1,
        string $remark = ''
    ): bool {
        try {
            $data = [
                'user_id' => $userId,
                'username' => $username,
                'ip_address' => $ipAddress,
                'device' => $device,
                'location' => $location,
                'user_agent' => $userAgent,
                'status' => $status,
                'remark' => $remark,
                'create_time' => time()
            ];

            return (new self)->save($data);
        } catch (\Exception $e) {
            // 记录错误日志
            \think\facade\Log::error('添加登录日志失败: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 获取用户最近的登录日志
     * @param int $userId 用户ID
     * @param int $limit 限制数量
     * @return array
     */
    public static function getRecentLogs(int $userId, int $limit = 10): array
    {
        return self::where('user_id', $userId)
            ->order('create_time', 'desc')
            ->limit($limit)
            ->select()
            ->toArray();
    }

    /**
     * 获取用户登录统计信息
     * @param int $userId 用户ID
     * @return array
     */
    public static function getUserLoginStats(int $userId): array
    {
        // 总登录次数
        $totalLogins = self::where('user_id', $userId)->count();

        // 成功登录次数
        $successLogins = self::where('user_id', $userId)
            ->where('status', 1)
            ->count();

        // 失败登录次数
        $failedLogins = self::where('user_id', $userId)
            ->where('status', 0)
            ->count();

        // 最后一次登录信息
        $lastLogin = self::where('user_id', $userId)
            ->where('status', 1)
            ->order('create_time', 'desc')
            ->find();

        return [
            'total_logins' => $totalLogins,
            'success_logins' => $successLogins,
            'failed_logins' => $failedLogins,
            'last_login' => $lastLogin ? $lastLogin->toArray() : null
        ];
    }
}
