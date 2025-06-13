/**
 * i18n结构修复工具
 * 用于修复翻译文件中的嵌套结构问题
 */

const fs = require('fs');
const path = require('path');

// 配置
const config = {
  // 语言包目录
  localesDir: path.resolve(__dirname, '../frontend/src/locales'),
  // 需要处理的语言
  languages: ['zh', 'en'],
  // 需要处理的文件
  files: ['common.js']
};

/**
 * 修复翻译文件结构
 */
function fixI18nStructure() {
  console.log('开始修复翻译文件结构...');
  
  for (const language of config.languages) {
    for (const file of config.files) {
      const filePath = path.join(config.localesDir, language, file);
      
      if (!fs.existsSync(filePath)) {
        console.error(`文件不存在: ${filePath}`);
        continue;
      }
      
      console.log(`处理文件: ${filePath}`);
      
      // 读取文件内容
      const content = fs.readFileSync(filePath, 'utf8');
      
      // 提取 export default {...} 中的内容
      const match = content.match(/export\s+default\s+({[\s\S]*})/);
      if (!match || !match[1]) {
        console.error(`无法解析文件: ${filePath}`);
        continue;
      }
      
      try {
        // 将内容转换为对象
        const obj = new Function(`return ${match[1]}`)();
        
        // 检查是否有嵌套的common对象
        if (obj.common && typeof obj.common === 'object') {
          console.log(`发现嵌套的common对象，正在修复...`);
          
          // 将嵌套的common对象中的内容移动到顶层
          for (const key in obj.common) {
            if (obj[key] && typeof obj[key] === 'object') {
              console.log(`警告: 顶层已存在 ${key} 键，将合并内容`);
              // 合并对象
              obj[key] = { ...obj[key], ...obj.common[key] };
            } else {
              obj[key] = obj.common[key];
            }
          }
          
          // 删除嵌套的common对象
          delete obj.common;
          
          // 将修改后的对象写回文件
          const newContent = `export default ${JSON.stringify(obj, null, 2)}`;
          fs.writeFileSync(filePath, newContent, 'utf8');
          
          console.log(`已修复文件: ${filePath}`);
        } else {
          console.log(`文件结构正常，无需修复: ${filePath}`);
        }
      } catch (error) {
        console.error(`处理文件时出错: ${filePath}`, error);
      }
    }
  }
  
  console.log('翻译文件结构修复完成');
}

// 执行修复
fixI18nStructure();
