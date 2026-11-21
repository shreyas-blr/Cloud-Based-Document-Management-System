# 📂 DocuVault — Document Intelligence & Cloud DMS

[![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)](LICENSE)

**DocuVault** is an intelligent Cloud Document Management System (DMS) and text analytics platform. It automatically processes uploaded **PDF** and **Word (DOCX)** documents, extracts body text, classifies them into hierarchical category trees, generates intelligent NLP summaries, and enables instant full-text search with keyword highlighting.

---

## ✨ Key Features

- 📑 **Smart File Upload (PDF & Word DOCX)**
  - Seamless drag-and-drop and browse interface with real-time file size & format validation (up to 50MB).
  - Automatically derives meaningful document titles from file metadata and contents.

- 🧠 **Intelligent Document Summarizer**
  - Built-in TF-IDF and NLP-based extractive summarization engine.
  - Generates cohesive executive summaries and bulleted key takeaways.
  - Calculates reading time saved (e.g. *97% reduction, 11 min ➔ 1 min read*).
  - Caches generated summaries in the database for instant sub-millisecond retrieval.

- 🏷️ **Hierarchical Auto-Categorization**
  - Scans document text against predefined multi-tier category taxonomy trees (e.g., *Medical ➔ Prescriptions ➔ Chronic Medications*).
  - Categorizes files automatically without manual tagging.

- 🔍 **Full-Text Search with Snippet Highlighting**
  - Search across all indexed document bodies, titles, and filenames.
  - Matches display with instant yellow-highlighted context snippets.

- ⚡ **Interactive Dashboard & Real-Time Filtering**
  - Client-side table filtering by query, category, and format (PDF/DOCX) with zero page reloads.
  - One-click document preview modal with raw text stream and copy-to-clipboard actions.
  - Direct in-browser viewing and streaming of original files.
  - Real-time analytics: total documents, storage footprint (KB/MB), and query latency.

- ☁️ **Hybrid Storage Architecture**
  - Supports local disk storage with automatic cloud storage fallback (Dropbox / S3).

---

## 🛠️ Tech Stack

- **Backend Framework**: Laravel 10 (PHP 8.1+)
- **Database**: SQLite / MySQL (with Soft Deletes & Indexing)
- **Document Parsers**: `smalot/pdfparser` (PDF) & `phpoffice/phpword` (DOCX)
- **Frontend & UI**: Blade Templates, Bootstrap 5.3 (Argon theme), FontAwesome 6, Plus Jakarta Sans
- **Storage Driver**: Laravel Flysystem (Local & Dropbox API)

---

## 🚀 Quick Start Guide

### 1. Clone the repository
```bash
git clone https://github.com/shreyas-blr/Cloud-Based-Document-Management-System.git
cd Cloud-Based-Document-Management-System
```

### 2. Install dependencies & configure environment
```bash
composer install
cp .env.example .env
php artisan key:generate
```

### 3. Set up the database
```bash
touch database/database.sqlite
php artisan migrate --seed
```

### 4. Run the development server
```bash
php artisan serve
```
Open **[http://localhost:8000](http://localhost:8000)** in your browser.
