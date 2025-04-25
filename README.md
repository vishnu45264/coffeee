# Coffee Cafe Website

A full-featured coffee cafe website with user/admin functionality, ordering system, and management tools.

## Features

- **Beautiful Homepage**: Visually engaging landing page with animations and featured items
- **User Authentication**: Secure login/register system with role-based access
- **Menu Management**: Admins can add, edit, delete menu items with images
- **Ordering System**: Users can browse menu and place orders
- **Order Management**: Admins can view and update order status
- **Feedback System**: Users can leave feedback, visible to admins
- **Analytics Dashboard**: Admins can view sales statistics and popular items

## Tech Stack

- **Frontend**: HTML, CSS, JavaScript, Bootstrap 5
- **Backend**: PHP
- **Database**: MySQL
- **Libraries**: 
  - AOS.js for scroll animations
  - Font Awesome for icons
  - Chart.js for analytics

## Installation Instructions

1. **Setup Web Server**:
   - Install a web server environment like XAMPP, WAMP, or MAMP
   - Start Apache and MySQL services

2. **Database Setup**:
   - Create a database named `coffee_cafe`
   - Import the `database/coffee_cafe.sql` file

3. **Configure Database Connection**:
   - Edit `config/database.php` with your database credentials

4. **Run the Application**:
   - Place all files in your web server's root directory
   - Open your browser and navigate to `http://localhost/coffee_cafe/`

## Default Admin Account

- Email: admin@coffeecafe.com
- Password: admin123

## Directory Structure

```
coffee_cafe/
│
├── assets/               # Static assets
│   ├── css/              # Stylesheets
│   ├── js/               # JavaScript files
│   └── img/              # Images
│       ├── menu/         # Menu item images
│       └── testimonials/ # Testimonial images
│
├── config/               # Configuration files
│   └── database.php      # Database connection
│
├── database/             # Database scripts
│   └── coffee_cafe.sql   # Database schema and sample data
│
├── include/              # Reusable components
│   ├── header.php        # Site header
│   ├── footer.php        # Site footer
│   └── functions.php     # Utility functions
│
├── index.php             # Homepage
├── menu.php              # Menu page
├── login.php             # Login page
├── register.php          # Registration page
├── logout.php            # Logout script
├── dashboard_user.php    # User dashboard
├── dashboard_admin.php   # Admin dashboard
└── README.md             # Project documentation
```

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Credits

Developed by [Your Name/Organization]