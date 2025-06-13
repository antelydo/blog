ALTER TABLE `ai_blog_like` CHANGE `user_type` `user_type` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'user' COMMENT '用户类型：user用户，admin管理员;guest:访客';
ALTER TABLE `ai_blog_like` CHANGE `user_id` `user_id` INT NULL DEFAULT '0' COMMENT '用户ID';
ALTER TABLE `ai_blog_comment` CHANGE `user_type` `user_type` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'user' COMMENT '用户类型：user用户，admin管理员，guest 访客';
ALTER TABLE `ai_blog_comment` ADD `user_name` VARCHAR(80) NULL DEFAULT NULL COMMENT '访客输入的用户名称' AFTER `user_type`, ADD `email` VARCHAR(80) NULL DEFAULT NULL COMMENT '访客输入的邮箱' AFTER `user_name`;



//AI工具点赞
ALTER TABLE `ai_ai_tool_like` CHANGE `user_type` `user_type` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'user' COMMENT '用户类型：user用户，admin管理员;guest:访客';
ALTER TABLE `ai_ai_tool_like` CHANGE `user_id` `user_id` INT NULL DEFAULT '0' COMMENT '用户ID';
ALTER TABLE `ai_ai_tool_like` ADD `uuid` VARCHAR(50) NULL DEFAULT NULL COMMENT '访客uuid' AFTER `user_id`;

//AI评价
ALTER TABLE `ai_ai_tool_comment` CHANGE `user_type` `user_type` VARCHAR(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT 'user' COMMENT '用户类型：user用户，admin管理员，guest 访客';
ALTER TABLE `ai_ai_tool_comment` ADD `uuid` VARCHAR(50) NULL DEFAULT NULL COMMENT '访客uuid' AFTER `user_id`;
ALTER TABLE `ai_ai_tool_comment` CHANGE `user_id` `user_id` INT NULL DEFAULT '0' COMMENT '用户ID';