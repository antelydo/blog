-- AI工具分类表模拟数据
INSERT INTO `ai_ai_tool_category` (`id`, `name`, `slug`, `icon`, `description`, `parent_id`, `sort_order`, `is_show`, `seo_title`, `seo_keywords`, `seo_description`, `create_time`, `update_time`) VALUES
(1, '文本生成', 'text-generation', 'icon-text', '用于生成各种类型文本内容的AI工具', 0, 10, 1, '文本生成AI工具', '文本生成,AI写作,内容创作', '发现最佳文本生成AI工具，提升您的写作和内容创作效率', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(2, '图像生成', 'image-generation', 'icon-image', '用于创建和编辑图像的AI工具', 0, 20, 1, '图像生成AI工具', '图像生成,AI绘画,图像创作', '探索顶级图像生成AI工具，释放您的创意潜能', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(3, '音频处理', 'audio-processing', 'icon-audio', '用于音频创作和处理的AI工具', 0, 30, 1, '音频处理AI工具', '音频处理,AI音乐,语音合成', '发现革命性的音频处理AI工具，提升您的音频创作体验', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(4, '视频创作', 'video-creation', 'icon-video', '用于视频制作和编辑的AI工具', 0, 40, 1, '视频创作AI工具', '视频创作,AI视频,视频编辑', '探索强大的视频创作AI工具，让视频制作更简单高效', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(5, '数据分析', 'data-analysis', 'icon-data', '用于数据处理和分析的AI工具', 0, 50, 1, '数据分析AI工具', '数据分析,AI数据,大数据处理', '发现专业的数据分析AI工具，从数据中获取更多价值', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(6, '编程辅助', 'coding-assistant', 'icon-code', '用于辅助编程和开发的AI工具', 0, 60, 1, '编程辅助AI工具', '编程辅助,AI编程,代码生成', '探索智能的编程辅助AI工具，提高开发效率', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(7, '聊天机器人', 'chatbots', 'icon-chat', '各类AI聊天机器人和对话系统', 0, 70, 1, 'AI聊天机器人', 'AI聊天,聊天机器人,对话系统', '发现最智能的AI聊天机器人，提升用户交互体验', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(8, '教育学习', 'education', 'icon-education', '用于教育和学习的AI工具', 0, 80, 1, '教育学习AI工具', '教育AI,学习辅助,AI教育', '探索革新教育的AI工具，提升学习效果', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(9, '商业营销', 'business-marketing', 'icon-business', '用于商业和营销的AI工具', 0, 90, 1, '商业营销AI工具', '商业AI,营销工具,AI营销', '发现提升业务的AI营销工具，增强市场竞争力', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(10, '生产力工具', 'productivity', 'icon-productivity', '提高工作效率的AI工具', 0, 100, 1, '生产力AI工具', '生产力,效率工具,AI助手', '探索提升工作效率的AI工具，让工作更智能', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(11, '内容写作', 'content-writing', 'icon-writing', '专注于内容写作的AI工具', 1, 11, 1, 'AI内容写作工具', '内容写作,AI写作,文章生成', '发现专业的AI内容写作工具，创作高质量文章', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(12, '文案创作', 'copywriting', 'icon-copy', '用于广告和营销文案的AI工具', 1, 12, 1, 'AI文案创作工具', '文案创作,AI文案,广告文案', '探索智能的AI文案创作工具，提升营销效果', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(13, '代码生成', 'code-generation', 'icon-code-gen', '自动生成代码的AI工具', 6, 61, 1, 'AI代码生成工具', '代码生成,AI编程,自动编码', '发现高效的AI代码生成工具，加速开发过程', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(14, '绘画创作', 'ai-painting', 'icon-painting', 'AI辅助绘画和艺术创作工具', 2, 21, 1, 'AI绘画创作工具', 'AI绘画,数字艺术,创意绘画', '探索革命性的AI绘画工具，释放艺术创造力', UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
(15, '语音合成', 'text-to-speech', 'icon-voice', '将文本转换为语音的AI工具', 3, 31, 1, 'AI语音合成工具', '语音合成,文本转语音,AI配音', '发现自然的AI语音合成工具，创建专业语音内容', UNIX_TIMESTAMP(), UNIX_TIMESTAMP());
