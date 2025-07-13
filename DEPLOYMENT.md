# Panduan Deployment - Sistem Pembayaran Sekolah

## Deployment ke Localhost (Development)

### Prerequisites:
- XAMPP, WAMP, atau LAMP stack
- PHP 7.4+
- MySQL 8.0+

### Langkah-langkah:

1. **Install Web Server Stack**
   - Download dan install XAMPP: https://www.apachefriends.org/
   - Start Apache dan MySQL services

2. **Copy Project Files**
   ```bash
   # Copy folder proyek ke htdocs
   cp -r e:\Web\AHHHHHH_baru C:\xampp\htdocs\school-payment
   ```

3. **Setup Database**
   - Buka phpMyAdmin: http://localhost/phpmyadmin
   - Buat database baru: `school_payment`
   - Import file: `database/school_payment.sql`

4. **Konfigurasi Database**
   ```php
   // Edit config/database.php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'school_payment');
   define('DB_USER', 'root');
   define('DB_PASS', ''); // Kosong untuk XAMPP default
   ```

5. **Test Installation**
   - Akses: http://localhost/school-payment
   - Login admin: username=`admin`, password=`admin123`

## Deployment ke Hosting Shared

### Prerequisites:
- Shared hosting dengan PHP 7.4+ dan MySQL
- cPanel atau control panel hosting
- FTP/SFTP access

### Langkah-langkah:

1. **Upload Files**
   ```bash
   # Via FTP client (FileZilla, WinSCP)
   # Upload semua file ke public_html/
   ```

2. **Create Database**
   - Masuk ke cPanel
   - Buka MySQL Databases
   - Buat database baru dan user
   - Import database/school_payment.sql

3. **Update Configuration**
   ```php
   // config/database.php
   define('DB_HOST', 'localhost'); // atau host yang diberikan hosting
   define('DB_NAME', 'username_school_payment');
   define('DB_USER', 'username_dbuser');
   define('DB_PASS', 'your_db_password');
   ```

4. **Set File Permissions**
   ```bash
   chmod 755 uploads/
   chmod 644 .htaccess
   ```

## Deployment ke VPS/Cloud Server

### Prerequisites:
- Ubuntu 20.04+ atau CentOS 8+
- Root atau sudo access
- Domain name (optional)

### Install LAMP Stack:

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Apache
sudo apt install apache2 -y
sudo systemctl enable apache2

# Install PHP 7.4
sudo apt install php7.4 php7.4-mysql php7.4-gd php7.4-curl php7.4-zip php7.4-xml -y

# Install MySQL
sudo apt install mysql-server -y
sudo mysql_secure_installation

# Enable mod_rewrite
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Deploy Application:

1. **Clone/Upload Project**
   ```bash
   cd /var/www/html
   sudo git clone [repository-url] school-payment
   sudo chown -R www-data:www-data school-payment/
   sudo chmod -R 755 school-payment/
   sudo chmod -R 777 school-payment/uploads/
   ```

2. **Setup Database**
   ```bash
   sudo mysql -u root -p
   CREATE DATABASE school_payment;
   CREATE USER 'school_user'@'localhost' IDENTIFIED BY 'strong_password';
   GRANT ALL PRIVILEGES ON school_payment.* TO 'school_user'@'localhost';
   FLUSH PRIVILEGES;
   EXIT;
   
   # Import database
   mysql -u school_user -p school_payment < /var/www/html/school-payment/database/school_payment.sql
   ```

3. **Configure Apache Virtual Host**
   ```bash
   sudo nano /etc/apache2/sites-available/school-payment.conf
   ```
   
   ```apache
   <VirtualHost *:80>
       ServerName your-domain.com
       DocumentRoot /var/www/html/school-payment
       
       <Directory /var/www/html/school-payment>
           AllowOverride All
           Require all granted
       </Directory>
       
       ErrorLog ${APACHE_LOG_DIR}/school-payment-error.log
       CustomLog ${APACHE_LOG_DIR}/school-payment-access.log combined
   </VirtualHost>
   ```
   
   ```bash
   sudo a2ensite school-payment.conf
   sudo systemctl reload apache2
   ```

