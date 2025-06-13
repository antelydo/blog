-- AI工具标签关联表模拟数据
INSERT INTO `ai_ai_tool_tag_relation` (`id`, `tool_id`, `tag_id`, `create_time`) VALUES
-- ChatGPT标签关联
(1, 1, 1, UNIX_TIMESTAMP()),  -- ChatGPT - GPT
(2, 1, 7, UNIX_TIMESTAMP()),  -- ChatGPT - 效率
(3, 1, 11, UNIX_TIMESTAMP()), -- ChatGPT - 写作
(4, 1, 18, UNIX_TIMESTAMP()), -- ChatGPT - 聊天机器人
(5, 1, 2, UNIX_TIMESTAMP()),  -- ChatGPT - 免费

-- Midjourney标签关联
(6, 2, 3, UNIX_TIMESTAMP()),  -- Midjourney - 付费
(7, 2, 6, UNIX_TIMESTAMP()),  -- Midjourney - 创意
(8, 2, 10, UNIX_TIMESTAMP()), -- Midjourney - 设计
(9, 2, 16, UNIX_TIMESTAMP()), -- Midjourney - 图像

-- Jasper标签关联
(10, 3, 3, UNIX_TIMESTAMP()),  -- Jasper - 付费
(11, 3, 7, UNIX_TIMESTAMP()),  -- Jasper - 效率
(12, 3, 11, UNIX_TIMESTAMP()), -- Jasper - 写作
(13, 3, 13, UNIX_TIMESTAMP()), -- Jasper - 营销

-- GitHub Copilot标签关联
(14, 4, 3, UNIX_TIMESTAMP()),  -- GitHub Copilot - 付费
(15, 4, 7, UNIX_TIMESTAMP()),  -- GitHub Copilot - 效率
(16, 4, 12, UNIX_TIMESTAMP()), -- GitHub Copilot - 编程
(17, 4, 19, UNIX_TIMESTAMP()), -- GitHub Copilot - 自动化

-- Mubert标签关联
(18, 5, 2, UNIX_TIMESTAMP()),  -- Mubert - 免费
(19, 5, 6, UNIX_TIMESTAMP()),  -- Mubert - 创意
(20, 5, 15, UNIX_TIMESTAMP()), -- Mubert - 语音

-- DALL-E标签关联
(21, 6, 2, UNIX_TIMESTAMP()),  -- DALL-E - 免费
(22, 6, 6, UNIX_TIMESTAMP()),  -- DALL-E - 创意
(23, 6, 10, UNIX_TIMESTAMP()), -- DALL-E - 设计
(24, 6, 16, UNIX_TIMESTAMP()), -- DALL-E - 图像

-- Notion AI标签关联
(25, 7, 3, UNIX_TIMESTAMP()),  -- Notion AI - 付费
(26, 7, 7, UNIX_TIMESTAMP()),  -- Notion AI - 效率
(27, 7, 11, UNIX_TIMESTAMP()), -- Notion AI - 写作
(28, 7, 19, UNIX_TIMESTAMP()), -- Notion AI - 自动化

-- ElevenLabs标签关联
(29, 8, 2, UNIX_TIMESTAMP()),  -- ElevenLabs - 免费
(30, 8, 6, UNIX_TIMESTAMP()),  -- ElevenLabs - 创意
(31, 8, 15, UNIX_TIMESTAMP()), -- ElevenLabs - 语音
(32, 8, 5, UNIX_TIMESTAMP()),  -- ElevenLabs - 专业级

-- Runway标签关联
(33, 9, 2, UNIX_TIMESTAMP()),  -- Runway - 免费
(34, 9, 6, UNIX_TIMESTAMP()),  -- Runway - 创意
(35, 9, 17, UNIX_TIMESTAMP()), -- Runway - 视频
(36, 9, 10, UNIX_TIMESTAMP()), -- Runway - 设计

-- Tableau标签关联
(37, 10, 3, UNIX_TIMESTAMP()),  -- Tableau - 付费
(38, 10, 5, UNIX_TIMESTAMP()),  -- Tableau - 专业级
(39, 10, 9, UNIX_TIMESTAMP()),  -- Tableau - 商业
(40, 10, 14, UNIX_TIMESTAMP()); -- Tableau - 数据分析
