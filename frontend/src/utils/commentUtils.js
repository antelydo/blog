/**
 * 评论工具函数
 */

/**
 * 将扁平的评论列表转换为嵌套结构
 * @param {Array} comments 评论列表
 * @returns {Array} 嵌套结构的评论列表
 */
export function buildCommentTree(comments) {
  if (!comments || !comments.length) return [];

  // 创建评论映射，用于快速查找
  const commentMap = {};
  const rootComments = [];

  // 第一步：创建所有评论的映射
  comments.forEach(comment => {
    // 确保每个评论都有replies数组
    comment.replies = comment.replies || [];
    commentMap[comment.id] = comment;
  });

  // 第二步：构建评论树
  comments.forEach(comment => {
    // 如果有父评论，将此评论添加到父评论的replies中
    if (comment.parent_id && comment.parent_id !== 0 && commentMap[comment.parent_id]) {
      commentMap[comment.parent_id].replies.push(comment);
    } else {
      // 没有父评论或父评论不存在，作为根评论
      rootComments.push(comment);
    }
  });

  // 第三步：对每个评论的replies进行排序（可选）
  const sortReplies = (comments) => {
    comments.forEach(comment => {
      // 按时间升序排序回复
      comment.replies.sort((a, b) => new Date(a.create_time) - new Date(b.create_time));
      // 递归排序子回复
      if (comment.replies.length > 0) {
        sortReplies(comment.replies);
      }
    });
  };

  sortReplies(rootComments);

  return rootComments;
}

/**
 * 按抖音风格处理评论列表（所有评论在同一层级）
 * @param {Array} comments 评论列表
 * @returns {Array} 处理后的评论列表
 */
export function processDouyinStyleComments(comments) {
  if (!comments || !comments.length) return [];

  // 创建评论映射，用于快速查找
  const commentMap = {};
  const result = [];
  const flattenedComments = [];

  // 第一步：创建所有评论的映射
  comments.forEach(comment => {
    commentMap[comment.id] = comment;

    // 将嵌套的回复展平
    if (comment.replies && comment.replies.length > 0) {
      flattenNestedReplies(comment.replies, flattenedComments, commentMap);
    }
  });

  // 使用 Set 去除重复项
  const uniqueCommentIds = new Set();
  const allComments = [];

  // 先添加原始评论
  comments.forEach(comment => {
    if (!uniqueCommentIds.has(comment.id)) {
      uniqueCommentIds.add(comment.id);
      allComments.push(comment);
    }
  });

  // 再添加展平的回复，确保不重复
  flattenedComments.forEach(reply => {
    if (!uniqueCommentIds.has(reply.id)) {
      uniqueCommentIds.add(reply.id);
      allComments.push(reply);
    }
  });

  // 第二步：处理每个评论，添加回复信息
  allComments.forEach(comment => {
    // 深拷贝评论对象，避免修改原始数据
    const processedComment = { ...comment };

    // 如果是回复类型的评论，添加被回复人的信息
    if (processedComment.parent_id && processedComment.parent_id !== 0) {
      const parentComment = commentMap[processedComment.parent_id];
      if (parentComment) {
        // 添加被回复人的信息
        processedComment.reply_to_name = parentComment.name || parentComment.author || parentComment.username || parentComment.nickname;
        processedComment.reply_to_id = parentComment.id;
        processedComment.is_reply = true;

        // 设置回复层级
        processedComment.reply_level = parentComment.reply_level ? parentComment.reply_level + 1 : 1;

        // 设置根评论 ID（用于分组显示）
        if (parentComment.root_comment_id) {
          processedComment.root_comment_id = parentComment.root_comment_id;
        } else if (!parentComment.is_reply) {
          processedComment.root_comment_id = parentComment.id;
        } else {
          processedComment.root_comment_id = parentComment.parent_id;
        }
      }
    } else {
      processedComment.is_reply = false;
      processedComment.reply_level = 0;
    }

    result.push(processedComment);
  });

  // 按抖音风格排序
  result.sort((a, b) => {
    // 先按是否为回复排序，非回复在前
    if (a.is_reply !== b.is_reply) {
      return a.is_reply ? 1 : -1;
    }

    // 如果都是回复，则按根评论 ID 分组
    if (a.is_reply && b.is_reply) {
      if (a.root_comment_id !== b.root_comment_id) {
        return a.root_comment_id - b.root_comment_id;
      }

      // 同一组内按时间升序排序（早的在前）
      return new Date(a.create_time) - new Date(b.create_time);
    }

    // 非回复的评论按时间降序排序（新的在前）
    return new Date(b.create_time) - new Date(a.create_time);
  });

  return result;
}

/**
 * 递归展平嵌套的回复
 * @param {Array} replies 回复数组
 * @param {Array} result 结果数组
 * @param {Object} commentMap 评论映射
 */
function flattenNestedReplies(replies, result, commentMap) {
  if (!replies || !replies.length) return;

  replies.forEach(reply => {
    // 将回复添加到结果数组
    result.push(reply);
    commentMap[reply.id] = reply;

    // 递归处理嵌套回复
    if (reply.replies && reply.replies.length > 0) {
      flattenNestedReplies(reply.replies, result, commentMap);
    }
  });
}

/**
 * 计算评论的总回复数（包括所有嵌套回复）
 * @param {Object} comment 评论对象
 * @returns {Number} 总回复数
 */
export function countTotalReplies(comment) {
  if (!comment.replies || comment.replies.length === 0) return 0;

  let count = comment.replies.length;

  // 递归计算子回复的数量
  comment.replies.forEach(reply => {
    count += countTotalReplies(reply);
  });

  return count;
}

/**
 * 查找评论树中的特定评论
 * @param {Array} comments 评论树
 * @param {Number|String} commentId 要查找的评论ID
 * @returns {Object|null} 找到的评论或null
 */
export function findComment(comments, commentId) {
  if (!comments || !comments.length) return null;

  for (const comment of comments) {
    if (comment.id === commentId) {
      return comment;
    }

    if (comment.replies && comment.replies.length > 0) {
      const found = findComment(comment.replies, commentId);
      if (found) return found;
    }
  }

  return null;
}

/**
 * 格式化评论时间
 * @param {String} dateString 日期字符串
 * @returns {String} 格式化后的时间
 */
export function formatCommentTime(dateString) {
  const date = new Date(dateString);
  const now = new Date();
  const diffMs = now - date;
  const diffSec = Math.floor(diffMs / 1000);
  const diffMin = Math.floor(diffSec / 60);
  const diffHour = Math.floor(diffMin / 60);
  const diffDay = Math.floor(diffHour / 24);

  if (diffSec < 60) {
    return '刚刚';
  } else if (diffMin < 60) {
    return `${diffMin}分钟前`;
  } else if (diffHour < 24) {
    return `${diffHour}小时前`;
  } else if (diffDay < 30) {
    return `${diffDay}天前`;
  } else {
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
  }
}
