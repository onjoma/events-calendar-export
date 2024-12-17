# Events Calendar Listings Export 📅

[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

> 🚀 A powerful WordPress plugin that transforms your Events Calendar entries into beautifully formatted PDF documents.

## ✨ Features

- 📊 Export events to PDF for any selected date range
- 🎨 Customizable PDF settings with logo upload
- 📄 Professional PDF layout with cover page
- 🔄 Seamless integration with The Events Calendar plugin
- 🎯 User-friendly admin interface
- 🌐 Internationalization ready

## 🔧 Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- [The Events Calendar](https://wordpress.org/plugins/the-events-calendar/) plugin
- Composer (for installation)

## 📦 Installation

1. Clone this repository to your WordPress plugins directory:
```bash
cd wp-content/plugins/
git clone https://github.com/onjoma/events-calendar-export.git lindawp-events-export
```

2. Install dependencies using Composer:
```bash
cd lindawp-events-export
composer install
```

3. Activate the plugin through the WordPress admin panel.

## 🚀 Usage

1. Navigate to "Events Export" in your WordPress admin menu
2. Configure PDF settings:
   - 🖼️ Upload your logo for branding
   - 📏 Recommended logo size: 200px width
   - 📄 Logo appears on PDF cover page
3. Select your desired date range
4. Click "Export to PDF" to generate your document

## ⚙️ Configuration

### PDF Settings
The plugin allows you to customize your PDF exports with the following options:

- **Logo Upload** 🖼️
  - Supports various image formats
  - Automatically sized for optimal display
  - Appears on the PDF cover page

- **Date Range Selection** 📅
  - Flexible date picker
  - Export events for any time period

## 🛠️ Development

### Setup Development Environment
1. 📥 Clone the repository
2. 🔧 Install dependencies: `composer install`
3. ✅ Ensure The Events Calendar plugin is installed and activated

### File Structure
```
lindawp-events-export/
├── 📁 admin/                 # Admin-related files
├── 📁 includes/             # Core plugin files
├── 📁 languages/            # Translation files
├── 📁 public/              # Public-facing functionality
└── 📁 vendor/              # Composer dependencies
```

## 🤝 Contributing

We welcome contributions! Here's how you can help:

1. 🍴 Fork the repository
2. 🌿 Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. 💾 Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. 📤 Push to the branch (`git push origin feature/AmazingFeature`)
5. 🎯 Open a Pull Request

## 📄 License

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details.

## 👨‍💻 Author

**LindaWP** - 

## 🙏 Acknowledgments

- Built with love for The Events Calendar community
- Special thanks to all contributors

---
⭐ If you find this plugin helpful, please consider giving it a star!
