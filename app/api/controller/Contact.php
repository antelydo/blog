<?php

declare(strict_types=1);

namespace app\api\controller;

use app\BaseController;
use think\facade\Db;
use think\facade\Lang;
use think\facade\Log;
use think\response\Json;
use think\facade\Request;

class Contact extends BaseController
{


    /**
     * 用户类型
     * @var string
     */
    protected string $userType;

    /**
     * 构造方法
     */
    protected function initialize()
    {
        $this->userType = 'user';
        $this->userId = $this->getUserId();
    }


    /**
     * 提交联系表单
     * @route('submitForm', 'post')
     */
    public function submitForm(): Json
    {


        // 获取表单数据
        $data = Request::post();

        // 验证表单数据
        $validate = \think\facade\Validate::rule([
            'name' => 'require|max:100',
            'email' => 'require|email|max:255',
            'subject' => 'require|max:255',
            'message' => 'require'
        ]);

        if (!$validate->check($data)) {
            return json(['code' => 0, 'msg' => $validate->getError()]);
        }

        // 检查是否启用了联系表单功能
        $formEnabled = Db::name('website_config')
            ->where('name', 'contact_form_enabled')
            ->value('value');

        if ($formEnabled != 1) {
            return json(['code' => 0, 'msg' => Lang::get('contact.form_disabled')]);
        }

        // 组装数据
        $currentTime = time();
        $contactData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'subject' => $data['subject'],
            'message' => $data['message'],
            'ip_address' => Request::ip(),
            'user_agent' => Request::header('user-agent'),
            'status' => 0, // 未处理
            'create_time' => $currentTime,
            'update_time' => $currentTime
        ];

        // 保存到数据库
        $result = Db::name('contact_message')->insert($contactData);

        if ($result) {
            // 发送邮件通知（如果启用）
            $this->sendEmailNotification($contactData);

            return json(['code' => 200, 'msg' => Lang::get('contact.submit_success')]);
        } else {
            return json(['code' => 0, 'msg' => Lang::get('contact.submit_failed')]);
        }
    }


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
