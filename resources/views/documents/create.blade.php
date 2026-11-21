@extends('layout.app')
@section('title')
<title>Upload Document | DocuVault</title>
@endsection
@section('main-content')
<body class="bg-gray-100" style="background-color: #f4f6fa !important;">
  <!-- Dark Header Background -->
  <div class="position-absolute w-100" style="min-height: 280px; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);"></div>

  <main class="main-content position-relative border-radius-lg">
    <!-- Navbar -->
    <nav class="navbar navbar-main navbar-expand-lg px-4 pt-3 shadow-none border-radius-xl" id="navbarBlur">
      <div class="container-fluid py-1 px-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center">
          <a href="{{ route('documents.index') }}" class="d-flex align-items-center text-decoration-none">
            <img src="{{ asset('images/logo.svg') }}" alt="DocuVault Logo" width="44" height="44" class="me-3 shadow-sm rounded-3">
            <div>
              <div class="d-flex align-items-center gap-2">
                <h4 class="text-white font-weight-bolder mb-0">Docu<span style="background: linear-gradient(135deg, #60a5fa, #a78bfa); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Vault</span></h4>
                <span class="badge font-weight-bold text-xxs px-2 py-1 rounded-pill" style="background: rgba(99, 102, 241, 0.3) !important; color: #c7d2fe !important; border: 1px solid rgba(165, 180, 252, 0.35);">UPLOAD</span>
              </div>
              <span class="text-white opacity-7 text-xs">Upload & Auto-Classify Document</span>
            </div>
          </a>
        </div>

        <div>
          <a href="{{ route('documents.index') }}" class="btn btn-sm btn-white text-primary font-weight-bold mb-0 shadow-sm">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Dashboard
          </a>
        </div>
      </div>
    </nav>

    <div class="container-fluid px-4 py-4">
      <div class="row justify-content-center">
        <div class="col-lg-7 col-md-9">
          <div class="card shadow-sm p-4">
            <div class="text-center mb-4">
              <h5 class="font-weight-bolder text-dark mb-1">Select Document to Upload</h5>
              <p class="text-xs text-muted mb-0">Supported formats: PDF (.pdf), Word (.docx) &bull; Max size: 50MB</p>
            </div>

            <!-- Upload Form -->
            <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
              @csrf

              <div class="d-flex flex-column align-items-center p-5 text-center rounded-4 mb-3" 
                   id="dropZone" 
                   style="border: 2px dashed #6366f1; cursor: pointer; background: rgba(99, 102, 241, 0.04); transition: all 0.2s ease;"
                   onclick="document.getElementById('fileUpload').click();">
                <div class="icon icon-shape bg-white rounded-circle shadow-sm mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                  <i class="fa-solid fa-cloud-arrow-up text-primary fs-3"></i>
                </div>
                <h6 class="text-dark font-weight-bold mb-1" id="dropTitle">Click here to browse or drag & drop file</h6>
                <span id="dropText" class="text-xs text-muted">Files are automatically analyzed and organized by category</span>
                
                <div id="fileInfoBox" class="mt-3 p-2 px-3 bg-white rounded-3 shadow-sm d-none align-items-center gap-2">
                  <i class="fa-solid fa-file-circle-check text-success fs-5"></i>
                  <span id="fileDetails" class="text-xs font-weight-bold text-dark"></span>
                </div>
              </div>

              <input type="file" name="document" id="fileUpload" class="d-none" accept=".pdf,.docx">

              <div class="d-grid mt-4">
                <button type="submit" id="submitBtn" class="btn btn-primary btn-lg shadow-sm">
                  <i class="fa-solid fa-bolt me-1"></i> Upload and Process Document
                </button>
              </div>
            </form>

            @if (session('success'))
              <div class="alert alert-success text-white mt-3 mb-0" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                @if (session('duration'))
                  <span class="d-block text-xs opacity-8 mt-1">{{ session('duration') }}</span>
                @endif
              </div>
            @endif

            @if ($errors->any())
              <div class="alert alert-danger text-white mt-3 mb-0" role="alert">
                <ul class="mb-0 ps-3">
                  @foreach ($errors->all() as $error)
                    <li class="text-xs font-weight-bold">{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </main>

  <script>
    const fileInput = document.getElementById('fileUpload');
    const dropTitle = document.getElementById('dropTitle');
    const dropText = document.getElementById('dropText');
    const fileInfoBox = document.getElementById('fileInfoBox');
    const fileDetails = document.getElementById('fileDetails');
    const uploadForm = document.getElementById('uploadForm');
    const dropZone = document.getElementById('dropZone');

    fileInput.addEventListener('change', function() {
      if (this.files && this.files[0]) {
        const file = this.files[0];
        fileDetails.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
        fileInfoBox.classList.remove('d-none');
        fileInfoBox.classList.add('d-flex');
        dropTitle.textContent = 'File Ready for Upload';
        dropText.textContent = 'Click button below to start processing';
      }
    });

    uploadForm.addEventListener('submit', function(e) {
      if (!fileInput.files || !fileInput.files[0]) {
        e.preventDefault();
        alert('Please choose a PDF or DOCX file first.');
      }
    });

    ['dragenter', 'dragover'].forEach(eventName => {
      dropZone.addEventListener(eventName, (e) => {
        e.preventDefault();
        dropZone.style.background = 'rgba(99, 102, 241, 0.12)';
      }, false);
    });
    ['dragleave', 'drop'].forEach(eventName => {
      dropZone.addEventListener(eventName, (e) => {
        e.preventDefault();
        dropZone.style.background = 'rgba(99, 102, 241, 0.04)';
      }, false);
    });
    dropZone.addEventListener('drop', (e) => {
      const dt = e.dataTransfer;
      if (dt.files && dt.files.length) {
        fileInput.files = dt.files;
        fileInput.dispatchEvent(new Event('change'));
      }
    });
  </script>
</body>
@endsection