# Events Calendar Listings Export

A WordPress plugin that allows you to export events from The Events Calendar plugin to PDF format with customizable settings.

## Features

- Export events to PDF for a selected date range
- Customizable PDF settings including logo upload
- Professional PDF layout with cover page
- Compatible with The Events Calendar plugin
- Easy-to-use admin interface

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- The Events Calendar plugin
- Composer (for installation)

## Installation

1. Clone this repository to your WordPress plugins directory:
```bash
cd wp-content/plugins/
git clone [your-repository-url] lindawp-events-export
```

2. Install dependencies using Composer:
```bash
cd lindawp-events-export
composer install
```

3. Activate the plugin through the WordPress admin panel.

## Usage

1. Go to "Events Export" in your WordPress admin menu
2. Configure PDF settings including your logo under "PDF Settings"
3. Select a date range for the events you want to export
4. Click "Export to PDF" to generate your PDF

## Configuration

### PDF Settings
- Upload your logo for the PDF cover page
- Recommended logo size: 200px width
- Logo will appear on the PDF cover page above your site name

## Development

### Setup Development Environment
1. Clone the repository
2. Install dependencies: `composer install`
3. Make sure you have The Events Calendar plugin installed and activated

### File Structure
```
lindawp-events-export/
├── admin/                 # Admin-related files
├── includes/             # Core plugin files
├── languages/            # Translation files
├── public/              # Public-facing functionality
└── vendor/              # Composer dependencies
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details.

## Credits

Developed by LindaWP
