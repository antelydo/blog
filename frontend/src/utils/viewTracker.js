/**
 * 浏览时长跟踪服务
 * 用于记录用户在网站和文章页面的浏览时长
 */
import api from '@/api';
import { apiUrls } from '@/api';

class ViewTracker {
  constructor() {
    this.startTime = null;
    this.pageType = null;
    this.pageId = null;
    this.isTracking = false;
    this.heartbeatInterval = null;
    this.lastHeartbeat = null;
    this.heartbeatDelay = 30000; // 30秒发送一次心跳
    this.visibilityHandler = this.handleVisibilityChange.bind(this);
    this.beforeUnloadHandler = this.handleBeforeUnload.bind(this);

    // 尝试发送之前失败的浏览时长数据
    this.trySendFailedDurations();
  }

  /**
   * 开始跟踪页面浏览时长
   * @param {string} pageType 页面类型 ('site', 'post', 'category', 'tag', etc.)
   * @param {number|null} pageId 页面ID，如文章ID
   */
  start(pageType, pageId = null) {
    if (this.isTracking) {
      this.stop(); // 如果已经在跟踪，先停止之前的跟踪
    }

    this.startTime = Date.now();
    this.pageType = pageType;
    this.pageId = pageId;
    this.isTracking = true;
    this.lastHeartbeat = this.startTime;

    // 添加页面可见性变化事件监听
    document.addEventListener('visibilitychange', this.visibilityHandler);

    // 添加页面卸载事件监听
    window.addEventListener('beforeunload', this.beforeUnloadHandler);

    // 启动心跳检测，定期发送浏览时长更新
    this.startHeartbeat();

    console.log(`开始跟踪 ${pageType} 页面浏览时长`, pageId ? `ID: ${pageId}` : '');
  }

  /**
   * 停止跟踪并发送最终的浏览时长数据
   */
  stop() {
    if (!this.isTracking) return;

    // 计算浏览时长（秒）
    const duration = this.calculateDuration();

    // 发送最终的浏览时长数据
    this.sendViewDuration(duration);

    // 清理事件监听和心跳检测
    this.cleanup();

    console.log(`停止跟踪 ${this.pageType} 页面浏览时长，总时长: ${duration}秒`);
  }

  /**
   * 启动心跳检测，定期发送浏览时长更新
   */
  startHeartbeat() {
    this.heartbeatInterval = setInterval(() => {
      // 只有在页面可见时才发送心跳
      if (document.visibilityState === 'visible') {
        const duration = this.calculateDuration();
        this.sendViewDuration(duration, true);
        this.lastHeartbeat = Date.now();
      }
    }, this.heartbeatDelay);
  }

  /**
   * 处理页面可见性变化
   */
  handleVisibilityChange() {
    if (document.visibilityState === 'hidden') {
      // 页面隐藏时，发送当前的浏览时长
      const duration = this.calculateDuration();
      this.sendViewDuration(duration, true);
    } else if (document.visibilityState === 'visible') {
      // 页面重新可见时，更新最后心跳时间
      this.lastHeartbeat = Date.now();
    }
  }

  /**
   * 处理页面卸载事件
   */
  handleBeforeUnload() {
    // 页面卸载前发送最终的浏览时长
    const duration = this.calculateDuration();
    this.sendViewDuration(duration);

    // 注意：在beforeunload事件中，异步请求可能不会完成
    // 使用同步请求或信标API可能更可靠，但这里使用简单方法
  }

  /**
   * 计算当前的浏览时长（秒）
   * @returns {number} 浏览时长（秒）
   */
  calculateDuration() {
    if (!this.startTime) return 0;
    const now = Date.now();
    return Math.floor((now - this.startTime) / 1000);
  }

  /**
   * 发送浏览时长数据到服务器
   * @param {number} duration 浏览时长（秒）
   * @param {boolean} isHeartbeat 是否是心跳更新
   * @param {number} retryCount 重试次数
   */
  sendViewDuration(duration, isHeartbeat = false, retryCount = 0) {
    if (duration <= 0) return;

    // 最大重试次数
    const MAX_RETRIES = 3;
    // 重试间隔（毫秒）
    const RETRY_DELAY = 2000;

    // 准备请求数据
    let endpoint, data;

    if (this.pageType === 'post' && this.pageId) {
      // 文章页面浏览时长
      endpoint = apiUrls.BLOG_API.UPDATE_VIEW_DURATION;
      data = {
        post_id: this.pageId,
        duration: duration,
        is_heartbeat: isHeartbeat
      };
    } else {
      // 网站整体浏览时长
      endpoint = apiUrls.BLOG_API.UPDATE_SITE_DURATION;
      data = {
        page_type: this.pageType,
        page_id: this.pageId,
        duration: duration,
        is_heartbeat: isHeartbeat
      };
    }

    // 发送请求
    api.post(endpoint, data)
      .then(response => {
        if (response && response.code === 200) {
          // 请求成功
          if (!isHeartbeat) {
            console.log(`浏览时长数据发送成功: ${this.pageType} ${this.pageId || ''}, 时长: ${duration}秒`);
          }
        } else {
          // 请求失败但服务器有响应
          console.warn(`浏览时长数据发送失败: ${response ? response.msg : '未知错误'}`);
          this.retryIfNeeded(duration, isHeartbeat, retryCount, MAX_RETRIES, RETRY_DELAY);
        }
      })
      .catch(error => {
        // 请求异常
        console.error(`发送${this.pageType}浏览时长失败:`, error);
        this.retryIfNeeded(duration, isHeartbeat, retryCount, MAX_RETRIES, RETRY_DELAY);
      });
  }

