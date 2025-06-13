-- AI工具访问记录表模拟数据
INSERT INTO `ai_ai_tool_visit_log` (`id`, `tool_id`, `user_id`, `user_type`, `ip`, `user_agent`, `referer`, `create_time`) VALUES
-- ChatGPT访问记录（最近30天，每天多条记录）
(1, 1, 1, 'user', '192.168.1.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'https://www.google.com/', UNIX_TIMESTAMP()-3600*24*30),
(2, 1, 2, 'user', '192.168.1.2', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', 'https://www.bing.com/', UNIX_TIMESTAMP()-3600*24*30+3600),
(3, 1, 0, NULL, '192.168.1.100', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0)', 'https://www.baidu.com/', UNIX_TIMESTAMP()-3600*24*30+7200),
(4, 1, 3, 'user', '192.168.1.3', 'Mozilla/5.0 (iPad; CPU OS 15_0)', 'https://www.google.com/', UNIX_TIMESTAMP()-3600*24*29),
(5, 1, 4, 'user', '192.168.1.4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', 'https://www.bing.com/', UNIX_TIMESTAMP()-3600*24*29+3600),
(6, 1, 0, NULL, '192.168.1.101', 'Mozilla/5.0 (Linux; Android 12)', 'https://www.baidu.com/', UNIX_TIMESTAMP()-3600*24*29+7200),
(7, 1, 5, 'user', '192.168.1.5', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', 'https://www.google.com/', UNIX_TIMESTAMP()-3600*24*28),
(8, 1, 6, 'user', '192.168.1.6', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', 'https://www.bing.com/', UNIX_TIMESTAMP()-3600*24*28+3600),
(9, 1, 0, NULL, '192.168.1.102', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0)', 'https://www.baidu.com/', UNIX_TIMESTAMP()-3600*24*28+7200),
(10, 1, 1, 'admin', '192.168.1.201', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', 'https://www.google.com/', UNIX_TIMESTAMP()-3600*24*27),
(11, 1, 7, 'user', '192.168.1.7', 'Mozilla/5.0 (iPad; CPU OS 15_0)', 'https://www.bing.com/', UNIX_TIMESTAMP()-3600*24*27+3600),
(12, 1, 0, NULL, '192.168.1.103', 'Mozilla/5.0 (Linux; Android 12)', 'https://www.baidu.com/', UNIX_TIMESTAMP()-3600*24*27+7200),
(13, 1, 8, 'user', '192.168.1.8', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', 'https://www.google.com/', UNIX_TIMESTAMP()-3600*24*26),
(14, 1, 9, 'user', '192.168.1.9', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', 'https://www.bing.com/', UNIX_TIMESTAMP()-3600*24*26+3600),
(15, 1, 0, NULL, '192.168.1.104', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0)', 'https://www.baidu.com/', UNIX_TIMESTAMP()-3600*24*26+7200),

-- Midjourney访问记录（最近30天，每天多条记录）
(16, 2, 1, 'user', '192.168.1.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'https://www.google.com/', UNIX_TIMESTAMP()-3600*24*30),
(17, 2, 2, 'user', '192.168.1.2', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', 'https://www.bing.com/', UNIX_TIMESTAMP()-3600*24*30+3600),
(18, 2, 0, NULL, '192.168.1.100', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0)', 'https://www.baidu.com/', UNIX_TIMESTAMP()-3600*24*30+7200),
(19, 2, 3, 'user', '192.168.1.3', 'Mozilla/5.0 (iPad; CPU OS 15_0)', 'https://www.google.com/', UNIX_TIMESTAMP()-3600*24*29),
(20, 2, 4, 'user', '192.168.1.4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', 'https://www.bing.com/', UNIX_TIMESTAMP()-3600*24*29+3600),
(21, 2, 0, NULL, '192.168.1.101', 'Mozilla/5.0 (Linux; Android 12)', 'https://www.baidu.com/', UNIX_TIMESTAMP()-3600*24*29+7200),
(22, 2, 5, 'user', '192.168.1.5', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', 'https://www.google.com/', UNIX_TIMESTAMP()-3600*24*28),
(23, 2, 6, 'user', '192.168.1.6', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', 'https://www.bing.com/', UNIX_TIMESTAMP()-3600*24*28+3600),
(24, 2, 0, NULL, '192.168.1.102', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0)', 'https://www.baidu.com/', UNIX_TIMESTAMP()-3600*24*28+7200),
(25, 2, 1, 'admin', '192.168.1.201', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', 'https://www.google.com/', UNIX_TIMESTAMP()-3600*24*27),

-- Jasper访问记录
(26, 3, 1, 'user', '192.168.1.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'https://www.google.com/', UNIX_TIMESTAMP()-3600*24*30),
(27, 3, 2, 'user', '192.168.1.2', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', 'https://www.bing.com/', UNIX_TIMESTAMP()-3600*24*30+3600),
(28, 3, 0, NULL, '192.168.1.100', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0)', 'https://www.baidu.com/', UNIX_TIMESTAMP()-3600*24*30+7200),
(29, 3, 3, 'user', '192.168.1.3', 'Mozilla/5.0 (iPad; CPU OS 15_0)', 'https://www.google.com/', UNIX_TIMESTAMP()-3600*24*29),
(30, 3, 4, 'user', '192.168.1.4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', 'https://www.bing.com/', UNIX_TIMESTAMP()-3600*24*29+3600),
(31, 3, 0, NULL, '192.168.1.101', 'Mozilla/5.0 (Linux; Android 12)', 'https://www.baidu.com/', UNIX_TIMESTAMP()-3600*24*29+7200),
(32, 3, 5, 'user', '192.168.1.5', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', 'https://www.google.com/', UNIX_TIMESTAMP()-3600*24*28),

-- GitHub Copilot访问记录
(33, 4, 1, 'user', '192.168.1.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'https://www.google.com/', UNIX_TIMESTAMP()-3600*24*30),
(34, 4, 2, 'user', '192.168.1.2', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', 'https://www.bing.com/', UNIX_TIMESTAMP()-3600*24*30+3600),
(35, 4, 0, NULL, '192.168.1.100', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0)', 'https://www.baidu.com/', UNIX_TIMESTAMP()-3600*24*30+7200),
(36, 4, 3, 'user', '192.168.1.3', 'Mozilla/5.0 (iPad; CPU OS 15_0)', 'https://www.google.com/', UNIX_TIMESTAMP()-3600*24*29),
(37, 4, 4, 'user', '192.168.1.4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', 'https://www.bing.com/', UNIX_TIMESTAMP()-3600*24*29+3600),
(38, 4, 0, NULL, '192.168.1.101', 'Mozilla/5.0 (Linux; Android 12)', 'https://www.baidu.com/', UNIX_TIMESTAMP()-3600*24*29+7200),

-- Mubert访问记录
(39, 5, 1, 'user', '192.168.1.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'https://www.google.com/', UNIX_TIMESTAMP()-3600*24*30),
(40, 5, 2, 'user', '192.168.1.2', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', 'https://www.bing.com/', UNIX_TIMESTAMP()-3600*24*30+3600),
(41, 5, 0, NULL, '192.168.1.100', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0)', 'https://www.baidu.com/', UNIX_TIMESTAMP()-3600*24*30+7200),
(42, 5, 3, 'user', '192.168.1.3', 'Mozilla/5.0 (iPad; CPU OS 15_0)', 'https://www.google.com/', UNIX_TIMESTAMP()-3600*24*29),
(43, 5, 4, 'user', '192.168.1.4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', 'https://www.bing.com/', UNIX_TIMESTAMP()-3600*24*29+3600),

-- DALL-E访问记录
(44, 6, 1, 'user', '192.168.1.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'https://www.google.com/', UNIX_TIMESTAMP()-3600*24*30),
(45, 6, 2, 'user', '192.168.1.2', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', 'https://www.bing.com/', UNIX_TIMESTAMP()-3600*24*30+3600),
(46, 6, 0, NULL, '192.168.1.100', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0)', 'https://www.baidu.com/', UNIX_TIMESTAMP()-3600*24*30+7200),
(47, 6, 3, 'user', '192.168.1.3', 'Mozilla/5.0 (iPad; CPU OS 15_0)', 'https://www.google.com/', UNIX_TIMESTAMP()-3600*24*29),
(48, 6, 4, 'user', '192.168.1.4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', 'https://www.bing.com/', UNIX_TIMESTAMP()-3600*24*29+3600),
(49, 6, 0, NULL, '192.168.1.101', 'Mozilla/5.0 (Linux; Android 12)', 'https://www.baidu.com/', UNIX_TIMESTAMP()-3600*24*29+7200),

-- Notion AI访问记录
(50, 7, 1, 'user', '192.168.1.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'https://www.google.com/', UNIX_TIMESTAMP()-3600*24*30),
(51, 7, 2, 'user', '192.168.1.2', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', 'https://www.bing.com/', UNIX_TIMESTAMP()-3600*24*30+3600),
(52, 7, 0, NULL, '192.168.1.100', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0)', 'https://www.baidu.com/', UNIX_TIMESTAMP()-3600*24*30+7200),
(53, 7, 3, 'user', '192.168.1.3', 'Mozilla/5.0 (iPad; CPU OS 15_0)', 'https://www.google.com/', UNIX_TIMESTAMP()-3600*24*29),

-- ElevenLabs访问记录
(54, 8, 1, 'user', '192.168.1.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'https://www.google.com/', UNIX_TIMESTAMP()-3600*24*30),
(55, 8, 2, 'user', '192.168.1.2', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', 'https://www.bing.com/', UNIX_TIMESTAMP()-3600*24*30+3600),
(56, 8, 0, NULL, '192.168.1.100', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0)', 'https://www.baidu.com/', UNIX_TIMESTAMP()-3600*24*30+7200),
(57, 8, 3, 'user', '192.168.1.3', 'Mozilla/5.0 (iPad; CPU OS 15_0)', 'https://www.google.com/', UNIX_TIMESTAMP()-3600*24*29),

-- Runway访问记录
(58, 9, 1, 'user', '192.168.1.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'https://www.google.com/', UNIX_TIMESTAMP()-3600*24*30),
(59, 9, 2, 'user', '192.168.1.2', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', 'https://www.bing.com/', UNIX_TIMESTAMP()-3600*24*30+3600),
(60, 9, 0, NULL, '192.168.1.100', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0)', 'https://www.baidu.com/', UNIX_TIMESTAMP()-3600*24*30+7200),

-- Tableau访问记录
(61, 10, 1, 'user', '192.168.1.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', 'https://www.google.com/', UNIX_TIMESTAMP()-3600*24*30),
(62, 10, 2, 'user', '192.168.1.2', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', 'https://www.bing.com/', UNIX_TIMESTAMP()-3600*24*30+3600),
(63, 10, 0, NULL, '192.168.1.100', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0)', 'https://www.baidu.com/', UNIX_TIMESTAMP()-3600*24*30+7200);
