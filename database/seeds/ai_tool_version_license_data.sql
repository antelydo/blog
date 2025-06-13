-- 更新AI工具表，添加版本和许可证数据
UPDATE `ai_ai_tool` SET 
    `version` = '1.0.0',
    `license` = 'MIT'
WHERE `id` = 1;

-- 更新其他AI工具示例数据
UPDATE `ai_ai_tool` SET 
    `version` = '3.5',
    `license` = 'Commercial'
WHERE `id` = 2;

UPDATE `ai_ai_tool` SET 
    `version` = '2.1.3',
    `license` = 'Apache 2.0'
WHERE `id` = 3;