4. **SSL Certificate (Let's Encrypt)**
   ```bash
   sudo apt install certbot python3-certbot-apache -y
   sudo certbot --apache -d your-domain.com
   ```

## Deployment ke Docker

### Dockerfile:

```dockerfile
FROM php:7.4-apache

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Enable Apache rewrite module
RUN a2enmod rewrite

# Copy application
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html/
RUN chmod -R 755 /var/www/html/
RUN chmod -R 777 /var/www/html/uploads/

EXPOSE 80
```

### docker-compose.yml:

```yaml
version: '3.8'
services:
  web:
    build: .
    ports:
      - "8000:80"
    volumes:
      - ./uploads:/var/www/html/uploads
    depends_on:
      - db
    environment:
      - DB_HOST=db
      - DB_NAME=school_payment
      - DB_USER=root
      - DB_PASS=rootpassword

  db:
    image: mysql:8.0
    restart: always
    environment:
      MYSQL_ROOT_PASSWORD: rootpassword
      MYSQL_DATABASE: school_payment
    volumes:
      - ./database/school_payment.sql:/docker-entrypoint-initdb.d/init.sql
      - mysql_data:/var/lib/mysql

volumes:
  mysql_data:
```

### Deploy:
```bash
docker-compose up -d
```

## Production Security Checklist

### Server Security:
- [ ] Update sistem operasi
- [ ] Configure firewall (UFW/iptables)
- [ ] Disable root SSH login
- [ ] Setup fail2ban
- [ ] Regular security updates

### Application Security:
- [ ] Change default admin password
- [ ] Enable HTTPS/SSL
- [ ] Configure secure headers
- [ ] Disable PHP error display
- [ ] Implement rate limiting
- [ ] Regular backup database

### File Security:
- [ ] Correct file permissions
- [ ] Secure upload directory
- [ ] Disable directory listing
- [ ] Remove development files

### Database Security:
- [ ] Strong database passwords
- [ ] Remove test accounts
- [ ] Backup database regularly
- [ ] Monitor database access

## Monitoring dan Maintenance

### Log Files:
```bash
# Apache logs
tail -f /var/log/apache2/error.log
tail -f /var/log/apache2/access.log

# PHP logs
tail -f /var/log/php7.4-fpm.log

# MySQL logs
tail -f /var/log/mysql/error.log
```

### Performance Monitoring:
- Setup monitoring tools (Nagios, Zabbix)
- Monitor disk space
- Monitor database performance
- Track application response times

### Backup Strategy:
```bash
#!/bin/bash
# Backup script example
DATE=$(date +"%Y%m%d_%H%M%S")
BACKUP_DIR="/backup/school-payment"

# Database backup
mysqldump -u school_user -p school_payment > $BACKUP_DIR/db_$DATE.sql

# Files backup
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/html/school-payment/uploads/

# Cleanup old backups (keep 30 days)
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete
```

## Troubleshooting Production Issues

### Common Issues:

1. **500 Internal Server Error**
   - Check Apache error logs
   - Verify file permissions
   - Check .htaccess syntax

2. **Database Connection Failed**
   - Verify database credentials
   - Check MySQL service status
   - Test database connectivity

3. **File Upload Issues**
   - Check upload directory permissions
   - Verify PHP upload settings
   - Check disk space

4. **Performance Issues**
   - Enable PHP OPcache
   - Optimize database queries
   - Check server resources

### Debug Commands:
```bash
# Check Apache status
sudo systemctl status apache2

# Check MySQL status
sudo systemctl status mysql

# Check PHP version
php -v

# Check PHP modules
php -m

# Test database connection
mysql -u school_user -p -e "SELECT 1"
```

## Update Procedures

### Application Updates:
1. Backup current version
2. Download new version
3. Compare configuration files
4. Update database schema if needed
5. Test in staging environment
6. Deploy to production

### Security Updates:
1. Monitor security advisories
2. Test updates in staging
3. Schedule maintenance window
4. Apply updates
5. Verify functionality

---

**Important**: Selalu test deployment di environment development/staging sebelum deploy ke production!
