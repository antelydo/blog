-- AI工具分类关联表模拟数据
INSERT INTO `ai_ai_tool_category_relation` (`id`, `tool_id`, `category_id`, `create_time`) VALUES
-- 假设工具1(ChatGPT)属于分类1(文本生成)和分类6(AI助手)
(1, 1, 1, UNIX_TIMESTAMP()),
(2, 1, 6, UNIX_TIMESTAMP()),

-- 假设工具2(Midjourney)属于分类2(图像生成)和分类14(绘画创作)
(3, 2, 2, UNIX_TIMESTAMP()),
(4, 2, 14, UNIX_TIMESTAMP()),

-- 假设工具3(DALL-E)属于分类2(图像生成)
(5, 3, 2, UNIX_TIMESTAMP()),

-- 假设工具4(Claude)属于分类1(文本生成)和分类6(AI助手)
(6, 4, 1, UNIX_TIMESTAMP()),
(7, 4, 6, UNIX_TIMESTAMP()),

-- 假设工具5(Stable Diffusion)属于分类2(图像生成)和分类14(绘画创作)
(8, 5, 2, UNIX_TIMESTAMP()),
(9, 5, 14, UNIX_TIMESTAMP()),

-- 假设工具6(Jasper)属于分类1(文本生成)和分类12(文案创作)
(10, 6, 1, UNIX_TIMESTAMP()),
(11, 6, 12, UNIX_TIMESTAMP()),

-- 假设工具7(GitHub Copilot)属于分类6(AI助手)和分类13(代码生成)
(12, 7, 6, UNIX_TIMESTAMP()),
(13, 7, 13, UNIX_TIMESTAMP()),

-- 假设工具8(Notion AI)属于分类1(文本生成)和分类6(AI助手)
(14, 8, 1, UNIX_TIMESTAMP()),
(15, 8, 6, UNIX_TIMESTAMP()),

-- 假设工具9(Murf AI)属于分类3(音频处理)和分类15(语音合成)
(16, 9, 3, UNIX_TIMESTAMP()),
(17, 9, 15, UNIX_TIMESTAMP()),

-- 假设工具10(Descript)属于分类3(音频处理)和分类15(语音合成)
(18, 10, 3, UNIX_TIMESTAMP()),
(19, 10, 15, UNIX_TIMESTAMP());
