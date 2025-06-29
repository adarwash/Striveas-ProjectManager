<div class="container-fluid">
    <!-- Page Header with Background -->
    <div class="bg-light rounded-3 p-4 mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="/dashboard" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="/sites" class="text-decoration-none">Sites</a></li>
                <li class="breadcrumb-item"><a href="/sites/viewSite/<?= $data['site']['id'] ?>" class="text-decoration-none"><?= $data['site']['name'] ?></a></li>
                <li class="breadcrumb-item active" aria-current="page">Assign Clients</li>
            </ol>
        </nav>
        
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-1">Assign Clients to Site</h1>
                <p class="text-muted"><?= $data['site']['name'] ?> - <?= $data['site']['location'] ?></p>
            </div>
            <div class="d-flex">
                <a href="/sites/viewSite/<?= $data['site']['id'] ?>" class="btn btn-light">
                    <i class="bi bi-arrow-left me-1"></i> Back to Site
                </a>
            </div>
        </div>
    </div>
    
    <!-- Flash Messages -->
    <?php flash('site_success'); ?>
    <?php flash('site_error'); ?>
    
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Select Clients</h5>
            <div class="form-group has-search position-relative">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="text" class="form-control ps-4" placeholder="Search clients..." id="clientSearch">
            </div>
        </div>
        <div class="card-body">
            <form id="clientAssignmentForm" action="/sites/assignClients/<?= $data['site']['id'] ?>" method="post">
                <!-- Quick Selection Buttons -->
                <div class="mb-3">
                    <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="selectAll">Select All</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary me-2" id="deselectAll">Deselect All</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="invertSelection">Invert Selection</button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="clientsTable">
                        <thead class="table-light">
                            <tr>
                                <th width="50">Select</th>
                                <th>Client</th>
                                <th>Industry</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th>Relationship</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($data['clients'])): ?>
                                <?php foreach ($data['clients'] as $client): ?>
                                    <?php
                                        $isAssigned = in_array($client['id'], $data['assignedIds']);
                                        $relationship = $isAssigned && isset($data['relationships'][$client['id']]) 
                                                      ? $data['relationships'][$client['id']] 
                                                      : 'Standard';
                                    ?>
                                    <tr class="client-row <?= $isAssigned ? 'table-active' : '' ?>">
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input client-checkbox" type="checkbox" 
                                                       name="clients[]" value="<?= $client['id'] ?>"
                                                       id="client<?= $client['id'] ?>"
                                                       <?= $isAssigned ? 'checked' : '' ?>>
                                            </div>
                                        </td>
                                        <td>
                                            <label for="client<?= $client['id'] ?>" class="cursor-pointer">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-icon rounded bg-light p-2 me-3">
                                                        <i class="bi bi-building text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <span class="fw-semibold"><?= $client['name'] ?></span>
                                                        <?php if(!empty($client['notes'])): ?>
                                                        <div class="small text-muted text-truncate" style="max-width: 200px;"><?= $client['notes'] ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </label>
                                        </td>
                                        <td><?= $client['industry'] ?? 'N/A' ?></td>
                                        <td>
                                            <?php if(!empty($client['contact_person']) || !empty($client['email'])): ?>
                                            <div class="d-flex flex-column">
                                                <?php if(!empty($client['contact_person'])): ?>
                                                <span><?= $client['contact_person'] ?></span>
                                                <?php endif; ?>
                                                <?php if(!empty($client['email'])): ?>
                                                <span class="small text-muted"><?= $client['email'] ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <?php else: ?>
                                            <span class="text-muted">No contact info</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                                $statusClass = 'bg-secondary';
                                                if ($client['status'] === 'Active') {
                                                    $statusClass = 'bg-success';
                                                } elseif ($client['status'] === 'Inactive') {
                                                    $statusClass = 'bg-danger';
                                                } elseif ($client['status'] === 'Prospect') {
                                                    $statusClass = 'bg-warning';
                                                }
                                            ?>
                                            <span class="badge <?= $statusClass ?> rounded-pill"><?= $client['status'] ?></span>
                                        </td>
                                        <td>
                                            <select name="relationship_types[<?= $client['id'] ?>]" class="form-select form-select-sm relationship-select" 
                                                    data-client-id="<?= $client['id'] ?>"
                                                    <?= !$isAssigned ? 'disabled' : '' ?>>
                                                <option value="Standard" <?= $relationship === 'Standard' ? 'selected' : '' ?>>Standard</option>
                                                <option value="Primary" <?= $relationship === 'Primary' ? 'selected' : '' ?>>Primary</option>
                                                <option value="Secondary" <?= $relationship === 'Secondary' ? 'selected' : '' ?>>Secondary</option>
                                                <option value="Partner" <?= $relationship === 'Partner' ? 'selected' : '' ?>>Partner</option>
                                                <option value="Vendor" <?= $relationship === 'Vendor' ? 'selected' : '' ?>>Vendor</option>
                                                <option value="Prospect" <?= $relationship === 'Prospect' ? 'selected' : '' ?>>Prospect</option>
                                                <option value="Former" <?= $relationship === 'Former' ? 'selected' : '' ?>>Former</option>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">
                                        <div class="p-4">
                                            <i class="bi bi-building fs-1 text-muted d-block mb-2"></i>
                                            <p class="mb-0">No clients found in the system.</p>
                                            <?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'manager'])): ?>
                                            <p class="text-muted">Clients need to be added to the system first.</p>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3 d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        <i class="bi bi-info-circle"></i> Select clients and set their relationship with this site
                    </div>
                    <div>
                        <input type="hidden" name="form_submitted" value="1">
                        <button type="submit" class="btn btn-primary" id="saveButton">
                            <i class="bi bi-save me-1"></i> Save Assignments
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.avatar-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.form-control:focus,
.form-select:focus {
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
    border-color: #86b7fe;
}
.cursor-pointer {
    cursor: pointer;
}
.client-row:hover {
    background-color: rgba(0, 123, 255, 0.05);
}
</style>

