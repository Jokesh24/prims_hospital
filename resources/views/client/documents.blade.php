<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>School HRMS Dashboard</title>
    <?php require_once 'includes/header_links.php'; ?>


</head>

<body>

    <div id="overlay" onclick="closeMobile()"></div>

    <!-- ═══════════════ SIDEBAR ═══════════════ -->
    <?php require_once 'includes/sidebar.php'; ?>


    <!-- ═══════════════ MAIN ═══════════════ -->
    <div id="main">

        <?php require_once 'includes/header.php'; ?>


        <div class="content">
            <div class="container-fluid py-2">

                <!-- 1. HEADER SECTION -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h4 class="fw-bold m-0">Document Repository</h4>
                    </div>
                    <button class="btn btn-primary px-4 py-2" style="border-radius:10px;" data-bs-toggle="modal"
                        data-bs-target="#uploadDocumentModal">
                        <i class="bi bi-cloud-arrow-up me-2"></i> Upload Document
                    </button>
                </div>



                <!-- 3. FILTER CARD -->
                <div class="dashboard-card mb-2">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="filter-label">Search Files</label>
                            <input type="text" class="filter-control w-100" placeholder="Enter file name...">
                        </div>
                        <div class="col-md-2">
                            <label class="filter-label">File Format</label>
                            <select class="form-select filter-control">
                                <option selected>All Formats</option>
                                <option>PDF Documents</option>
                                <option>Images (JPG/PNG)</option>
                                <option>Word Files</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="filter-label">Sort By</label>
                            <select class="form-select filter-control">
                                <option selected>Newest First</option>
                                <option>Oldest First</option>
                                <option>Size (Large to Small)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex gap-2">
                                <button class="btn btn-search-trigger">
                                    <i class="bi bi-search"></i>
                                </button>
                                <button class="btn btn-refresh" title="Reset Filters">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. TABLE CARD -->
                <div class="dashboard-card">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3" style="width:40px"><input class="form-check-input"
                                            type="checkbox"></th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Document Name</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Category</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Size</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Uploaded Date</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold">Status</th>
                                    <th class="py-3 text-muted text-uppercase small fw-bold text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Row 1 -->
                                <tr>
                                    <td class="ps-4"><input class="form-check-input" type="checkbox"></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-file-earmark-pdf text-danger fs-5 me-2"></i>
                                            <span class="text-dark fw-medium">offer_letter.pdf</span>
                                        </div>
                                    </td>
                                    <td>HR Documents</td>
                                    <td>1.2 MB</td>
                                    <td>15 Jan 2024</td>
                                    <td><span class="badge-soft"
                                            style="background:#ecfdf5; color:#10b981;">Verified</span></td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-light btn-sm text-primary me-1"><i
                                                class="bi bi-download"></i></button>
                                        <button class="btn btn-light btn-sm text-danger"><i
                                                class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                                <!-- Row 2 -->
                                <tr>
                                    <td class="ps-4"><input class="form-check-input" type="checkbox"></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-file-earmark-word text-primary fs-5 me-2"></i>
                                            <span class="text-dark fw-medium">project_scope.docx</span>
                                        </div>
                                    </td>
                                    <td>Academic</td>
                                    <td>850 KB</td>
                                    <td>02 Feb 2024</td>
                                    <td><span class="badge-soft"
                                            style="background:#ecfdf5; color:#10b981;">Verified</span></td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-light btn-sm text-primary me-1"><i
                                                class="bi bi-download"></i></button>
                                        <button class="btn btn-light btn-sm text-danger"><i
                                                class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                                <!-- Row 3 -->
                                <tr>
                                    <td class="ps-4"><input class="form-check-input" type="checkbox"></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-file-earmark-image text-warning fs-5 me-2"></i>
                                            <span class="text-dark fw-medium">id_front_view.jpg</span>
                                        </div>
                                    </td>
                                    <td>Identity Proof</td>
                                    <td>2.4 MB</td>
                                    <td>20 Feb 2024</td>
                                    <td><span class="badge-soft"
                                            style="background:#fffbeb; color:#f59e0b;">Pending</span></td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-light btn-sm text-primary me-1"><i
                                                class="bi bi-download"></i></button>
                                        <button class="btn btn-light btn-sm text-danger"><i
                                                class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- PAGINATION -->
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-end mb-0">
                            <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">Next</a></li>
                        </ul>
                    </nav>
                </div>

                <!-- 5. UPLOAD MODAL -->
                <div class="modal fade" id="uploadDocumentModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content custom-modal">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold">Upload New Document</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Document Title</label>
                                        <input type="text" class="form-control custom-input"
                                            placeholder="e.g. Employee Contract 2024">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Category</label>
                                        <select class="form-select custom-input">
                                            <option>Personal ID</option>
                                            <option>Academic Certificate</option>
                                            <option>Employment Contract</option>
                                            <option>Policy Manual</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Select File</label>
                                        <input type="file" class="form-control custom-input">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Expiry Date (Optional)</label>
                                        <input type="date" class="form-control custom-input">
                                    </div>
                                    <div class="mt-4">
                                        <button type="button" class="btn btn-primary py-2 fw-bold w-100">Upload &
                                            Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <?php require_once 'includes/footer_links.php'; ?>

</body>

</html>