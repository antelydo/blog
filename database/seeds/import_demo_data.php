<?php
// 数据库配置
$config = [
    'host'     => 'localhost', // 数据库服务器
    'username' => 'root',      // 数据库用户名
    'password' => '',          // 数据库密码
    'database' => 'aichatui',          // 数据库名
    'charset'  => 'utf8mb4',   // 字符集
];

// 请在执行前修改上面的数据库配置

// 连接数据库
try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "数据库连接成功！\n";
} catch (PDOException $e) {
    die("数据库连接失败: " . $e->getMessage() . "\n");
}

// 获取SQL文件路径
$mainSqlFile = __DIR__ . '/ai_tool_demo_data.sql';
$sqlFiles = [
    __DIR__ . '/ai_tool_category_data.sql',
    __DIR__ . '/ai_tool_tag_data.sql',
    __DIR__ . '/ai_tool_data.sql',
    __DIR__ . '/ai_tool_tag_relation_data.sql',
    __DIR__ . '/ai_tool_comment_data.sql',
    __DIR__ . '/ai_tool_favorite_data.sql',
    __DIR__ . '/ai_tool_like_data.sql',
    __DIR__ . '/ai_tool_visit_log_data.sql',
];

// 检查文件是否存在
foreach ($sqlFiles as $file) {
    if (!file_exists($file)) {
        die("SQL文件不存在: {$file}\n");
    }
}

// 清空现有数据
try {
    echo "\n正在清空现有数据...\n";
    $tables = [
        'ai_ai_tool_visit_log',
        'ai_ai_tool_like',
        'ai_ai_tool_favorite',
        'ai_ai_tool_comment',
        'ai_ai_tool_tag_relation',
        'ai_ai_tool_tag',
        'ai_ai_tool',
        'ai_ai_tool_category'
    ];

    foreach ($tables as $table) {
        echo "\n清空表: {$table}";
        $pdo->exec("TRUNCATE TABLE `{$table}`");
    }

    echo "\n所有表清空完成!\n";
} catch (PDOException $e) {
    die("\n清空表失败: " . $e->getMessage() . "\n");
}

// 导入数据
try {
    // 开始事务
    $pdo->beginTransaction();

    // 导入各个SQL文件
    foreach ($sqlFiles as $file) {
        echo "正在导入: " . basename($file) . "\n";
        $sql = file_get_contents($file);

        // 分割SQL语句
        $statements = splitSqlStatements($sql);

        // 执行SQL语句
        foreach ($statements as $statement) {
            if (!empty(trim($statement))) {
                $pdo->exec($statement);
            }
        }
    }

    // 执行更新语句
    echo "正在更新统计数据...\n";

    // 更新标签使用次数
    $pdo->exec("UPDATE `ai_ai_tool_tag` t SET t.count = (SELECT COUNT(*) FROM `ai_ai_tool_tag_relation` r WHERE r.tag_id = t.id)");

    // 更新工具评分和评论数
    $pdo->exec("UPDATE `ai_ai_tool` t SET
        t.comments = (SELECT COUNT(*) FROM `ai_ai_tool_comment` c WHERE c.tool_id = t.id AND c.parent_id = 0),
        t.rating_count = (SELECT COUNT(*) FROM `ai_ai_tool_comment` c WHERE c.tool_id = t.id AND c.parent_id = 0 AND c.rating > 0)");

    // 更新工具收藏数和点赞数
    $pdo->exec("UPDATE `ai_ai_tool` t SET
        t.favorites = (SELECT COUNT(*) FROM `ai_ai_tool_favorite` f WHERE f.tool_id = t.id),
        t.likes = (SELECT COUNT(*) FROM `ai_ai_tool_like` l WHERE l.tool_id = t.id)");

    // 更新工具浏览量
    $pdo->exec("UPDATE `ai_ai_tool` t SET
        t.views = (SELECT COUNT(*) FROM `ai_ai_tool_visit_log` v WHERE v.tool_id = t.id)");

    // 提交事务
    $pdo->commit();

    echo "AI工具模拟数据导入完成！\n";
} catch (PDOException $e) {
    // 回滚事务
    $pdo->rollBack();
    die("导入失败: " . $e->getMessage() . "\n");
}

/**
 * 分割SQL语句
 *
 * @param string $sql
 * @return array
 */
function splitSqlStatements($sql) {
    // 移除注释
    $sql = preg_replace(['/#.*/', '/-- .*/', '/\/\*.*?\*\//s'], '', $sql);

    // 分割为单独的语句
    $statements = [];
    $currentStatement = '';

    foreach (explode("\n", $sql) as $line) {
        $line = trim($line);
        if (empty($line)) continue;

        $currentStatement .= ' ' . $line;

        if (substr($line, -1) === ';') {
            $statements[] = trim($currentStatement);
            $currentStatement = '';
        }
    }

    // 添加最后一个语句（如果没有分号结尾）
    if (!empty(trim($currentStatement))) {
        $statements[] = trim($currentStatement);
    }

    return array_filter($statements);
}
