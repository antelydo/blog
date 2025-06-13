-- AI工具相关表模拟数据
-- 创建时间：2024年

-- 清空现有数据（如果需要）
-- TRUNCATE TABLE `ai_tool_category`;
-- TRUNCATE TABLE `ai_tool_tag`;
-- TRUNCATE TABLE `ai_tool`;
-- TRUNCATE TABLE `ai_tool_tag_relation`;
-- TRUNCATE TABLE `ai_tool_category_relation`;
-- TRUNCATE TABLE `ai_tool_comment`;
-- TRUNCATE TABLE `ai_tool_favorite`;
-- TRUNCATE TABLE `ai_tool_like`;
-- TRUNCATE TABLE `ai_tool_visit_log`;

-- 导入AI工具分类表数据
SOURCE database/seeds/ai_tool_category_data.sql;

-- 导入AI工具标签表数据
SOURCE database/seeds/ai_tool_tag_data.sql;

-- 导入AI工具表数据
SOURCE database/seeds/ai_tool_data.sql;

-- 导入AI工具标签关联表数据
SOURCE database/seeds/ai_tool_tag_relation_data.sql;

-- 导入AI工具分类关联表数据
SOURCE database/seeds/ai_tool_category_relation_data.sql;

-- 导入AI工具评论表数据
SOURCE database/seeds/ai_tool_comment_data.sql;

-- 导入AI工具收藏表数据
SOURCE database/seeds/ai_tool_favorite_data.sql;

-- 导入AI工具点赞表数据
SOURCE database/seeds/ai_tool_like_data.sql;

-- 导入AI工具访问记录表数据
SOURCE database/seeds/ai_tool_visit_log_data.sql;

-- 更新标签使用次数
UPDATE `ai_ai_tool_tag` t SET t.count = (SELECT COUNT(*) FROM `ai_ai_tool_tag_relation` r WHERE r.tag_id = t.id);

-- 更新工具评分和评论数
UPDATE `ai_tool` t SET
    t.comments = (SELECT COUNT(*) FROM `ai_ai_tool_comment` c WHERE c.tool_id = t.id AND c.parent_id = 0),
    t.rating_count = (SELECT COUNT(*) FROM `ai_ai_tool_comment` c WHERE c.tool_id = t.id AND c.parent_id = 0 AND c.rating > 0);

-- 更新工具收藏数和点赞数
UPDATE `ai_ai_tool` t SET
    t.favorites = (SELECT COUNT(*) FROM `ai_ai_tool_favorite` f WHERE f.tool_id = t.id),
    t.likes = (SELECT COUNT(*) FROM `ai_ai_tool_like` l WHERE l.tool_id = t.id);

-- 更新工具浏览量
UPDATE `ai_ai_tool` t SET
    t.views = (SELECT COUNT(*) FROM `ai_ai_tool_visit_log` v WHERE v.tool_id = t.id);

-- 输出完成信息
SELECT 'AI工具模拟数据导入完成' AS '结果';
