# Laravel CSV Manager & Marketplace Generator

A Laravel-based web application that enables CSV upload, dynamic editing (rows & columns), multi-format export (CSV, XML, TXT, PDF), and the ability to create marketplaces by selecting specific CSV columns. Includes role-based authentication (RBAC) using Laravel Fortify.

---

## 🚀 Features

- 🔐 User Registration & Login (RBAC with Admin & User roles)
- 📤 Upload CSV files with unlimited rows and columns
- 📝 Dynamically update CSVs:
  - Add new rows
  - Add new columns
- 📁 Export data as:
  - CSV
  - XML
  - TXT
  - PDF
- 🏪 Create Marketplaces:
  - Name your marketplace
  - Select columns from uploaded CSV using a UI checkbox list
  - Automatically generate new CSV for each marketplace
  - Export marketplace file in CSV and PDF

---

## 🛠️ Tech Stack

- **Backend**: Laravel 10+
- **Authentication**: Laravel Fortify
- **Database**: MySQL
- **Frontend**: Blade (or Vue.js if applicable)
- **Export Libraries**: (e.g., Maatwebsite Excel, DOMPDF)

---

## 📦 Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/Anniedrippin/csv-app.git
   cd csv-app
