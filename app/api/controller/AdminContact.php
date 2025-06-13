<?php
declare(strict_types=1);

namespace app\api\controller;

use app\BaseController;
use think\facade\Lang;
use think\facade\Db;
use think\facade\Request;
use think\response\Json;
use think\facade\Log;
use think\facade\Config;


/**
 * 管理员联系表单控制器
 * @route('AdminContact')
 */

class AdminContact extends BaseController
{

    /**
     * 初始化方法
     */
    protected function initialize()
    {
        $this->userType = 'admin';
        parent::initialize();
    }

    /**
     * 获取联系表单信息（后台使用）
     * @route('getMessages', 'get')
     */
    public function getMessages(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $page = Request::param('page', 1);
        $limit = Request::param('limit', 10);
        $status = Request::param('status', -1);
        $keyword = Request::param('keyword', '');

        $where = [];

        // 根据状态筛选
        if ($status >= 0) {
            $where[] = ['status', '=', intval($status)];
        }

        // 根据关键词搜索
        if ($keyword) {
            $where[] = ['name|email|subject|message', 'like', "%{$keyword}%"];
        }

        // 查询数据
        $query = Db::name('contact_message')
            ->field('id,name,email,subject,message,ip_address,status,create_time,update_time');

        // 获取分页数据
        $result = $this->getPageList($query, $page, $limit, $where, ['create_time desc']);

        return json(['code' => 200, 'msg' => Lang::get('contact.get_success'), 'data' => $result]);
    }

    /**
     * 获取联系表单详情（后台使用）
     * @route('getMessage', 'get')
     */
    public function getMessage(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $id = Request::param('id');

        if (!$id) {
            return json(['code' => 0, 'msg' => Lang::get('contact.id_required')]);
        }

        $message = Db::name('contact_message')
            ->where('id', $id)
            ->find();

        if (!$message) {
            return json(['code' => 0, 'msg' => Lang::get('contact.message_not_found')]);
        }

        return json(['code' => 200, 'msg' => Lang::get('contact.get_success'), 'data' => $message]);
    }

    /**
     * 更新联系表单状态（后台使用）
     * @route('updateStatus', 'post')
     */
    public function updateStatus(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $id = Request::post('id');
        $status = Request::post('status');

        if (!$id) {
            return json(['code' => 0, 'msg' => Lang::get('contact.id_required')]);
        }

        if ($status === null || !in_array($status, [0, 1, 2])) {
            return json(['code' => 0, 'msg' => Lang::get('contact.invalid_status')]);
        }

        $result = Db::name('contact_message')
            ->where('id', $id)
            ->update([
                'status' => $status,
                'update_time' => time()
            ]);

        if ($result) {
            return json(['code' => 200, 'msg' => Lang::get('contact.update_success')]);
        } else {
            return json(['code' => 0, 'msg' => Lang::get('contact.update_failed')]);
        }
    }

    /**
     * 回复联系表单（后台使用）
     * @route('replyMessage', 'post')
     */
    public function replyMessage(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $id = Request::post('id');
        $reply = Request::post('reply');

        if (!$id) {
            return json(['code' => 0, 'msg' => Lang::get('contact.id_required')]);
        }

        if (!$reply) {
            return json(['code' => 0, 'msg' => Lang::get('contact.reply_required')]);
        }

        // 检查消息是否存在
        $message = Db::name('contact_message')
            ->where('id', $id)
            ->find();

        if (!$message) {
            return json(['code' => 0, 'msg' => Lang::get('contact.message_not_found')]);
        }

        // 获取当前管理员ID
        $adminId = $this->getUserId();

        $currentTime = time();
        $result = Db::name('contact_message')
            ->where('id', $id)
            ->update([
                'status' => 2, // 已回复
                'reply' => $reply,
                'replied_by' => $adminId,
                'replied_time' => $currentTime,
                'update_time' => $currentTime
            ]);

        if ($result) {
            // 发送回复邮件给用户
            $this->sendEmailNotification($message);

            return json(['code' => 200, 'msg' => Lang::get('contact.reply_success')]);
        } else {
            return json(['code' => 0, 'msg' => Lang::get('contact.reply_failed')]);
        }
    }