<script>
// Search functionality
document.getElementById('clientSearch').addEventListener('keyup', function() {
    var searchText = this.value.toLowerCase();
    var table = document.getElementById('clientsTable');
    var rows = table.getElementsByTagName('tr');
    var noResults = true;
    
    for (var i = 1; i < rows.length; i++) {
        var clientText = rows[i].textContent.toLowerCase();
        if (clientText.indexOf(searchText) > -1) {
            rows[i].style.display = "";
            noResults = false;
        } else {
            rows[i].style.display = "none";
        }
    }
    
    // Show/hide no results message
    var tbody = table.getElementsByTagName('tbody')[0];
    var existingNoResults = document.getElementById('noResultsMessage');
    
    if (noResults && searchText.length > 0) {
        if (!existingNoResults) {
            var noResultsRow = document.createElement('tr');
            noResultsRow.id = 'noResultsMessage';
            noResultsRow.innerHTML = '<td colspan="6" class="text-center py-4">' +
                                     '<i class="bi bi-search fs-4 text-muted mb-2"></i>' +
                                     '<p class="mb-0">No clients found matching <strong>"' + searchText + '"</strong></p>' +
                                     '<p class="text-muted">Try using different keywords</p>' +
                                     '</td>';
            tbody.appendChild(noResultsRow);
        }
    } else if (existingNoResults) {
        existingNoResults.remove();
    }
});

// Select All button
document.getElementById('selectAll').addEventListener('click', function() {
    var checkboxes = document.getElementsByClassName('client-checkbox');
    for (var i = 0; i < checkboxes.length; i++) {
        var checkbox = checkboxes[i];
        var row = checkbox.closest('tr');
        if (row.style.display !== 'none') { // Only select visible rows
            checkbox.checked = true;
            row.classList.add('table-active');
            enableRelationshipSelect(checkbox);
        }
    }
});

// Deselect All button
document.getElementById('deselectAll').addEventListener('click', function() {
    var checkboxes = document.getElementsByClassName('client-checkbox');
    for (var i = 0; i < checkboxes.length; i++) {
        var checkbox = checkboxes[i];
        var row = checkbox.closest('tr');
        if (row.style.display !== 'none') { // Only deselect visible rows
            checkbox.checked = false;
            row.classList.remove('table-active');
            enableRelationshipSelect(checkbox);
        }
    }
});

// Invert Selection button
document.getElementById('invertSelection').addEventListener('click', function() {
    var checkboxes = document.getElementsByClassName('client-checkbox');
    for (var i = 0; i < checkboxes.length; i++) {
        var checkbox = checkboxes[i];
        var row = checkbox.closest('tr');
        if (row.style.display !== 'none') { // Only affect visible rows
            checkbox.checked = !checkbox.checked;
            if (checkbox.checked) {
                row.classList.add('table-active');
            } else {
                row.classList.remove('table-active');
            }
            enableRelationshipSelect(checkbox);
        }
    }
});

// Click on row to toggle checkbox
document.querySelectorAll('.client-row').forEach(function(row) {
    row.addEventListener('click', function(e) {
        // Don't toggle if clicking on the checkbox itself or select element
        if (e.target.type !== 'checkbox' && e.target.tagName !== 'SELECT' && e.target.tagName !== 'OPTION') {
            var checkbox = this.querySelector('.client-checkbox');
            checkbox.checked = !checkbox.checked;
            
            if (checkbox.checked) {
                this.classList.add('table-active');
            } else {
                this.classList.remove('table-active');
            }
            
            enableRelationshipSelect(checkbox);
        }
    });
});

// Enable/disable relationship select based on checkbox
document.querySelectorAll('.client-checkbox').forEach(function(checkbox) {
    checkbox.addEventListener('change', function() {
        var row = this.closest('tr');
        if (this.checked) {
            row.classList.add('table-active');
        } else {
            row.classList.remove('table-active');
        }
        enableRelationshipSelect(this);
    });
});

function enableRelationshipSelect(checkbox) {
    var row = checkbox.closest('tr');
    var select = row.querySelector('.relationship-select');
    if (select) {
        select.disabled = !checkbox.checked;
    }
}

// Form validation before submit
document.getElementById('clientAssignmentForm').addEventListener('submit', function(event) {
    var hasChecked = false;
    var checkboxes = document.getElementsByClassName('client-checkbox');
    
    // Check if at least one checkbox is checked
    for (var i = 0; i < checkboxes.length; i++) {
        if (checkboxes[i].checked) {
            hasChecked = true;
            break;
        }
    }
    
    // Log form data for debugging
    console.log('Form being submitted with data:', new FormData(this));
});
</script> 