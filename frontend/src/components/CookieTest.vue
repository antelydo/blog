<template>
  <div class="cookie-test">
    <h2>Cookie 测试</h2>
    <div class="actions">
      <el-button type="primary" @click="setCookie">设置 Cookie</el-button>
      <el-button type="success" @click="getCookie">获取 Cookie</el-button>
      <el-button type="warning" @click="clearCookie">清除 Cookie</el-button>
    </div>
    <div class="result" v-if="result">
      <h3>结果:</h3>
      <pre>{{ result }}</pre>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'CookieTest',
  data() {
    return {
      result: null
    };
  },
  methods: {
    async setCookie() {
      try {
        this.result = '正在设置 Cookie...';
        
        // 创建一个带有 withCredentials 的 axios 实例
        const instance = axios.create({
          baseURL: '',
          withCredentials: true
        });
        
        // 发送请求到测试端点
        const response = await instance.get('/api/test/set-cookie', {
          withCredentials: true
        });
        
        this.result = `设置 Cookie 成功:\n${JSON.stringify(response.data, null, 2)}`;
      } catch (error) {
        this.result = `设置 Cookie 失败:\n${error.message}`;
        console.error('设置 Cookie 错误:', error);
      }
    },
    
    async getCookie() {
      try {
        this.result = '正在获取 Cookie...';
        
        // 创建一个带有 withCredentials 的 axios 实例
        const instance = axios.create({
          baseURL: '',
          withCredentials: true
        });
        
        // 发送请求到测试端点
        const response = await instance.get('/api/test/get-cookie', {
          withCredentials: true
        });
        
        this.result = `获取 Cookie 成功:\n${JSON.stringify(response.data, null, 2)}`;
      } catch (error) {
        this.result = `获取 Cookie 失败:\n${error.message}`;
        console.error('获取 Cookie 错误:', error);
      }
    },
    
    async clearCookie() {
      try {
        this.result = '正在清除 Cookie...';
        
        // 创建一个带有 withCredentials 的 axios 实例
        const instance = axios.create({
          baseURL: '',
          withCredentials: true
        });
        
        // 发送请求到测试端点
        const response = await instance.get('/api/test/clear-cookie', {
          withCredentials: true
        });
        
        this.result = `清除 Cookie 成功:\n${JSON.stringify(response.data, null, 2)}`;
      } catch (error) {
        this.result = `清除 Cookie 失败:\n${error.message}`;
        console.error('清除 Cookie 错误:', error);
      }
    }
  }
};
</script>

<style scoped>
.cookie-test {
  padding: 20px;
  border: 1px solid #eee;
  border-radius: 5px;
  margin: 20px 0;
}

.actions {
  margin: 20px 0;
  display: flex;
  gap: 10px;
}

.result {
  margin-top: 20px;
  padding: 15px;
  background-color: #f5f7fa;
  border-radius: 4px;
}

pre {
  white-space: pre-wrap;
  word-break: break-all;
}
</style>
