# College Project Progress Diary Management System

## Overview
The College Project Progress Diary Management System is designed to streamline the tracking and collaboration of student projects within a college environment. It facilitates structured documentation and enhances communication between admins, teachers, and students.

## Features
- **Admin Panel**: Manage user accounts, import data from Excel, and edit institutional information.
- **Teacher Dashboard**: Create project groups, provide feedback, and track student progress.
- **Student Portal**: Submit diary entries, receive feedback, and update submissions.
- **Role-Based Access Control**: Secure authentication for different user roles.
- **Real-Time Notifications**: Alerts for updates and feedback.
- **PDF Generation**: Export project diaries as PDF reports.

## Technology Stack
- **Backend**: PHP, MySQL
- **Frontend**: HTML, CSS
- **Libraries**: 
  - TCPDF / DomPDF for PDF generation
  - PhpSpreadsheet for Excel data import

## Installation
1. Clone the repository:
   ```
   git clone https://github.com/yourusername/project-diary-system.git
   ```
2. Navigate to the project directory:
   ```
   cd project-diary-system
   ```
3. Import the database schema into your MySQL database.
4. Configure the database connection in `config/database.php`.
5. Install required PHP libraries (TCPDF, PhpSpreadsheet) via Composer or manually.
6. Access the application through your web server.

## Usage
- **Admin**: Log in to manage users and institutional data.
- **Teachers**: Create project groups and review student submissions.
- **Students**: Fill out diary entries and submit for review.

## Contributing
Contributions are welcome! Please fork the repository and submit a pull request for any enhancements or bug fixes.

## License
This project is licensed under the MIT License. See the LICENSE file for details.

## Acknowledgments
- Thanks to the contributors and libraries that made this project possible.