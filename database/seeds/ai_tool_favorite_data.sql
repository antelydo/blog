-- AI工具收藏表模拟数据
INSERT INTO `ai_ai_tool_favorite` (`id`, `tool_id`, `user_id`, `user_type`, `create_time`) VALUES
-- ChatGPT收藏
(1, 1, 1, 'user', UNIX_TIMESTAMP()-3600*24*30),
(2, 1, 2, 'user', UNIX_TIMESTAMP()-3600*24*29),
(3, 1, 3, 'user', UNIX_TIMESTAMP()-3600*24*28),
(4, 1, 4, 'user', UNIX_TIMESTAMP()-3600*24*27),
(5, 1, 5, 'user', UNIX_TIMESTAMP()-3600*24*26),
(6, 1, 6, 'user', UNIX_TIMESTAMP()-3600*24*25),
(7, 1, 7, 'user', UNIX_TIMESTAMP()-3600*24*24),
(8, 1, 8, 'user', UNIX_TIMESTAMP()-3600*24*23),
(9, 1, 9, 'user', UNIX_TIMESTAMP()-3600*24*22),
(10, 1, 10, 'user', UNIX_TIMESTAMP()-3600*24*21),
(11, 1, 11, 'user', UNIX_TIMESTAMP()-3600*24*20),
(12, 1, 12, 'user', UNIX_TIMESTAMP()-3600*24*19),
(13, 1, 13, 'user', UNIX_TIMESTAMP()-3600*24*18),
(14, 1, 14, 'user', UNIX_TIMESTAMP()-3600*24*17),
(15, 1, 15, 'user', UNIX_TIMESTAMP()-3600*24*16),
(16, 1, 1, 'admin', UNIX_TIMESTAMP()-3600*24*15),
(17, 1, 2, 'admin', UNIX_TIMESTAMP()-3600*24*14),

-- Midjourney收藏
(18, 2, 1, 'user', UNIX_TIMESTAMP()-3600*24*30),
(19, 2, 2, 'user', UNIX_TIMESTAMP()-3600*24*29),
(20, 2, 3, 'user', UNIX_TIMESTAMP()-3600*24*28),
(21, 2, 4, 'user', UNIX_TIMESTAMP()-3600*24*27),
(22, 2, 5, 'user', UNIX_TIMESTAMP()-3600*24*26),
(23, 2, 6, 'user', UNIX_TIMESTAMP()-3600*24*25),
(24, 2, 7, 'user', UNIX_TIMESTAMP()-3600*24*24),
(25, 2, 8, 'user', UNIX_TIMESTAMP()-3600*24*23),
(26, 2, 9, 'user', UNIX_TIMESTAMP()-3600*24*22),
(27, 2, 10, 'user', UNIX_TIMESTAMP()-3600*24*21),
(28, 2, 11, 'user', UNIX_TIMESTAMP()-3600*24*20),
(29, 2, 12, 'user', UNIX_TIMESTAMP()-3600*24*19),
(30, 2, 1, 'admin', UNIX_TIMESTAMP()-3600*24*18),

-- Jasper收藏
(31, 3, 1, 'user', UNIX_TIMESTAMP()-3600*24*30),
(32, 3, 2, 'user', UNIX_TIMESTAMP()-3600*24*29),
(33, 3, 3, 'user', UNIX_TIMESTAMP()-3600*24*28),
(34, 3, 4, 'user', UNIX_TIMESTAMP()-3600*24*27),
(35, 3, 5, 'user', UNIX_TIMESTAMP()-3600*24*26),
(36, 3, 6, 'user', UNIX_TIMESTAMP()-3600*24*25),
(37, 3, 7, 'user', UNIX_TIMESTAMP()-3600*24*24),
(38, 3, 8, 'user', UNIX_TIMESTAMP()-3600*24*23),
(39, 3, 9, 'user', UNIX_TIMESTAMP()-3600*24*22),
(40, 3, 10, 'user', UNIX_TIMESTAMP()-3600*24*21),