    /**
     * 删除联系表单（后台使用）
     * @route('deleteMessage', 'post')
     */
    public function deleteMessage(): Json
    {
        if (!$this->isLogin()) {
            return $this->needLogin();
        }

        $id = Request::post('id');

        if (!$id) {
            return json(['code' => 0, 'msg' => Lang::get('contact.id_required')]);
        }

        $result = Db::name('contact_message')
            ->where('id', $id)
            ->delete();

        if ($result) {
            return json(['code' => 200, 'msg' => Lang::get('contact.delete_success')]);
        } else {
            return json(['code' => 0, 'msg' => Lang::get('contact.delete_failed')]);
        }
    }

    /**
     * 发送邮件通知
     * @param array $message 联系表单消息
     */
    /**
     * 发送邮件通知
     * @param array $contactData 联系表单数据
     */
    private function sendEmailNotification(array $contactData): void
    {
        // 检查是否启用了邮件通知
        $emailNotificationEnabled = Db::name('website_config')
            ->where('name', 'contact_email_notification')
            ->value('value');

        if ($emailNotificationEnabled != 1) {
            return;
        }

        // 获取接收通知的邮箱
        $notificationEmail = Db::name('website_config')
            ->where('name', 'contact_notification_email')
            ->value('value');

        if (empty($notificationEmail)) {
            return;
        }

        try {
            // 获取网站名称作为发件人
            $siteName = Db::name('website_config')
                ->where('name', 'site_name')
                ->value('value') ?: 'Website Contact';

            // 邮件标题
            $subject = Lang::get('contact.email_subject', ['subject' => $contactData['subject']]);

            // 邮件内容
            $message = "
                <html>
                <head>
                    <title>" . Lang::get('contact.email_title') . "</title>
                </head>
                <body>
                    <h2>" . Lang::get('contact.email_received') . "</h2>
                    <p><strong>" . Lang::get('contact.email_sender') . "</strong> {$contactData['name']}</p>
                    <p><strong>" . Lang::get('contact.email_sender_email') . "</strong> {$contactData['email']}</p>
                    <p><strong>" . Lang::get('contact.email_subject_label') . "</strong> {$contactData['subject']}</p>
                    <p><strong>" . Lang::get('contact.email_time') . "</strong> " . date('Y-m-d H:i:s', $contactData['create_time']) . "</p>
                    <p><strong>" . Lang::get('contact.email_ip') . "</strong> {$contactData['ip_address']}</p>
                    <p><strong>" . Lang::get('contact.email_message') . "</strong></p>
                    <div style='background-color: #f5f5f5; padding: 15px; border-radius: 5px;'>
                        " . nl2br(htmlspecialchars($contactData['message'])) . "
                    </div>
                    <p>" . Lang::get('contact.email_admin_note') . "</p>
                </body>
                </html>
            ";

            // 邮件头信息
            $headers = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=utf-8',
                "From: {$siteName} <no-reply@" . $_SERVER['HTTP_HOST'] . ">",
                "Reply-To: {$contactData['email']}"
            ];

            // 使用PHP的mail函数发送邮件
            // 注意：服务器需要配置好邮件发送功能
            if (function_exists('mail')) {
                $recipients = explode(',', $notificationEmail);
                foreach ($recipients as $recipient) {
                    $recipient = trim($recipient);
                    if (filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                        mail($recipient, $subject, $message, implode("\r\n", $headers));
                    }
                }

                // 记录发送日志
                Log::info(Lang::get('contact.log_email_success', ['recipients' => $notificationEmail]));
            } else {
                // 如果mail函数不可用，记录到日志
                Log::error(Lang::get('contact.log_email_unavailable'));
            }
        } catch (\Exception $e) {
            // 记录错误日志
            Log::error(Lang::get('contact.log_email_failed', ['error' => $e->getMessage()]));
        }
    }

}