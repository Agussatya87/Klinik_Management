# Klinik Management System

A comprehensive web-based clinic management system built with **PHP**, **Bootstrap 5**, and **MySQL**. This system provides a complete solution for managing patient data, medical procedures, doctors, rooms, and diagnoses in a clinical environment.

## 🏥 Features

### Core Modules
- **Dashboard Overview** - Real-time statistics and quick access to all modules
- **Patient Management** - Complete patient data management with medical records
- **Medical Procedures** - Track and manage medical procedures and treatments
- **Diagnosis Management** - ICD-based diagnosis catalog with categories
- **Doctor Management** - Comprehensive doctor profiles and specializations
- **Room Management** - Room allocation and availability tracking

### Key Features
- 🔐 **Secure Authentication** - User login with role-based access
- 📊 **Real-time Dashboard** - Statistics and overview of clinic operations
- 🔍 **Advanced Search** - Search functionality across all modules
- 📄 **Pagination** - Efficient data display with pagination
- 📱 **Responsive Design** - Mobile-friendly interface using Bootstrap 5
- 🎨 **Modern UI/UX** - Clean and professional design
- 📋 **CRUD Operations** - Create, Read, Update, Delete for all entities
- 🔔 **Flash Messages** - User-friendly notifications
- 📅 **Date/Time Management** - Proper scheduling and appointment tracking
- 💰 **Cost Tracking** - Medical procedure cost management

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+ (Pure PHP, no framework)
- **Frontend**: Bootstrap 5.3.0, HTML5, CSS3, JavaScript
- **Database**: MySQL 5.7+
- **Icons**: Bootstrap Icons 1.10.0
- **Server**: Apache/Nginx with PHP support

## 📋 Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Modern web browser (Chrome, Firefox, Safari, Edge)

## 🚀 Installation

### 1. Clone or Download
```bash
git clone https://github.com/yourusername/klinik-management.git
cd klinik-management
```

### 2. Database Setup
1. Create a MySQL database named `db_klinik_management`
2. Import the database schema:
```bash
mysql -u root -p db_klinik_management < database/schema.sql
```

### 3. Configuration
1. Edit `config/database.php` with your database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'db_klinik_management');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### 4. Web Server Setup
1. Place the project in your web server directory (e.g., `htdocs/` for XAMPP)
2. Ensure the web server has read/write permissions to the project directory

### 5. Access the System
1. Open your web browser
2. Navigate to `http://localhost/klinik-management`
3. Login with default credentials:
   - **Username**: `admin`
   - **Password**: `admin123`

## 📁 Project Structure

```
klinik-management/
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── script.js
├── config/
│   └── database.php
├── database/
│   └── schema.sql
├── includes/
│   ├── functions.php
│   ├── header.php
│   └── footer.php
├── pages/
│   ├── dashboard.php
│   ├── pasien.php
│   ├── tindakan.php
│   ├── diagnosis.php
│   ├── dokter.php
│   ├── ruang.php
│   └── logout.php
├── index.php
├── login.php
└── README.md
```

## 🔧 Configuration

### Database Configuration
Edit `config/database.php` to match your database settings:

```php
define('DB_HOST', 'localhost');     // Database host
define('DB_NAME', 'db_klinik_management'); // Database name
define('DB_USER', 'root');          // Database username
define('DB_PASS', '');              // Database password
```

### Security Settings
- Change default admin password after first login
- Configure proper file permissions
- Enable HTTPS in production
- Regular database backups

## 📖 Usage Guide

### Dashboard
- View real-time statistics
- Quick access to all modules
- Recent activities overview

### Patient Management
1. **Add Patient**: Click "Tambah Pasien" button
2. **Search**: Use search bar to find patients
3. **Edit**: Click edit icon on patient row
4. **Delete**: Click delete icon (with confirmation)

### Medical Procedures
1. **Add Procedure**: Select patient, doctor, and room
2. **Schedule**: Set date and time
3. **Track**: Monitor procedure status
4. **Cost**: Record procedure costs

### Doctor Management
1. **Add Doctor**: Complete doctor profile
2. **Specialization**: Assign medical specializations
3. **Status**: Track active/inactive status
4. **Contact**: Manage contact information

### Room Management
1. **Add Room**: Define room type and capacity
2. **Status**: Track availability
3. **Floor**: Organize by floor levels
4. **Description**: Add room details

### Diagnosis Management
1. **ICD Codes**: Use standard ICD-10 codes
2. **Categories**: Organize by disease categories
3. **Descriptions**: Add detailed descriptions
4. **Search**: Find diagnoses quickly

## 🔐 Security Features

- **Session Management**: Secure user sessions
- **Input Validation**: Sanitized user inputs
- **SQL Injection Prevention**: Prepared statements
- **XSS Protection**: Output escaping
- **CSRF Protection**: Form token validation
- **Password Hashing**: MD5 encryption (upgrade to bcrypt recommended)

## 🎨 Customization

### Styling
- Edit `assets/css/style.css` for custom styling
- Modify Bootstrap variables for theme changes
- Add custom fonts and colors

### Functionality
- Extend `includes/functions.php` for new features
- Add new modules in `pages/` directory
- Modify database schema as needed

### Localization
- Update text strings for different languages
- Modify date/time formats
- Adjust currency symbols

## 📊 Database Schema

### Main Tables
- `users` - System users and authentication
- `pasien` - Patient information
- `dokter` - Doctor profiles
- `ruang` - Room management
- `diagnosis` - Medical diagnoses
- `tindakan` - Medical procedures
- `janji_temu` - Appointments
- `rekam_medis` - Medical records

### Views
- `v_tindakan_lengkap` - Complete procedure information
- `v_statistik_dashboard` - Dashboard statistics

### Stored Procedures
- `GetPasienByNoRM()` - Find patient by medical record number
- `GetTindakanByDateRange()` - Get procedures by date range
- `GetRuangTersedia()` - Find available rooms

## 🚀 Deployment

### Production Setup
1. **Web Server**: Configure Apache/Nginx
2. **SSL Certificate**: Enable HTTPS
3. **Database**: Use dedicated MySQL server
4. **Backup**: Set up automated backups
5. **Monitoring**: Implement error logging

### Performance Optimization
- Enable PHP OPcache
- Configure MySQL query cache
- Use CDN for static assets
- Implement caching strategies

## 🐛 Troubleshooting

### Common Issues

**Database Connection Error**
- Check database credentials in `config/database.php`
- Ensure MySQL service is running
- Verify database exists

**Page Not Found**
- Check web server configuration
- Verify file permissions
- Ensure .htaccess is properly configured

**Login Issues**
- Verify default credentials: admin/admin123
- Check session configuration
- Clear browser cache

**Permission Errors**
- Set proper file permissions (755 for directories, 644 for files)
- Ensure web server can write to session directory

## 📝 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📞 Support

For support and questions:
- Create an issue on GitHub
- Email: support@klinikmanagement.com
- Documentation: [Wiki](https://github.com/yourusername/klinik-management/wiki)

## 🔄 Updates

### Version 1.0.0
- Initial release
- Core modules implemented
- Basic CRUD operations
- Responsive design

### Planned Features
- Appointment scheduling system
- Billing and invoicing
- Inventory management
- Reporting and analytics
- Mobile app integration
- API endpoints

## 🙏 Acknowledgments

- Bootstrap team for the excellent CSS framework
- PHP community for documentation and examples
- MySQL team for the robust database system
- All contributors and testers

---

**Note**: This is a development version. For production use, ensure proper security measures, regular backups, and thorough testing. 