  /**
   * 如果需要，重试发送浏览时长数据
   * @param {number} duration 浏览时长
   * @param {boolean} isHeartbeat 是否是心跳更新
   * @param {number} retryCount 当前重试次数
   * @param {number} maxRetries 最大重试次数
   * @param {number} delay 重试间隔（毫秒）
   */
  retryIfNeeded(duration, isHeartbeat, retryCount, maxRetries, delay) {
    if (retryCount < maxRetries) {
      console.log(`将在 ${delay/1000} 秒后重试发送浏览时长数据 (重试 ${retryCount + 1}/${maxRetries})`);
      setTimeout(() => {
        this.sendViewDuration(duration, isHeartbeat, retryCount + 1);
      }, delay);
    } else if (!isHeartbeat) {
      // 如果不是心跳更新，则在本地存储未发送成功的数据
      this.storeFailedDuration(duration);
    }
  }

  /**
   * 存储未发送成功的浏览时长数据
   * @param {number} duration 浏览时长
   */
  storeFailedDuration(duration) {
    try {
      // 从本地存储中获取已存储的数据
      const storedData = localStorage.getItem('failed_view_durations');
      let failedDurations = storedData ? JSON.parse(storedData) : [];

      // 添加新的数据
      failedDurations.push({
        pageType: this.pageType,
        pageId: this.pageId,
        duration: duration,
        timestamp: Date.now()
      });

      // 限制存储数量，防止过多
      if (failedDurations.length > 50) {
        failedDurations = failedDurations.slice(-50);
      }

      // 保存到本地存储
      localStorage.setItem('failed_view_durations', JSON.stringify(failedDurations));
      console.log('浏览时长数据已存储到本地，将在下次访问时尝试重新发送');
    } catch (e) {
      console.error('存储浏览时长数据到本地失败:', e);
    }
  }

  /**
   * 清理事件监听和定时器
   */
  cleanup() {
    document.removeEventListener('visibilitychange', this.visibilityHandler);
    window.removeEventListener('beforeunload', this.beforeUnloadHandler);

    if (this.heartbeatInterval) {
      clearInterval(this.heartbeatInterval);
      this.heartbeatInterval = null;
    }

    this.isTracking = false;
    this.startTime = null;
    this.pageType = null;
    this.pageId = null;
  }

  /**
   * 尝试发送之前失败的浏览时长数据
   */
  trySendFailedDurations() {
    try {
      // 从本地存储中获取失败的数据
      const storedData = localStorage.getItem('failed_view_durations');
      if (!storedData) return;

      const failedDurations = JSON.parse(storedData);
      if (!failedDurations || !failedDurations.length) return;

      console.log(`尝试发送 ${failedDurations.length} 条之前失败的浏览时长数据`);

      // 创建一个新的数组来存储仍然失败的数据
      const stillFailedDurations = [];

      // 尝试发送每条数据
      const sendPromises = failedDurations.map(item => {
        // 检查数据是否过期（超过7天的数据不再发送）
        const isExpired = Date.now() - item.timestamp > 7 * 24 * 60 * 60 * 1000;
        if (isExpired) return Promise.resolve();

        // 准备请求数据
        let endpoint, data;

        if (item.pageType === 'post' && item.pageId) {
          endpoint = apiUrls.BLOG_API.UPDATE_VIEW_DURATION;
          data = {
            post_id: item.pageId,
            duration: item.duration,
            is_heartbeat: false
          };
        } else {
          endpoint = apiUrls.BLOG_API.UPDATE_SITE_DURATION;
          data = {
            page_type: item.pageType,
            page_id: item.pageId,
            duration: item.duration,
            is_heartbeat: false
          };
        }

        // 发送请求
        return api.post(endpoint, data)
          .then(response => {
            if (!response || response.code !== 200) {
              // 如果请求失败，将数据添加到仍然失败的数组中
              stillFailedDurations.push(item);
            }
          })
          .catch(() => {
            // 如果请求异常，将数据添加到仍然失败的数组中
            stillFailedDurations.push(item);
          });
      });

      // 等待所有请求完成
      Promise.all(sendPromises).then(() => {
        // 如果还有失败的数据，将其保存回本地存储
        if (stillFailedDurations.length > 0) {
          localStorage.setItem('failed_view_durations', JSON.stringify(stillFailedDurations));
          console.log(`仍有 ${stillFailedDurations.length} 条浏览时长数据发送失败，已重新存储`);
        } else {
          // 如果所有数据都发送成功，清除本地存储
          localStorage.removeItem('failed_view_durations');
          console.log('所有之前失败的浏览时长数据已成功发送');
        }
      });
    } catch (e) {
      console.error('尝试发送之前失败的浏览时长数据时出错:', e);
    }
  }
}

// 创建单例实例
const viewTracker = new ViewTracker();

export default viewTracker;