-- GitHub Copilot收藏
(41, 4, 1, 'user', UNIX_TIMESTAMP()-3600*24*30),
(42, 4, 2, 'user', UNIX_TIMESTAMP()-3600*24*29),
(43, 4, 3, 'user', UNIX_TIMESTAMP()-3600*24*28),
(44, 4, 4, 'user', UNIX_TIMESTAMP()-3600*24*27),
(45, 4, 5, 'user', UNIX_TIMESTAMP()-3600*24*26),
(46, 4, 6, 'user', UNIX_TIMESTAMP()-3600*24*25),
(47, 4, 7, 'user', UNIX_TIMESTAMP()-3600*24*24),
(48, 4, 8, 'user', UNIX_TIMESTAMP()-3600*24*23),
(49, 4, 1, 'admin', UNIX_TIMESTAMP()-3600*24*22),

-- Mubert收藏
(50, 5, 1, 'user', UNIX_TIMESTAMP()-3600*24*30),
(51, 5, 2, 'user', UNIX_TIMESTAMP()-3600*24*29),
(52, 5, 3, 'user', UNIX_TIMESTAMP()-3600*24*28),
(53, 5, 4, 'user', UNIX_TIMESTAMP()-3600*24*27),
(54, 5, 5, 'user', UNIX_TIMESTAMP()-3600*24*26),
(55, 5, 6, 'user', UNIX_TIMESTAMP()-3600*24*25),
(56, 5, 7, 'user', UNIX_TIMESTAMP()-3600*24*24),

-- DALL-E收藏
(57, 6, 1, 'user', UNIX_TIMESTAMP()-3600*24*30),
(58, 6, 2, 'user', UNIX_TIMESTAMP()-3600*24*29),
(59, 6, 3, 'user', UNIX_TIMESTAMP()-3600*24*28),
(60, 6, 4, 'user', UNIX_TIMESTAMP()-3600*24*27),
(61, 6, 5, 'user', UNIX_TIMESTAMP()-3600*24*26),
(62, 6, 6, 'user', UNIX_TIMESTAMP()-3600*24*25),
(63, 6, 7, 'user', UNIX_TIMESTAMP()-3600*24*24),
(64, 6, 8, 'user', UNIX_TIMESTAMP()-3600*24*23),
(65, 6, 9, 'user', UNIX_TIMESTAMP()-3600*24*22),

-- Notion AI收藏
(66, 7, 1, 'user', UNIX_TIMESTAMP()-3600*24*30),
(67, 7, 2, 'user', UNIX_TIMESTAMP()-3600*24*29),
(68, 7, 3, 'user', UNIX_TIMESTAMP()-3600*24*28),
(69, 7, 4, 'user', UNIX_TIMESTAMP()-3600*24*27),
(70, 7, 5, 'user', UNIX_TIMESTAMP()-3600*24*26),
(71, 7, 6, 'user', UNIX_TIMESTAMP()-3600*24*25),

-- ElevenLabs收藏
(72, 8, 1, 'user', UNIX_TIMESTAMP()-3600*24*30),
(73, 8, 2, 'user', UNIX_TIMESTAMP()-3600*24*29),
(74, 8, 3, 'user', UNIX_TIMESTAMP()-3600*24*28),
(75, 8, 4, 'user', UNIX_TIMESTAMP()-3600*24*27),
(76, 8, 5, 'user', UNIX_TIMESTAMP()-3600*24*26),

-- Runway收藏
(77, 9, 1, 'user', UNIX_TIMESTAMP()-3600*24*30),
(78, 9, 2, 'user', UNIX_TIMESTAMP()-3600*24*29),
(79, 9, 3, 'user', UNIX_TIMESTAMP()-3600*24*28),
(80, 9, 4, 'user', UNIX_TIMESTAMP()-3600*24*27),

-- Tableau收藏
(81, 10, 1, 'user', UNIX_TIMESTAMP()-3600*24*30),
(82, 10, 2, 'user', UNIX_TIMESTAMP()-3600*24*29),
(83, 10, 3, 'user', UNIX_TIMESTAMP()-3600*24*28);
