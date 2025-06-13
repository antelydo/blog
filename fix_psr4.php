<?php
// 需要修复的文件列表
$files = [
    './app/api/controller/search.php' => './app/api/controller/Search.php',
    './app/api/controller/adminLink.php' => './app/api/controller/AdminLink.php',
    './app/api/controller/adminContact.php' => './app/api/controller/AdminContact.php',
    './app/cat.php' => './app/Cat.php',
];

foreach ($files as $oldPath => $newPath) {
    if (file_exists($oldPath)) {
        // 确保目标目录存在
        $dir = dirname($newPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        // 重命名文件
        if (rename($oldPath, $newPath)) {
            echo "已将 {$oldPath} 重命名为 {$newPath}\n";
        } else {
            echo "无法重命名 {$oldPath}\n";
        }
    } else {
        echo "{$oldPath} 不存在\n";
    }
}

echo "PSR-4 文件名修复完成\n";
