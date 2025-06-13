export default {
  ipLocation: {
    // 页面标题和描述
    title: 'IP地理位置查询',
    description: '查询IP地址的地理位置信息',
    
    // 查询表单
    form: {
      ipAddress: 'IP地址',
      placeholder: '输入IP地址，留空则查询当前IP',
      query: '查询',
      batchQuery: '批量查询'
    },
    
    // 结果显示
    result: {
      title: 'IP地理位置信息',
      loading: '正在查询IP地理位置...',
      error: '查询失败',
      emptyDescription: '请输入IP地址进行查询',
      ipAddress: 'IP地址',
      country: '国家/地区',
      countryCode: '国家代码',
      region: '省/州',
      regionCode: '省/州代码',
      city: '城市',
      zipCode: '邮政编码',
      latitude: '纬度',
      longitude: '经度',
      timezone: '时区',
      isp: 'ISP',
      organization: '组织',
      as: 'AS',
      map: '地图位置',
      unknown: '未知'
    },
    
    // 批量查询
    batch: {
      title: '批量查询IP地理位置',
      description: 'IP地址列表（每行一个IP）',
      placeholder: '请输入IP地址，每行一个',
      maxLimit: '一次最多查询20个IP地址',
      results: '查询结果',
      success: '成功查询 {count} 个IP地址的地理位置',
      columns: {
        ipAddress: 'IP地址',
        status: '查询状态',
        location: '地理位置',
        isp: 'ISP'
      },
      statusSuccess: '成功',
      statusFailed: '失败'
    },
    
    // 消息提示
    messages: {
      querySuccess: '查询成功',
      queryFailed: '查询失败',
      invalidIp: '无效的IP地址',
      emptyIpList: '请输入至少一个IP地址',
      tooManyIps: '一次最多查询20个IP地址',
      networkError: '网络错误，请稍后再试'
    }
  }
}
