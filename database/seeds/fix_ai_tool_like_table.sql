-- 修复ai_tool_like表结构，移除唯一键约束
ALTER TABLE `ai_ai_tool_like` DROP INDEX `tool_id_2`;

-- 如果上面的命令不起作用，可能是索引名称不同，尝试以下命令
-- 首先查看表的索引
-- SHOW INDEX FROM `ai_tool_like`;

-- 然后删除唯一键约束（替换为实际的索引名）
-- ALTER TABLE `ai_tool_like` DROP INDEX `索引名称`;
