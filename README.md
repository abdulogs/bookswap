# BookSwap - Online Community Book Exchange System

A web application built with Laravel Livewire Volt and Tailwind CSS that allows community members to share, discover, and borrow books from each other.

## Features

### 🔐 User Authentication & Profile Management
- User registration and login system
- Secure authentication with Laravel's built-in features
- User profile management

### 📚 Book Listing & Management
- Add books with detailed attributes (title, author, genre, condition, description)
- Edit and delete listed books
- Set book status as Available or Lent Out
- Comprehensive book information display

### 🔍 Search & Discovery
- Search books by title or author
- Filter books by genre and condition
- Browse all available books in the community
- Featured books on the homepage

### 🤝 Book Request System
- Send borrowing requests to book owners
- Approve, reject, or mark books as returned
- Track incoming and outgoing requests
- Manage book lending workflow

### 🗄️ Database Design
- Structured relational database with MySQL
- Proper relationships between users, books, and requests
- Efficient data organization and querying

## Technology Stack

- **Backend**: Laravel 12.x
- **Frontend**: Livewire Volt 3.x with Tailwind CSS
- **Database**: MySQL with Laravel Migrations
- **Authentication**: Laravel's built-in authentication system
- **Styling**: Tailwind CSS 4.x for modern, responsive design

## Installation & Setup

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 20.19+ or 22.12+
- MySQL database

### Installation Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd bookswap
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure database in `.env` file**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=bookswap
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. **Run database migrations**
   ```bash
   php artisan migrate
   ```

7. **Seed the database with sample data (optional)**
   ```bash
   php artisan db:seed --class=BookSeeder
   ```

8. **Build frontend assets**
   ```bash
   npm run build
   ```

9. **Start the development server**
   ```bash
   php artisan serve
   ```

10. **Access the application**
    - Open your browser and go to `http://localhost:8000`

## Sample Users for Testing

The seeder creates three sample users with the password `password`:

- **John Doe** (john@example.com) - Has 3 books
- **Jane Smith** (jane@example.com) - Has 2 books  
- **Bob Johnson** (bob@example.com) - Has 2 books

## System Architecture

### Database Schema

#### Users Table
- Basic user information (name, email, password)
- Relationships to books and requests

#### Books Table
- Book details (title, author, genre, condition, status)
- Owner relationship and description
- Status tracking (Available/Lent Out)

#### Book Requests Table
- Request details (book, borrower, owner)
- Status tracking (Pending/Approved/Rejected/Returned)
- Timestamps for borrowing and returning

### Key Features Implementation

#### Livewire Volt Components
- **Home**: Welcome page with featured books
- **Auth**: Login and registration forms
- **Books**: CRUD operations and browsing
- **Requests**: Managing borrowing requests

#### Responsive Design
- Mobile-first approach with Tailwind CSS
- Clean, modern interface
- Intuitive navigation and user experience

## Usage Guide

### For New Users
1. Register an account
2. Browse available books
3. Send borrowing requests
4. Manage your own book collection

### For Book Owners
1. Add books to your collection
2. Review borrowing requests
3. Approve or reject requests
4. Mark books as returned

### For Borrowers
1. Search and browse books
2. Send borrowing requests
3. Track request status
4. Return books on time

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Support

For support and questions, please open an issue in the repository or contact the development team.

---

**BookSwap** - Building communities through shared reading experiences 📚
