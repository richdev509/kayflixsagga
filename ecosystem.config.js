module.exports = {
  apps: [
    {
      name: 'laravel-scheduler',
      script: 'php',
      args: 'artisan schedule:work',
      cwd: 'C:\\laravelProject\\ProjetStream\\stream\\BackendLaravel',
      instances: 1,
      autorestart: true,
      watch: false,
      max_memory_restart: '500M',
      error_file: './storage/logs/pm2-error.log',
      out_file: './storage/logs/pm2-out.log',
      log_file: './storage/logs/pm2-combined.log',
      time: true
    }
  ]
};
