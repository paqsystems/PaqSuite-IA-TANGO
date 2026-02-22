import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'com.paqsystems.paqsuite',
  appName: 'PAQSuite',
  webDir: 'dist',
  server: {
    url: process.env.CAPACITOR_DEV_SERVER || undefined,
    cleartext: true
  }
};

export default config;
