<?php
// 需要修复的文件列表
$files = [
    './app/api/controller/Statistics.php' => './app/api/controller/statistics.php',
    './app/Cat.php' => './app/api/controller/Cat.php',
];

foreach ($files as $oldPath => $newPath) {
    if (file_exists($oldPath)) {
        // 确保目标目录存在
        $dir = dirname($newPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        // 读取文件内容
        $content = file_get_contents($oldPath);
        
        // 如果是 Cat.php，需要修改命名空间
        if (basename($oldPath) === 'Cat.php') {
            $content = str_replace('namespace app;', 'namespace app\\api\\controller;', $content);
        }
        
        // 写入新文件
        if (file_put_contents($newPath, $content)) {
            echo "已将 {$oldPath} 的内容复制到 {$newPath}\n";
            
            // 删除旧文件
            unlink($oldPath);
            echo "已删除 {$oldPath}\n";
        } else {
            echo "无法写入 {$newPath}\n";
        }
    } else {
        echo "{$oldPath} 不存在\n";
    }
}

echo "剩余 PSR-4 文件修复完成\n";
