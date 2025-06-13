/**
 * 格式化评论时间
 * @param {string} time 时间字符串
 * @returns {string} 格式化后的时间
 */
export function formatCommentTime(time) {
  if (!time) return '';

  // 打印原始时间字符串，用于调试
  console.log('Original time string:', time, typeof time);

  // 处理不同类型的时间输入
  let timeValue = time;
  let date;

  // 如果是字符串类型
  if (typeof time === 'string') {
    // 如果是数字字符串，转换为数字
    if (!isNaN(Number(time))) {
      timeValue = Number(time);

      // 如果是秒级时间戳，转换为毫秒级
      if (timeValue < 10000000000) {
        timeValue *= 1000;
      }

      date = new Date(timeValue);
    } else if (time.includes('/')) {
      // 尝试 MM/DD/YYYY 格式
      const parts = time.split('/');
      date = new Date(parts[2], parts[0] - 1, parts[1]);
    } else {
      // 尝试 ISO 格式
      date = new Date(time);
    }
  } else if (typeof time === 'number') {
    // 如果是数字类型
    timeValue = time;

    // 如果是秒级时间戳，转换为毫秒级
    if (timeValue < 10000000000) {
      timeValue *= 1000;
    }

    date = new Date(timeValue);
  } else {
    // 其他情况，直接尝试创建 Date 对象
    date = new Date(time);
  }

  // 如果还是无效，返回原始字符串
  if (isNaN(date.getTime())) {
    console.error('Invalid date:', timeValue);
    return timeValue;
  }

  // 打印解析后的时间，用于调试
  console.log('Parsed date:', date.toString());

  const now = new Date();
  console.log('Current date:', now.toString());

  // 检查是否是未来日期
  if (date.getTime() > now.getTime()) {
    console.log('Future date detected:', date.toString());

    // 如果是未来日期，可能是时间戳格式错误
    // 尝试将秒级时间戳视为毫秒级时间戳
    const correctedDate = new Date(date.getTime() / 1000);

    // 如果修正后的日期仍然是未来日期，则返回“刚刚”
    if (correctedDate.getTime() > now.getTime()) {
      console.log('Still future date after correction, using "just now"');
      return '刚刚';
    }

    // 如果修正后的日期是合理的，使用修正后的日期
    console.log('Using corrected date:', correctedDate.toString());
    date = correctedDate;
  }

  // 计算时间差（毫秒）
  const diffMs = now.getTime() - date.getTime();
  console.log('Time difference (ms):', diffMs);

  // 计算各个时间单位的差值
  const diffSec = Math.floor(diffMs / 1000);
  const diffMin = Math.floor(diffSec / 60);
  const diffHour = Math.floor(diffMin / 60);
  const diffDay = Math.floor(diffHour / 24);
  const diffMonth = Math.floor(diffDay / 30);
  const diffYear = Math.floor(diffMonth / 12);

  // 打印时间差值，用于调试
  console.log(`Time diff: ${diffYear}年 ${diffMonth}月 ${diffDay}天 ${diffHour}小时 ${diffMin}分钟 ${diffSec}秒`);

  // 如果时间差值异常，打印更多信息
  if (diffHour > 48) {
    console.log('Abnormal time difference detected!');
    console.log('Date object:', date);
    console.log('Now object:', now);
    console.log('Date timestamp:', date.getTime());
    console.log('Now timestamp:', now.getTime());
    console.log('Date timezone offset:', date.getTimezoneOffset());
    console.log('Now timezone offset:', now.getTimezoneOffset());
  }

  // 处理时区问题
  // 获取当前时区偏移（分钟）
  const timezoneOffset = date.getTimezoneOffset();
  console.log('Timezone offset (minutes):', timezoneOffset);

  // 如果时间差异常大，可能是时区问题
  if (diffHour > 24 && Math.abs(timezoneOffset) > 0) {
    // 调整时间差，考虑时区偏移
    const adjustedDiffHour = diffHour - Math.abs(timezoneOffset) / 60;
    console.log('Adjusted diff hours:', adjustedDiffHour);

    // 如果调整后的时间差在合理范围内，使用调整后的时间
    if (adjustedDiffHour >= 0 && adjustedDiffHour < 24) {
      console.log('Using timezone adjusted time');
      return `${Math.floor(adjustedDiffHour)}小时前`;
    }
  }

  // 根据时间差返回不同的格式
  if (diffSec < 60) {
    return '刚刚';
  } else if (diffMin < 60) {
    return `${diffMin}分钟前`;
  } else if (diffHour < 24) {
    return `${diffHour}小时前`;
  } else if (diffDay < 30) {
    return `${diffDay}天前`;
  } else if (diffMonth < 12) {
    return `${diffMonth}个月前`;
  } else {
    return `${diffYear}年前`;
  }
}

/**
 * 格式化日期时间
 * @param {string} time 时间字符串
 * @returns {string} 格式化后的日期时间
 */
export function formatDateTime(time) {
  if (!time) return '';

  // 处理不同类型的时间输入
  let timeValue = time;
  let date;

  // 如果是字符串类型
  if (typeof time === 'string') {
    // 如果是数字字符串，转换为数字
    if (!isNaN(Number(time))) {
      timeValue = Number(time);

      // 如果是秒级时间戳，转换为毫秒级
      if (timeValue < 10000000000) {
        timeValue *= 1000;
      }

      date = new Date(timeValue);
    } else if (time.includes('/')) {
      // 尝试 MM/DD/YYYY 格式
      const parts = time.split('/');
      date = new Date(parts[2], parts[0] - 1, parts[1]);
    } else {
      // 尝试 ISO 格式
      date = new Date(time);
    }
  } else if (typeof time === 'number') {
    // 如果是数字类型
    timeValue = time;

    // 如果是秒级时间戳，转换为毫秒级
    if (timeValue < 10000000000) {
      timeValue *= 1000;
    }

    date = new Date(timeValue);
  } else {
    // 其他情况，直接尝试创建 Date 对象
    date = new Date(time);
  }

  // 检查是否是未来日期
  const now = new Date();
  if (date.getTime() > now.getTime()) {
    // 如果是未来日期，可能是时间戳格式错误
    // 尝试将秒级时间戳视为毫秒级时间戳
    const correctedDate = new Date(date.getTime() / 1000);

    // 如果修正后的日期是合理的，使用修正后的日期
    if (correctedDate.getTime() <= now.getTime()) {
      date = correctedDate;
    }
  }

  // 如果还是无效，返回原始字符串
  if (isNaN(date.getTime())) {
    console.error('Invalid date:', timeValue);
    return timeValue;
  }

  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');

  return `${year}-${month}-${day} ${hours}:${minutes}`;
}
