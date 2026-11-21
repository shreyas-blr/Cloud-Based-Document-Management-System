<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  @yield('title')
  @include('includes.style')
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
    }
    .card {
      border: 0;
      border-radius: 1rem;
      box-shadow: 0 10px 30px 0 rgba(17, 38, 146, 0.05);
      transition: all 0.25s ease-in-out;
    }
    .card-hover:hover {
      transform: translateY(-4px);
      box-shadow: 0 16px 36px 0 rgba(17, 38, 146, 0.12);
    }
    .table td, .table th {
      vertical-align: middle;
    }
    .bg-gradient-indigo {
      background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
    }
    .bg-gradient-teal {
      background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
    }
    .badge-soft-primary {
      background-color: rgba(94, 114, 228, 0.12);
      color: #5e72e4;
    }
    .badge-soft-danger {
      background-color: rgba(245, 54, 92, 0.12);
      color: #f5365c;
    }
    .badge-soft-success {
      background-color: rgba(45, 206, 137, 0.12);
      color: #2dce89;
    }
    .badge-soft-warning {
      background-color: rgba(251, 99, 64, 0.12);
      color: #fb6340;
    }
    .badge-soft-info {
      background-color: rgba(17, 205, 239, 0.12);
      color: #11cdef;
    }
  </style>
</head>
@yield('main-content')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</html>
