const fs = require('fs');
const path = require('path');
const glob = require('glob');

const baseDir = path.join(__dirname);

// 定义需要处理的文件类型
const patterns = ['**/*.js', '**/*.vue', '**/*.ts'];

// 用于过滤掉包含 console.log 的行
function removeConsoleLogs(content) {
  // 如果一行中包含 console.log 则移除整个行
  return content.split('\n').filter(line => !line.includes('console.log')).join('\n');
}

patterns.forEach(pattern => {
  // 使用 glob.sync 获取匹配的文件列表
  const files = glob.sync(pattern, { cwd: baseDir, ignore: 'node_modules/**' });
  files.forEach(file => {
    const fullPath = path.join(baseDir, file);
    try {
      const data = fs.readFileSync(fullPath, 'utf8');
      const newData = removeConsoleLogs(data);
      if (newData !== data) {
        fs.writeFileSync(fullPath, newData, 'utf8');
        console.log(`Removed console.log from ${fullPath}`);
      }
    } catch (err) {
      console.error(`Error processing ${fullPath}:`, err);
    }
  });
}); 