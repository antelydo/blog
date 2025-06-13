export default {
  ipLocation: {
    // Page title and description
    title: 'IP Geolocation',
    description: 'Query geographic location information of IP addresses',
    
    // Query form
    form: {
      ipAddress: 'IP Address',
      placeholder: 'Enter IP address, leave empty to query current IP',
      query: 'Query',
      batchQuery: 'Batch Query'
    },
    
    // Result display
    result: {
      title: 'IP Geolocation Information',
      loading: 'Querying IP geolocation...',
      error: 'Query failed',
      emptyDescription: 'Please enter an IP address to query',
      ipAddress: 'IP Address',
      country: 'Country',
      countryCode: 'Country Code',
      region: 'Region',
      regionCode: 'Region Code',
      city: 'City',
      zipCode: 'ZIP Code',
      latitude: 'Latitude',
      longitude: 'Longitude',
      timezone: 'Timezone',
      isp: 'ISP',
      organization: 'Organization',
      as: 'AS',
      map: 'Map Location',
      unknown: 'Unknown'
    },
    
    // Batch query
    batch: {
      title: 'Batch IP Geolocation Query',
      description: 'IP Address List (one per line)',
      placeholder: 'Please enter IP addresses, one per line',
      maxLimit: 'You can query up to 20 IP addresses at once',
      results: 'Query Results',
      success: 'Successfully queried geolocation for {count} IP addresses',
      columns: {
        ipAddress: 'IP Address',
        status: 'Query Status',
        location: 'Location',
        isp: 'ISP'
      },
      statusSuccess: 'Success',
      statusFailed: 'Failed'
    },
    
    // Message prompts
    messages: {
      querySuccess: 'Query successful',
      queryFailed: 'Query failed',
      invalidIp: 'Invalid IP address',
      emptyIpList: 'Please enter at least one IP address',
      tooManyIps: 'You can query up to 20 IP addresses at once',
      networkError: 'Network error, please try again later'
    }
  }
}
