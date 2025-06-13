-- AI工具评论表模拟数据
INSERT INTO `ai_ai_tool_comment` (`id`, `tool_id`, `user_id`, `user_type`, `parent_id`, `content`, `rating`, `likes`, `status`, `ip`, `user_agent`, `create_time`, `update_time`) VALUES
-- ChatGPT评论
(1, 1, 1, 'user', 0, 'ChatGPT是我日常工作中必不可少的助手，它帮我解决了很多编程问题，也能写出不错的文案。对于快速获取信息和创意非常有帮助。', 5, 12, 'approved', '192.168.1.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', UNIX_TIMESTAMP()-3600*24*20, UNIX_TIMESTAMP()-3600*24*20),
(2, 1, 2, 'user', 0, '作为一名学生，ChatGPT帮助我理解复杂的概念和完成作业。它的解释通常很清晰，但有时会提供错误信息，需要仔细验证。总体来说是很好的学习工具。', 4, 8, 'approved', '192.168.1.2', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', UNIX_TIMESTAMP()-3600*24*18, UNIX_TIMESTAMP()-3600*24*18),
(3, 1, 3, 'user', 0, '免费版的使用限制有点烦人，但Plus版本的响应速度确实更快。内容质量通常很高，特别是在写作和创意方面。对于日常使用来说非常值得。', 4, 5, 'approved', '192.168.1.3', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0)', UNIX_TIMESTAMP()-3600*24*15, UNIX_TIMESTAMP()-3600*24*15),
(4, 1, 4, 'user', 1, '完全同意！我也发现它在编程方面特别有用，尤其是解释复杂的代码和调试问题。', 0, 3, 'approved', '192.168.1.4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', UNIX_TIMESTAMP()-3600*24*19, UNIX_TIMESTAMP()-3600*24*19),
(5, 1, 5, 'user', 0, 'ChatGPT的回答质量取决于你如何提问。学会正确提问后，它的帮助非常显著。我用它来头脑风暴、写作和学习新概念，都很满意。', 5, 10, 'approved', '192.168.1.5', 'Mozilla/5.0 (iPad; CPU OS 15_0)', UNIX_TIMESTAMP()-3600*24*10, UNIX_TIMESTAMP()-3600*24*10),

-- Midjourney评论
(6, 2, 6, 'user', 0, 'Midjourney彻底改变了我的设计工作流程。它生成的图像质量令人惊叹，特别是最新的版本。虽然价格不便宜，但对于专业创意工作者来说绝对值得。', 5, 15, 'approved', '192.168.1.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', UNIX_TIMESTAMP()-3600*24*19, UNIX_TIMESTAMP()-3600*24*19),
(7, 2, 7, 'user', 0, '作为一个没有绘画技能的人，Midjourney让我能够创建专业级的视觉内容。学习曲线有点陡，需要时间掌握提示工程，但结果令人满意。', 4, 7, 'approved', '192.168.1.7', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', UNIX_TIMESTAMP()-3600*24*17, UNIX_TIMESTAMP()-3600*24*17),
(8, 2, 8, 'user', 0, 'Midjourney的社区非常活跃和有创意，从其他用户那里学到了很多。图像质量很棒，但有时会遇到服务器繁忙的问题。总体来说是革命性的工具。', 4, 6, 'approved', '192.168.1.8', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0)', UNIX_TIMESTAMP()-3600*24*14, UNIX_TIMESTAMP()-3600*24*14),
(9, 2, 9, 'user', 6, '完全同意！它确实改变了创意行业。我发现它特别适合概念艺术和插图工作。', 0, 4, 'approved', '192.168.1.9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', UNIX_TIMESTAMP()-3600*24*18, UNIX_TIMESTAMP()-3600*24*18),
(10, 2, 10, 'user', 0, '价格是唯一的缺点，但考虑到它能生成的图像质量，还是很合理的。我用它创建的插图获得了很多客户好评。强烈推荐给所有创意工作者。', 5, 9, 'approved', '192.168.1.10', 'Mozilla/5.0 (iPad; CPU OS 15_0)', UNIX_TIMESTAMP()-3600*24*9, UNIX_TIMESTAMP()-3600*24*9),

-- Jasper评论
(11, 3, 11, 'user', 0, 'Jasper大大提高了我的内容创作效率。它生成的文案通常需要一些编辑，但作为起点非常有用。特别适合创建营销内容和社交媒体帖子。', 4, 8, 'approved', '192.168.1.11', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', UNIX_TIMESTAMP()-3600*24*18, UNIX_TIMESTAMP()-3600*24*18),
(12, 3, 12, 'user', 0, '作为营销团队负责人，Jasper帮助我们显著提高了内容产出。它的模板很实用，长篇内容助手功能也很强大。价格有点高，但节省的时间绝对值得。', 5, 10, 'approved', '192.168.1.12', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', UNIX_TIMESTAMP()-3600*24*16, UNIX_TIMESTAMP()-3600*24*16),
(13, 3, 13, 'user', 0, 'Jasper的内容质量取决于你提供的指导。给它详细的指示后，结果通常很好。我用它写博客和电子邮件，节省了大量时间。', 4, 6, 'approved', '192.168.1.13', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0)', UNIX_TIMESTAMP()-3600*24*13, UNIX_TIMESTAMP()-3600*24*13),

-- GitHub Copilot评论
(14, 4, 14, 'user', 0, '作为开发者，GitHub Copilot极大地提高了我的编码效率。它不仅能生成简单的代码片段，还能理解复杂的函数逻辑。对于重复性任务特别有用。', 5, 12, 'approved', '192.168.1.14', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', UNIX_TIMESTAMP()-3600*24*17, UNIX_TIMESTAMP()-3600*24*17),
(15, 4, 15, 'user', 0, 'Copilot有时会生成不完全正确的代码，需要仔细审查。但它确实帮助我学习新框架和库，提供了很好的起点。总体来说值得投资。', 4, 7, 'approved', '192.168.1.15', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', UNIX_TIMESTAMP()-3600*24*15, UNIX_TIMESTAMP()-3600*24*15),
(16, 4, 16, 'user', 0, '每月10美元对于它提供的价值来说非常合理。它帮我节省了大量查文档的时间，特别是在使用不熟悉的API时。强烈推荐给所有开发者。', 5, 9, 'approved', '192.168.1.16', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0)', UNIX_TIMESTAMP()-3600*24*12, UNIX_TIMESTAMP()-3600*24*12),

-- Mubert评论
(17, 5, 17, 'user', 0, 'Mubert解决了我寻找免版税音乐的问题。生成的音乐质量很好，适合作为视频背景。免费版已经很实用，但专业版的更多选项确实值得。', 4, 6, 'approved', '192.168.1.17', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', UNIX_TIMESTAMP()-3600*24*16, UNIX_TIMESTAMP()-3600*24*16),
(18, 5, 18, 'user', 0, '作为内容创作者，Mubert为我的视频提供了完美的背景音乐。风格选择丰富，质量一致。唯一的缺点是有时候找到完全匹配的音乐需要一些时间。', 4, 5, 'approved', '192.168.1.18', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', UNIX_TIMESTAMP()-3600*24*14, UNIX_TIMESTAMP()-3600*24*14),
(19, 5, 19, 'user', 0, 'Mubert的音乐生成算法非常智能，能够创建符合特定情绪和场景的音乐。我用它为播客创建了主题音乐，效果很好。', 5, 8, 'approved', '192.168.1.19', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0)', UNIX_TIMESTAMP()-3600*24*11, UNIX_TIMESTAMP()-3600*24*11),

-- DALL-E评论
(20, 6, 20, 'user', 0, 'DALL-E的图像生成质量令人印象深刻，特别是在理解复杂提示方面。它比其他一些工具更好地捕捉细节和风格。免费额度对于偶尔使用足够了。', 5, 10, 'approved', '192.168.1.20', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', UNIX_TIMESTAMP()-3600*24*15, UNIX_TIMESTAMP()-3600*24*15),
(21, 6, 21, 'user', 0, '作为设计师，DALL-E帮助我快速生成概念草图和灵感。它的编辑功能特别有用，可以修改已生成的图像。价格合理，按使用量计费很灵活。', 4, 7, 'approved', '192.168.1.21', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)', UNIX_TIMESTAMP()-3600*24*13, UNIX_TIMESTAMP()-3600*24*13),
(22, 6, 22, 'user', 0, 'DALL-E生成的图像通常很有创意，但有时会遇到一些限制。总体来说是很棒的创意工具，特别是在需要快速视觉概念时。', 4, 6, 'approved', '192.168.1.22', 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0)', UNIX_TIMESTAMP()-3600*24*10, UNIX_TIMESTAMP()-3600*24*10);
