-- 更新AI工具表，添加新字段数据
UPDATE `ai_ai_tool` SET 
    `platforms` = '["Web", "iOS", "Android"]',
    `languages` = '["中文", "英文"]',
    `features` = '["无广告搜索体验", "AI直接回答问题", "客观多样的信息呈现", "专业领域深度搜索", "来源透明的参考链接", "简洁高效的界面"]',
    `usage_tips` = '["可以直接提问复杂问题获得综合回答", "使用"专业搜索"功能查询特定领域信息", "点击参考链接可以查看原始信息来源", "使用引号可以进行精确短语搜索"]',
    `company` = '秘塔科技'
WHERE `id` = 1;

-- 更新其他AI工具示例数据
UPDATE `ai_ai_tool` SET 
    `platforms` = '["Web"]',
    `languages` = '["中文", "英文"]',
    `features` = '["智能对话", "知识问答", "文本生成", "内容创作"]',
    `usage_tips` = '["使用清晰的指令获得更好的结果", "提供足够的上下文信息", "分步骤提问复杂问题"]',
    `company` = 'OpenAI'
WHERE `id` = 2;

UPDATE `ai_ai_tool` SET 
    `platforms` = '["Web", "iOS", "Android", "Windows", "macOS"]',
    `languages` = '["中文", "英文", "日文", "韩文", "法文", "德文", "西班牙文"]',
    `features` = '["实时翻译", "多语言支持", "语音识别", "图片翻译", "离线翻译"]',
    `usage_tips` = '["长按文本可快速翻译", "使用相机功能可直接翻译图片中的文字", "下载语言包可在无网络环境使用"]',
    `company` = 'Google'
WHERE `id` = 3;
